<?php

namespace App\Models;

use App\Traits\Refreshable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Teletext extends Model
{
    use Refreshable;
    protected $table = 'teletexts';
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
        $response = Http::get(env('TELETEXT_URL') .'?app_id=' .env('TELETEXT_APP_ID') . '&app_key=' .env('TELETEXT_APP_KEY'));
        $responseJson = $response->json();
        $items = $responseJson['teletext']['page']['subpage'][0]['content'][1]['line'];
        $filteredItems = array_filter($items, fn($item) => key_exists('Text', $item) &&  str_starts_with($item['Text'], '{DH}'));
        $upsertData = array_map(fn($item) => [
            'description' =>  preg_replace('({DH}\d+ )', '', $item['Text']),
            'source' => 'teletext'
        ], $filteredItems);
        return $upsertData;
    }
}
