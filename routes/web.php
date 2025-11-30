<?php

use Illuminate\Support\Facades\Route;
use App\Models\News;

Route::get('/', function () {
    $news = News::where('created_at', '>=', now()->subDays(1))->get();
    if ($news->isEmpty()) {
        $news = News::updateDBFromSources();
    }
    return view('index', ['news' => $news]);
});
