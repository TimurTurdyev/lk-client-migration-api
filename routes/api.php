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
Route::get('show/{tree_id}', [\App\Http\Controllers\TreeController::class, 'show']);
Route::get('export/{tree_id}/insert-to/{new_server_tree_id}', [\App\Http\Controllers\TreeController::class, 'export']);
