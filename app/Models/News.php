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

    public static function getItemsForToday() {
        $items = self::where('created_at', '>=', now()->subDays(1))->get();
        if ($items->isEmpty()) {
            self::updateDB();
            $items = self::where('created_at', '>=', now()->subDays(1))->get();
        }
        return $items;
    }

    public static function updateDB(): void
    {
        if (env('TELETEXT_URL')) {
            self::updateTeletextNews();
        } else {
            print('TELETEXT_URL is not set');
        }
        if (env('NEWS_URL')) {
            self::updateHNNews();
        } else {
            print('NEWS_URL is not set');
        }
    }

    private static function updateTeletextNews(): void {
        try {
            $response = Http::get(env('TELETEXT_URL') .'?app_id=' .env('TELETEXT_APP_ID') . '&app_key=' .env('TELETEXT_APP_KEY'));
            $responseJson = $response->json();
            $items = $responseJson['teletext']['page']['subpage'][0]['content'][1]['line'];
            $filteredItems = array_filter($items, fn($item) => key_exists('Text', $item) &&  str_starts_with($item['Text'], '{DH}'));
            $upsertData = array_map(fn($item) => [
                'description' =>  preg_replace('({DH}\d+ )', '', $item['Text']),
                'source' => 'teletext'
            ], $filteredItems);
            self::upsert($upsertData, uniqueBy: ['link'], update: ['description']);
        } catch (ConnectionException $e) {
            \Illuminate\Log\log('Failed to fetch teletext news');
        }
    }

    private static function updateHNNews() {
        try {
            $response = Http::get(env('NEWS_URL'));
            $responseJson = $response->json();
            $filteredItems = array_filter($responseJson['hits'], fn ($item) => $item['num_comments'] > 100);
            $upsertData = array_map(fn ($item) => [
                'description' => $item['title'],
                'source' => 'hackernews',
                'link' => 'https://news.ycombinator.com/item?id=' . $item['story_id']
            ] , $filteredItems);
            self::upsert($upsertData, uniqueBy: ['link'], update: ['description']);
        } catch (ConnectionException $e) {
            \Illuminate\Log\log('Failed to fetch hn news');
        }
    }

}
