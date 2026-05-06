<?php

use App\Http\Controllers\Main\SearchController;
use Illuminate\Support\Facades\Route;

Route::post('/search', [SearchController::class, 'search']);
