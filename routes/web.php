<?php

use Illuminate\Support\Facades\Route;

// This project is API-only. The frontend is a separate repository.
Route::get('/', fn () => response()->json(['name' => config('app.name'), 'status' => 'ok']));
