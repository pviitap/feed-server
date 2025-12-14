<?php

namespace App\Models;

use App\Traits\Refreshable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Event extends Model
{
    use Refreshable;

    protected $table = 'events';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'description',
        'source',
        'link'
    ];

    private static function fetchItems(string $url, $data): array {
        $response = Http::get($url);
        $responseJson = $response->json();
        sleep(1);

        \Illuminate\Log\log('Fetched ' .$url .' / ' . $responseJson['meta']['count']);

        $items = $responseJson['data'];

        $blacklistWords = explode(',',env('EVENT_IGNORE_WORDS'));
        $filteredItems = array_filter($items, function ($item) use ($blacklistWords) {
            return isset($item['name']['fi'])
                && isset($item['description']['fi'])
                && isset($item['short_description'])
                && !Str::contains(strtolower($item['description']['fi']), $blacklistWords);
        });

        $data = array_merge($data,
            array_map(fn($item) => [
                'description' => $item['name']['fi'] .'\n' . array_first($item['short_description']),
                'link' => $item['id'],
                'source' => 'events'
            ], $filteredItems)
        );

        if (!isset($responseJson['meta']['next']) || $responseJson['meta']['next'] == null) {
            return $data;
        } else {
            return self::fetchItems($responseJson['meta']['next'], $data);
        }
    }

}
