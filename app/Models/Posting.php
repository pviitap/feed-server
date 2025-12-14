<?php

namespace App\Models;

use App\Traits\Refreshable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Posting extends Model
{
    use Refreshable;

    protected $table = 'postings';
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
    private static function fetchItems(string $url, array $data): array {
        $response = Http::get($url);
        $responseJson = $response->json();

        $items = $responseJson['results'];

        $blacklistWords = explode(',',env('POSTING_IGNORE_WORDS'));
        $filteredItems = array_filter($items, function ($item) use ($blacklistWords) {
            return !Str::contains(strtolower($item['descr']), $blacklistWords);
        });

        $data = array_merge($data,
            array_map(fn($item) => [
                'description' => $item['company_name'] .'\n' . $item['heading'] . '\n' . $item['descr'],
                'link' => $item['apply_url'],
                'source' => 'postings'
            ], $filteredItems)
        );

        return $data;
    }

}
