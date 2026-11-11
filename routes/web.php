<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'catalog');
Route::get('/login', fn () => redirect('/'))->name('login');
