<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function index()
    {
        $users = User::when(request()->q, function ($users) {
            $users = $users->where('name', 'like', '%' . request()->q . '%');
        })->latest()->paginate(5);

        return new UserResource(true, 'List of users', $users);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => [
                'required',
                'string',
            ],
            'email'    => [
                'required',
                'string',
                'email',
                'unique:users',
            ],
            'password' => [
                'required',
                'confirmed'
            ],
        ]);

        if ( $validator->fails() ) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ( $user ) {
            return new UserResource(true, 'User created successfully', $user);
        }

        return new UserResource(false, 'User not created', null);
    }

    public function show($id)
    {
        $user = User::whereId($id)->first();

        if ( $user ) {
            return new UserResource(true, 'User found', $user);
        }

        return new UserResource(false, 'User not found', null);
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name'     => [
                'required',
                'string',
            ],
            'email'    => [
                'required',
                'string',
                'email',
                'unique:users,email,' . $user->id,
            ],
            'password' => [
                'nullable',
                'confirmed'
            ],
        ]);

        if ( $validator->fails() ) {
            return response()->json($validator->errors(), 422);
        }

        if ( $request->password === '' ) {
            $user->update([
                'name'  => $request->name,
                'email' => $request->email,
            ]);
        }

        $user->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ( $user ) {
            return new UserResource(true, 'User updated successfully', $user);
        }

        return new UserResource(false, 'User not updated', null);
    }

    public function destroy(User $user)
    {
        if ( $user->delete() ) {
            return new UserResource(true, 'User deleted successfully', $user);
        }

        return new UserResource(false, 'User not deleted', null);
    }

}