<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Grant;
use App\Models\GrantItem;
use Illuminate\Support\Facades\DB;

class GrantController extends Controller
{
    //
    /**
     * Upload an Excel file with multiple sheets, each containing
     * a grant header and its associated grant item records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
  
    /**
     * Display a listing of grants with their items.
     */
    public function Index(Request $request)
    {
        $grants = Grant::with([
                'grantItems' => function($query) {
                    $query->select('id', 'grant_id', 'bg_line', 'grant_position', 'grant_salary', 'grant_benefit', 'grant_level_of_effort', 'grant_position_number', 'grant_cost_by_monthly', 'grant_total_amount', 'grant_total_cost_by_person');
                    // No need for position relationship now
                }
            ])
            ->whereHas('grantItems', function($query) use ($request) {
                // Add grant_id filter
                if ($request->has('grant_id')) {
                    $query->where('grant_id', $request->grant_id);
                }
            })
            ->get(['id', 'code', 'name']); // Select only necessary columns from the Grant table

        return response()->json($grants);
    }

     public function upload(Request $request)
     {
         $this->validateFile($request);
     
         $file = $request->file('file');
     
         try {
             $spreadsheet = IOFactory::load($file->getRealPath());
             $sheets = $spreadsheet->getAllSheets();
     
             $processedGrants = 0;
             $errors = [];
     
             DB::transaction(function () use ($sheets, &$processedGrants, &$errors) {
                 foreach ($sheets as $sheet) {
                     $data = $sheet->toArray(null, true, true, true);
                     $sheetName = $sheet->getTitle();
     
                     // Validate minimum rows
                     if (count($data) < 5) {
                         $errors[] = "Sheet '$sheetName' skipped: Insufficient data rows (minimum 5 required)";
                         continue;
                     }
     
                     // Process grant
                     $grant = $this->createGrant($data, $sheetName, $errors);
                     
                     if (!$grant) continue; // Error already recorded
                     if (!$grant->wasRecentlyCreated) {
                         $errors[] = "Sheet '$sheetName': Grant '{$grant->code}' already exists - items skipped";
                         continue;
                     }
     
                     // Process items
                     try {
                         $itemsProcessed = $this->processGrantItems($data, $grant, $sheetName, $errors);
                         if ($itemsProcessed > 0) {
                             $processedGrants++;
                         }
                     } catch (\Exception $e) {
                         $errors[] = "Sheet '$sheetName': Error processing items - " . $e->getMessage();
                     }
                 }
             });
     
             $response = [
                 'message' => 'Grant data import completed',
                 'processed_grants' => $processedGrants,
             ];
     
             if (!empty($errors)) {
                 $response['warnings'] = $errors;
             }
     
             return response()->json($response);
     
         } catch (\Exception $e) {
             return response()->json([
                 'message' => 'Failed to import grant data',
                 'error' => $e->getMessage(),
             ], 500);
         }
     }
     
     private function createGrant(array $data, string $sheetName, array &$errors)
     {
         try {
             $grantName = trim(str_replace('Grant name -', '', $data[1]['A'] ?? ''));
             $grantCode = trim(str_replace('Grant code -', '', $data[2]['A'] ?? ''));
     
             // Validate required fields
             if (empty($grantCode)) {
                 $errors[] = "Sheet '$sheetName': Missing grant code";
                 return null;
             }
     
             if (empty($grantName)) {
                 $errors[] = "Sheet '$sheetName': Missing grant name";
                 return null;
             }
     
             return Grant::firstOrCreate(
                 ['code' => $grantCode],
                 [
                     'name' => $grantName,
                     'created_by' => auth()->user()->name ?? 'system',
                     'updated_by' => auth()->user()->name ?? 'system',
                 ]
             );
         } catch (\Exception $e) {
             $errors[] = "Sheet '$sheetName': Error creating grant - " . $e->getMessage();
             return null;
         }
     }
     
     private function processGrantItems(array $data, Grant $grant, string $sheetName, array &$errors): int
     {
         $itemsProcessed = 0;
         $grantItems = [];
     
         try {
             for ($i = 4; $i < count($data); $i++) {
                 $row = $data[$i];
                 $bgLine = $row['A'] ?? null;
     
                 // Validate row
                 if (!is_numeric(trim(str_replace(',', '', $bgLine)))) {
                     continue;
                 }
     
                 // Check for duplicates
                 $existingItem = GrantItem::where('grant_id', $grant->id)
                     ->where('bg_line', $bgLine)
                     ->exists();
     
                 if ($existingItem) {
                     $errors[] = "Sheet '$sheetName': Duplicate item (BG Line: $bgLine) skipped";
                     continue;
                 }
     
                 // Prepare item data
                 $grantItems[] = [
                     'grant_id' => $grant->id,
                     'bg_line' => $bgLine,
                     'grant_position' => $row['B'] ?? null,
                     'grant_salary' => $this->toFloat($row['C']),
                     'grant_benefit' => $this->toFloat($row['D']),
                     'grant_level_of_effort' => isset($row['E']) ? trim(str_replace('%', '', $row['E'])) : null,
                     'grant_position_number' => $row['F'] ?? null,
                     'grant_cost_by_monthly' => $this->toFloat($row['G']),
                     'grant_total_amount' => $this->toFloat($row['H']),
                     'grant_total_cost_by_person' => $this->toFloat($row['I']),
                     'position_id' => $row['J'] ?? null,
                     'created_by' => auth()->user()->name ?? 'system',
                     'updated_by' => auth()->user()->name ?? 'system',
                 ];
             }
     
             if (!empty($grantItems)) {
                 GrantItem::insert($grantItems);
                 $itemsProcessed = count($grantItems);
             }
         } catch (\Exception $e) {
             $errors[] = "Sheet '$sheetName': Error processing items - " . $e->getMessage();
             throw $e; // Re-throw to trigger transaction rollback
         }
     
         return $itemsProcessed;
     }
     
     private function validateFile(Request $request)
     {
         $request->validate([
             'file' => 'required|file|mimes:xlsx,xls,csv|max:10240' // Added max file size
         ]);
     }
     
     private function toFloat($value): ?float
     {
         if (is_null($value)) return null;
         return floatval(preg_replace('/[^0-9.-]/', '', $value));
     }
    
}
