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

    /**
     * @throws ConnectionException
     */
    public static function updateDBFromSources(): array
    {
        $response = Http::get('https://hn.algolia.com/api/v1/search?tags=front_page');

        $hnFrontPageItems = $response->json()['hits'];
        $filteredNews = array_filter($hnFrontPageItems, fn ($item) => $item['num_comments'] > 100);

        $upsertData = array_map(fn ($item) => [
            'description' => $item['title'] . " (" . $item['num_comments'] . " comments)",
            'source' => 'hackernews',
            'link' => 'https://news.ycombinator.com/item?id=' . $item['story_id']
        ] , $filteredNews);
        News::upsert($upsertData, uniqueBy: ['link'], update: ['description']);

        return $upsertData;
    }
}
