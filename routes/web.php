<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\NewsComponent;
use App\Http\Controllers\NewsController;
Route::get('/', function () {
    return view('welcome');
});

