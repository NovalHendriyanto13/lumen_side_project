<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'store']]);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('username', 'password');
        if (!$token = auth()->attempt($credentials)) {
            return $this->failed(['error' => 'Unauthorized']);
        }

        $user = auth()->user();
        $response = [
            'name' => $user->nama,
            'role' => $user->role,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ];

        return $this->success($response);
    }

    public function logout()
    {
        auth()->logout();

        return $this->success([], 'Successfully logged out');
    }

    public function me()
    {
        return $this->success(auth()->user());
    }

    public function index()
    {
        $users = User::all();
        return $this->success($users);
    }

    // Store a new user
    public function store(Request $request)
    {
        $validator = $this->validate($request, [
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|max:50',
            'no_telp' => 'required|string|max:255',
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'no_telp' => $request->no_telp,
            'alamat' => $request->alamat,
            'password' => $request->password, // Password will be hashed by the model
            'role' => $request->role,
            'user_kru' => !empty($request->user_kru) ? $request->user_kru : null,
        ]);

        return $this->success($user, 201);
    }

    // Show a specific user
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->failed([], 'User not found', 404);
        }

        return $this->success($user);
    }

    // Update an existing user
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->failed([], 'User not found', 404);
        }

        $validator = $this->validate($request, [
            'nama' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $id,
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6',
            'role' => 'sometimes|required|string|max:50',
        ]);

        $user->update($request->all());

        return $this->success($user);
    }

    // Delete a user
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->failed([], 'User not found', 404);
        }

        // $user->delete();

        return $this->success([], 'User deleted successfully');
    }

    // Show a specific user
    public function profile(Request $request)
    {

        $user = auth()->user();

        if (!$user) {
            return $this->failed([], 'User not found', 404);
        }

        return $this->success($user);
    }

    public function profileUpdate(Request $request)
    {

        $id = auth()->user()->id;

        $user = User::find($id);

        if (!$user) {
            return $this->failed([], 'User not found', 404);
        }

        $validator = $this->validate($request, [
            'nama' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $id,
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:6',
        ]);

        $user->update($request->all());

        return $this->success($user);
    }
}
