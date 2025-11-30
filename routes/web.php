<?php

use Illuminate\Support\Facades\Route;
use App\Models\News;

Route::get('/', function () {
    return view('index', ['news' => News::getNewsForToday()]);
});
