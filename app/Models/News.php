<?php

namespace App\Models;

use App\Traits\Refreshable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;


class News extends Model
{
    use Refreshable;
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
    private static function fetchItems(): array {
        $response = Http::get(env('NEWS_URL'));
        $responseJson = $response->json();
        $filteredItems = array_filter($responseJson['hits'], fn ($item) => $item['num_comments'] > 100);
        $upsertData = array_map(fn ($item) => [
            'description' => $item['title'],
            'source' => 'hackernews',
            'link' => 'https://news.ycombinator.com/item?id=' . $item['story_id']
        ] , $filteredItems);
        return $upsertData;
    }

}
