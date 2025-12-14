<?php

use App\Models\Teletext;
use Illuminate\Support\Facades\Route;
use App\Models\News;
use App\Models\Summary;
use App\Models\Event;
use App\Models\Posting;

Route::get('/', function () {
    return view('index', ['items' => Teletext::getItemsForToday()]);
});

Route::get('/events', function () {
    return view('index', ['items' => Event::getItemsForToday()]);
});

Route::get('/postings', function () {
    return view('index', ['items' => Posting::getItemsForToday()]);
});

Route::get('/news', function () {
    return view('index', ['items' => News::getItemsForToday()]);
});

Route::get('/rss', function () {
    return view('rss', ['items' => Summary::getItemsForToday()]);
});

Route::get('/summary', function () {
    return view('index', ['items' => Summary::getItemsForToday()]);
});
