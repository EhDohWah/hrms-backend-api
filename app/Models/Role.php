<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (empty($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
            $role->created_by = auth()->user()->name ?? 'system';
            $role->updated_by = auth()->user()->name ?? 'system';
        });

        static::updating(function ($role) {
            $role->updated_by = auth()->user()->name ?? 'system';
        });
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class)
            ->withTimestamps()
            ->withPivot(['created_by', 'updated_by']);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot(['created_by', 'updated_by']);
    }

    public function hasPermission($permission)
    {
        return $this->permissions()
            ->where('slug', $permission)
            ->exists();
    }

    public function hasPermissionTo($permission)
    {
        return $this->hasPermission($permission);
    }

    public function givePermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching([$permission->id => [
            'created_by' => auth()->user()->name ?? 'system',
            'updated_by' => auth()->user()->name ?? 'system'
        ]]);
    }

    public function withdrawPermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }

        $this->permissions()->detach($permission);
    }

    public function updatePermissions($permissions)
    {
        if (is_array($permissions)) {
            $permissionIds = Permission::whereIn('id', $permissions)->pluck('id');

            $syncData = [];
            foreach ($permissionIds as $id) {
                $syncData[$id] = [
                    'created_by' => auth()->user()->name ?? 'system',
                    'updated_by' => auth()->user()->name ?? 'system'
                ];
            }

            $this->permissions()->sync($syncData);
        }
    }

    public function getPermissionsByModule()
    {
        return $this->permissions()
            ->get()
            ->groupBy('module')
            ->map(function ($permissions) {
                return $permissions->pluck('action', 'slug');
            });
    }
}
