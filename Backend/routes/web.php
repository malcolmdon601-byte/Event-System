<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'EventFlow API. See /api for endpoints.']);
});
