<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => [
                'email:rfc,dns',
                'required'
            ],
            'password' => [
                'required'
            ]
        ]);

        if ( $validator->fails() ) {
            return response()->json($validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        if ( !$token = auth()->guard('api_admin')->attempt($credentials) ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user'    => auth()->guard('api_admin')->user(),
            'token'   => $token
        ], 200);

    }

    public function getUser()
    {
        return response()->json([
            'success' => true,
            'user'    => auth()->guard('api_admin')->user()
        ], 200);
    }


    public function refreshToken(Request $request)
    {
        $refreshToken = JWTAuth::refresh(JWTAuth::getToken());

        $user = JWTAuth::setToken($refreshToken)->toUser();

        $request->headers->set('Authorization', 'Bearer ' . $refreshToken);

        return response()->json([
            'success' => true,
            'user'    => $user,
            'token'   => $refreshToken
        ], 200);
    }

    public function logout()
    {
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success' => true,
            'message' => 'Logout successfully'
        ], 200);
    }


}
