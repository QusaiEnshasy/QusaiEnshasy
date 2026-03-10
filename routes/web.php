<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/board', function () {
    return view('board');
});
Route::get('/test', function () {
    return view('test');
});