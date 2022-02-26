<?php

use App\Http\Controllers\PortofolioController;
use App\Http\Middleware\CheckApiPassword;
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

Route::prefix('portofolio')->group(function () {
    Route::get('/',[PortofolioController::class,"index"]);
    Route::get('/{id}/image',[PortofolioController::class,"getImage"]);
    Route::middleware('api_key')->group(function(){
        Route::post('/',[PortofolioController::class,"store"]);
        Route::post('/{id}/image',[PortofolioController::class,"storeImage"]);
        Route::delete('/{id}/image/{imageId}',[PortofolioController::class,"deleteImage"]);
    });
});
