<?php

use Illuminate\Support\Facades\Route;
use App\Models\News;
use App\Models\Summary;

Route::get('/', function () {
    return view('index', ['items' => News::getItemsForToday()]);
});

Route::get('/rss', function () {
    return view('rss', ['items' => Summary::getItemsForToday()]);
});

Route::get('/summary', function () {
    return view('index', ['items' => Summary::getItemsForToday()]);
});
