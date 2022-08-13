<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('admin')->group(function () {

    Route::post('login', [
        \App\Http\Controllers\Api\Admin\LoginController::class,
        'index',
        [ 'as' => 'admin' ]
    ]);

    Route::middleware([ 'auth:api_admin' ])->group(function () {

        // login controller
        Route::get('user', [
            \App\Http\Controllers\Api\Admin\LoginController::class,
            'getUser',
            [ 'as' => 'admin' ]
        ]);

        Route::get('refresh', [
            \App\Http\Controllers\Api\Admin\LoginController::class,
            'refreshToken',
            [ 'as' => 'admin' ]
        ]);

        Route::post('logout', [
            \App\Http\Controllers\Api\Admin\LoginController::class,
            'logout',
            [ 'as' => 'admin' ]
        ]);

        // end login controller

        // dashboard controller

        Route::get('dashboard', [
            \App\Http\Controllers\Api\Admin\DashboardController::class,
            'index',
            [ 'as' => 'admin' ]
        ]);

        // end dashboard controller

        // category controller
        Route::apiResource('categories',
            \App\Http\Controllers\Api\Admin\CategoryController::class,
            [
                'except' => [
                    'create',
                    'edit'
                ],
                'as'     => 'admin'
            ]);
        // end category controller

        // product controller
        Route::apiResource('products',
            \App\Http\Controllers\Api\Admin\ProductController::class,
            [
                'except' => [
                    'create',
                    'edit'
                ],
                'as'     => 'admin'
            ]);
        // end product controller

        // invoice controller
        Route::apiResource('invoices',
            \App\Http\Controllers\Api\Admin\InvoiceController::class,
            [
                'except' => [
                    'create',
                    'edit',
                    'store',
                    'update',
                    'destroy'
                ],
                'as'     => 'admin'
            ]);
        // end invoice controller


    });

});
