<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Roles",
 *     description="API Endpoints for Role management"
 * )
 */
class RoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/roles",
     *     summary="Get list of all roles",
     *     description="Returns paginated list of roles with their permissions",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter roles by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "inactive"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search roles by name or description",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Administrator"),
     *                     @OA\Property(property="slug", type="string", example="admin"),
     *                     @OA\Property(property="description", type="string", example="Super admin with full access"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="permissions", type="array", @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="slug", type="string"),
     *                         @OA\Property(property="module", type="string"),
     *                         @OA\Property(property="action", type="string")
     *                     ))
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
        $query = Role::with('permissions');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $roles->items(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total()
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/roles",
     *     summary="Create a new role",
     *     description="Creates a new role with specified permissions",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","status"},
     *             @OA\Property(property="name", type="string", example="Project Manager"),
     *             @OA\Property(property="description", type="string", example="Manages project teams"),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role created successfully"),
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
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'permissions' => 'array'
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'status' => $validated['status']
        ]);

        if (!empty($validated['permissions'])) {
            $role->updatePermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Role created successfully',
            'data' => $role->load('permissions')
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/roles/{id}",
     *     summary="Get role details",
     *     description="Returns detailed information about a specific role",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
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
     *     @OA\Response(response=404, description="Role not found")
     * )
     */
    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'data' => $role
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/roles/{id}",
     *     summary="Update role details",
     *     description="Updates an existing role's information",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","status"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"active", "inactive"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - Insufficient permissions"),
     *     @OA\Response(response=404, description="Role not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])]
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'status' => $validated['status']
        ]);

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => $role->load('permissions')
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/roles/{id}",
     *     summary="Delete a role",
     *     description="Deletes a role if it's not a system role",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized or Cannot delete system role"),
     *     @OA\Response(response=404, description="Role not found")
     * )
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deletion of essential roles
        if (in_array($role->slug, ['admin', 'manager', 'employee'])) {
            return response()->json([
                'message' => 'Cannot delete essential system roles'
            ], 403);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/roles/{id}/permissions",
     *     summary="Get role permissions",
     *     description="Returns permissions for a specific role grouped by module",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="role_id", type="integer"),
     *                 @OA\Property(property="role_name", type="string"),
     *                 @OA\Property(property="modules", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - Insufficient permissions"),
     *     @OA\Response(response=404, description="Role not found")
     * )
     */
    public function getPermissions($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        $permissions = $role->permissions->groupBy('module')
            ->map(function ($modulePermissions) {
                return $modulePermissions->pluck('action', 'slug')->toArray();
            });

        return response()->json([
            'data' => [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'modules' => $permissions
            ]
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/roles/{id}/permissions",
     *     summary="Update role permissions",
     *     description="Updates the permissions assigned to a role",
     *     tags={"Roles"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(property="permissions", type="array",
     *                 @OA\Items(type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Permissions updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized - Insufficient permissions"),
     *     @OA\Response(response=404, description="Role not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updatePermissions(Request $request, $id)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::findOrFail($id);
        $role->updatePermissions($request->permissions);

        return response()->json([
            'message' => 'Permissions updated successfully',
            'data' => $role->load('permissions')
        ]);
    }
}
