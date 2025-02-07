<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Permissions",
 *     description="API Endpoints for Permission management"
 * )
 */
class PermissionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/permissions",
     *     summary="Get list of all permissions",
     *     description="Returns paginated list of permissions",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="module",
     *         in="query",
     *         description="Filter by module name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         description="Filter by action type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"read", "write", "create", "delete", "import", "export"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in name, description, or module",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="module", type="string"),
     *                     @OA\Property(property="action", type="string"),
     *                     @OA\Property(property="description", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - Insufficient permissions")
     * )
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        if ($request->has('module')) {
            $query->where('module', $request->module);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%");
            });
        }

        $permissions = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $permissions->items(),
            'meta' => [
                'current_page' => $permissions->currentPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total()
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/permissions",
     *     summary="Create a new permission",
     *     description="Creates a new permission with specified module and action",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","module","action"},
     *             @OA\Property(property="name", type="string", example="Read Employees"),
     *             @OA\Property(property="module", type="string", example="employees"),
     *             @OA\Property(property="action", type="string", enum={"read", "write", "create", "delete", "import", "export"}),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Permission created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - Insufficient permissions"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:255',
            'action' => ['required', Rule::in(['read', 'write', 'create', 'delete', 'import', 'export'])],
            'description' => 'nullable|string'
        ]);

        // Check for duplicate module-action combination
        if (Permission::where('module', $validated['module'])
                     ->where('action', $validated['action'])
                     ->exists()) {
            return response()->json([
                'message' => 'Permission with this module and action already exists'
            ], 422);
        }

        $permission = Permission::create([
            'name' => $validated['name'],
            'slug' => Str::slug("{$validated['module']}.{$validated['action']}"),
            'module' => $validated['module'],
            'action' => $validated['action'],
            'description' => $validated['description']
        ]);

        return response()->json([
            'message' => 'Permission created successfully',
            'data' => $permission
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/permissions/{id}",
     *     summary="Get permission details",
     *     description="Returns detailed information about a specific permission",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Permission ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - Insufficient permissions"),
     *     @OA\Response(response=404, description="Permission not found")
     * )
     */
    public function show($id)
    {
        $permission = Permission::with('roles')->findOrFail($id);

        return response()->json([
            'data' => $permission
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/permissions/{id}",
     *     summary="Update permission details",
     *     description="Updates an existing permission's information",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Permission ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","module","action"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="module", type="string"),
     *             @OA\Property(property="action", type="string", enum={"read", "write", "create", "delete", "import", "export"}),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - Insufficient permissions"),
     *     @OA\Response(response=404, description="Permission not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:255',
            'action' => ['required', Rule::in(['read', 'write', 'create', 'delete', 'import', 'export'])],
            'description' => 'nullable|string'
        ]);

        // Check for duplicate module-action combination
        if (Permission::where('module', $validated['module'])
                     ->where('action', $validated['action'])
                     ->where('id', '!=', $id)
                     ->exists()) {
            return response()->json([
                'message' => 'Permission with this module and action already exists'
            ], 422);
        }

        $permission->update([
            'name' => $validated['name'],
            'slug' => Str::slug("{$validated['module']}.{$validated['action']}"),
            'module' => $validated['module'],
            'action' => $validated['action'],
            'description' => $validated['description']
        ]);

        return response()->json([
            'message' => 'Permission updated successfully',
            'data' => $permission
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/permissions/{id}",
     *     summary="Delete a permission",
     *     description="Deletes a permission if it's not in use",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Permission ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permission deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - Insufficient permissions"),
     *     @OA\Response(response=404, description="Permission not found"),
     *     @OA\Response(response=422, description="Cannot delete permission in use")
     * )
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        // Check if permission is in use
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete permission that is assigned to roles'
            ], 422);
        }

        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/permissions/modules",
     *     summary="Get all permission modules",
     *     description="Returns list of all modules with their available permissions",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="actions", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="action", type="string"),
     *                             @OA\Property(property="description", type="string")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - Insufficient permissions")
     * )
     */
    public function getModules()
    {
        $modules = Permission::select('module')
            ->distinct()
            ->orderBy('module')
            ->get()
            ->pluck('module')
            ->map(function ($module) {
                return [
                    'name' => $module,
                    'actions' => Permission::where('module', $module)
                        ->orderBy('action')
                        ->get(['id', 'name', 'action', 'description'])
                ];
            });

        return response()->json([
            'data' => $modules
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/permissions/generate",
     *     summary="Generate module permissions",
     *     description="Generates default permissions for a specified module",
     *     tags={"Permissions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"module"},
     *             @OA\Property(property="module", type="string", example="employees"),
     *             @OA\Property(property="actions", type="array",
     *                 @OA\Items(type="string", enum={"read", "write", "create", "delete", "import", "export"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - Insufficient permissions"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function generateModulePermissions(Request $request)
    {
        $request->validate([
            'module' => 'required|string|max:255',
            'actions' => 'array'
        ]);

        $module = $request->module;
        $actions = $request->actions ?? ['read', 'write', 'create', 'delete', 'import', 'export'];

        Permission::generateForModule($module, $actions);

        return response()->json([
            'message' => 'Module permissions generated successfully',
            'data' => Permission::where('module', $module)->get()
        ]);
    }
}
