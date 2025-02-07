<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create default modules and their permissions
        $modules = [
            'roles',
            'permissions',
            'users',
            'employees',
            'grants',
            'departments',
            'positions',
            'reports'
        ];

        foreach ($modules as $module) {
            Permission::generateForModule($module);
        }

        // Create roles
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Super administrator with full system access',
                'status' => 'active'
            ],
            [
                'name' => 'HR Manager',
                'slug' => 'hr-manager',
                'description' => 'HR Manager with full HR operations access',
                'status' => 'active'
            ],
            [
                'name' => 'HR Assistant',
                'slug' => 'hr-assistant',
                'description' => 'HR Assistant with limited HR operations access',
                'status' => 'active'
            ],
            [
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Regular employee with basic access',
                'status' => 'active'
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::create($roleData);

            // Assign permissions based on role
            switch ($role->slug) {
                case 'admin':
                    // Admin gets all permissions
                    $role->permissions()->attach(Permission::all());
                    break;

                case 'hr-manager':
                    // HR Manager gets all HR-related permissions
                    $managerPermissions = Permission::whereIn('module', [
                        'employees',
                        'departments',
                        'positions',
                        'reports',
                        'grants'
                    ])->get();
                    $role->permissions()->attach($managerPermissions);

                    // Add specific user management permissions
                    $userPermissions = Permission::where('module', 'users')
                        ->whereIn('action', ['read', 'create'])
                        ->get();
                    $role->permissions()->attach($userPermissions);
                    break;

                case 'hr-assistant':
                    // HR Assistant gets read and limited write permissions
                    $assistantPermissions = Permission::whereIn('module', [
                        'employees',
                        'departments',
                        'positions'
                    ])->whereIn('action', ['read', 'create'])->get();

                    // Add reports read permission
                    $reportsReadPerm = Permission::where('module', 'reports')
                        ->where('action', 'read')
                        ->get();
                    $role->permissions()->attach($assistantPermissions);
                    $role->permissions()->attach($reportsReadPerm);
                    break;

                case 'employee':
                    // Employee gets basic read permissions
                    $employeePermissions = Permission::whereIn('module', [
                        'employees',
                        'departments',
                        'positions'
                    ])->where('action', 'read')->get();
                    $role->permissions()->attach($employeePermissions);
                    break;
            }
        }

        // Create admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@hrms.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('Admin@123'),
                'email_verified_at' => now(),
                'status' => 'active'
            ]
        );

        // Create HR Manager user
        $hrManager = User::firstOrCreate(
            ['email' => 'hr.manager@hrms.com'],
            [
                'name' => 'HR Manager',
                'password' => bcrypt('HRManager@123'),
                'email_verified_at' => now(),
                'status' => 'active'
            ]
        );

        // Create HR Assistant user
        $hrAssistant = User::firstOrCreate(
            ['email' => 'hr.assistant@hrms.com'],
            [
                'name' => 'HR Assistant',
                'password' => bcrypt('HRAssist@123'),
                'email_verified_at' => now(),
                'status' => 'active'
            ]
        );

        // Assign roles to users
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'hr-manager')->first();
        $assistantRole = Role::where('slug', 'hr-assistant')->first();

        $admin->roles()->sync([$adminRole->id]);
        $hrManager->roles()->sync([$managerRole->id]);
        $hrAssistant->roles()->sync([$assistantRole->id]);
    }
}
