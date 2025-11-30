<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class News extends Model
{
    protected $table = 'news';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'description',
        'source',
        'link'
    ];

    public static function getNewsForToday() {
        $news = self::where('created_at', '>=', now()->subDays(1))->get();
        if ($news->isEmpty()) {
            self::updateDBFromSources();
            $news = self::where('created_at', '>=', now()->subDays(1))->get();
        }
        return $news;
    }

    public static function updateDBFromSources(): void
    {
        self::updateHNNews();

        if (env('TELETEXT_api')) {
            self::updateTeletextNews();
        }
    }

    private static function updateTeletextNews(): void {
        try {
            $teletextResponse = Http::get(env('TELETEXT_api') .'?app_id=' .env('TELETEXT_app_id') . '&app_key=' .env('TELETEXT_app_key'));
            $teletextResponseJson = $teletextResponse->json();
            $items = $teletextResponseJson['teletext']['page']['subpage'][0]['content'][1]['line'];
            $filteredNews = array_filter($items, fn($item) => key_exists('Text', $item) &&  str_starts_with($item['Text'], '{DH}'));
            $upsertData = array_map(fn($item) => [
                'description' => str_replace('{DH}', '', $item['Text']),
                'source' => 'teletext'
            ], $filteredNews);
            News::upsert($upsertData, uniqueBy: ['link'], update: ['description']);
        } catch (ConnectionException $e) {
            \Illuminate\Log\log('Failed to fetch teletext news');
        }
    }

    private static function updateHNNews() {
        try {
            $hnFrontPageResponse = Http::get('https://hn.algolia.com/api/v1/search?tags=front_page');
            $hnFrontPageJson = $hnFrontPageResponse->json();
            $filteredNews = array_filter($hnFrontPageJson['hits'], fn ($item) => $item['num_comments'] > 100);
            $upsertData = array_map(fn ($item) => [
                'description' => $item['title'] . " (" . $item['num_comments'] . " comments)",
                'source' => 'hackernews',
                'link' => 'https://news.ycombinator.com/item?id=' . $item['story_id']
            ] , $filteredNews);
            News::upsert($upsertData, uniqueBy: ['link'], update: ['description']);
        } catch (ConnectionException $e) {
            \Illuminate\Log\log('Failed to fetch hn news');
        }
    }

}
