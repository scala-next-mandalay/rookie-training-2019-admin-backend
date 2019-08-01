<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->group(function () {
    Route::get('/autheduser', function () {
        return response()->json(auth()->user());
    });

    Route::ApiResource(
        'items', 
        'ItemsController', 
        ['except' => ['create', 'edit']]
    );
    Route::ApiResource(
        'categories', 
        'CategoriesController', 
        ['except' => ['create', 'edit']]
    );

    Route::ApiResource(
        'orderitems', 
        'OrderItemController', 
        ['except' => ['create', 'edit']]
    );

    Route::apiResource('orders', 'OrdersController')->only(['store','index','destroy','show']);

    Route::post('posttest', function (Request $request) {
        $data = $request->all();
        return response()->json($data);
    });
});

Route::get('/', function () {
    return "public endpoint";
});

