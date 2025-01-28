<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    //Get all users
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    //Store a new user
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email:rfc,dns|unique:users,email',  
            'password' => 'required|string|confirmed'
        ]);

        // Create a new user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        // Return the user object and a message
        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

}
