<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::apiResources(
    [
        'product' => \App\Http\Controllers\ProductController::class,
        'material' => \App\Http\Controllers\MaterialController::class,
    ]
);
Route::post('warehouse', [\App\Http\Controllers\WarehouseController::class, 'store']);
Route::post('order', [\App\Http\Controllers\WarehouseController::class, 'getProductMaterials']);
