<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AeropuertoController;
use App\Http\Controllers\Api\VueloController;
use App\Http\Controllers\Api\ReservaController;

Route::post('/login', [UsuarioController::class, 'login']);
Route::post('/usuarios/crear', [UsuarioController::class, 'store']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);
    
    Route::put('/usuarios/editar/{id}', [UsuarioController::class, 'update']);
    Route::patch('/usuarios/editar-parcial/{id}', [UsuarioController::class, 'updatePartial']);
    Route::delete('/usuarios/eliminar/{id}', [UsuarioController::class, 'destroy']);
    
    Route::post('/aeropuertos', [AeropuertoController::class, 'obtenerAeropuerto']);
    Route::post('/vuelos', [VueloController::class, 'obtenerVuelo']);

    Route::get('/reservas', [ReservaController::class, 'index']);
    Route::get('/reservas/{id}', [ReservaController::class, 'show']);
    Route::post('/reservas/crear', [ReservaController::class, 'store']);
    Route::put('/reservas/editar/{id}', [ReservaController::class, 'update']);
    Route::delete('/reservas/{id}', [ReservaController::class, 'destroy']);
});

