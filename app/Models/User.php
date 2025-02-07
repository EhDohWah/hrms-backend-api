<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Employee;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'last_login_at',
        'last_login_ip',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'status' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->created_by = auth()->user()->name ?? 'system';
            $user->updated_by = auth()->user()->name ?? 'system';
        });

        static::updating(function ($user) {
            $user->updated_by = auth()->user()->name ?? 'system';
        });

        // Clear user permissions cache on save/update
        static::saved(function ($user) {
            Cache::forget("user_{$user->id}_permissions");
            Cache::forget("user_{$user->id}_roles");
        });
    }

    /**
     * Get the employee record associated with the user.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'roles' => $this->roles->pluck('slug')->toArray()
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps()
            ->withPivot(['created_by', 'updated_by']);
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('slug', $role);
        }

        return !! $role->intersect($this->roles)->count();
    }

    public function hasPermission($permission)
    {
        $permissions = $this->getAllPermissions();

        if (is_string($permission)) {
            return $permissions->contains('slug', $permission);
        }

        return !! $permission->intersect($permissions)->count();
    }

    public function getAllPermissions()
    {
        return Cache::remember("user_{$this->id}_permissions", now()->addHours(24), function () {
            return $this->roles()
                ->with('permissions')
                ->get()
                ->map(function ($role) {
                    return $role->permissions;
                })
                ->flatten()
                ->unique('id');
        });
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching([$role->id => [
            'created_by' => auth()->user()->name ?? 'system',
            'updated_by' => auth()->user()->name ?? 'system'
        ]]);

        Cache::forget("user_{$this->id}_permissions");
        Cache::forget("user_{$this->id}_roles");
    }

    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->detach($role);

        Cache::forget("user_{$this->id}_permissions");
        Cache::forget("user_{$this->id}_roles");
    }

    public function syncRoles($roles)
    {
        if (is_array($roles)) {
            $roleIds = Role::whereIn('id', $roles)->pluck('id');

            $syncData = [];
            foreach ($roleIds as $id) {
                $syncData[$id] = [
                    'created_by' => auth()->user()->name ?? 'system',
                    'updated_by' => auth()->user()->name ?? 'system'
                ];
            }

            $this->roles()->sync($syncData);

            Cache::forget("user_{$this->id}_permissions");
            Cache::forget("user_{$this->id}_roles");
        }
    }

    public function getPermissionsByModule()
    {
        return $this->getAllPermissions()
            ->groupBy('module')
            ->map(function ($permissions) {
                return $permissions->pluck('action', 'slug');
            });
    }
}
