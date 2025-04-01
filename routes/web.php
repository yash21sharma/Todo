<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/todo', [TaskController::class, 'index']);
Route::get('/tasks', [TaskController::class, 'fetchTasks']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle']); 
Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

