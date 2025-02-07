<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'module',
        'action',
        'description',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'action' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($permission) {
            if (empty($permission->slug)) {
                $permission->slug = Str::slug("{$permission->module}.{$permission->action}");
            }
            $permission->created_by = auth()->user()->name ?? 'system';
            $permission->updated_by = auth()->user()->name ?? 'system';
        });

        static::updating(function ($permission) {
            $permission->updated_by = auth()->user()->name ?? 'system';
        });
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps()
            ->withPivot(['created_by', 'updated_by']);
    }

    public static function getModules()
    {
        return static::select('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');
    }

    public static function getActionsByModule($module)
    {
        return static::where('module', $module)
            ->orderBy('action')
            ->pluck('action');
    }

    public static function generateForModule($module, $actions = null)
    {
        $actions = $actions ?? ['read', 'write', 'create', 'delete', 'import', 'export'];

        foreach ($actions as $action) {
            static::firstOrCreate(
                [
                    'module' => $module,
                    'action' => $action,
                ],
                [
                    'name' => ucfirst($action) . ' ' . ucfirst($module),
                    'slug' => Str::slug("{$module}.{$action}"),
                    'description' => "Allows user to {$action} {$module}",
                ]
            );
        }
    }
}
