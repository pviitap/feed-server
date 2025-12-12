<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Event extends Model
{
    protected $table = 'events';
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
        if (env('EVENTS_URL')) {
            self::updateEvents();
        } else {
            print('EVENTS_URL not set');
        }
    }

    private static function updateEvents(): void {
        try {
            $upsertData = [];
            for ($i=1; $i<10; $i++) {
                $response = Http::get(env('EVENTS_URL') . '&page=' . $i);
                $responseJson = $response->json();
                $items = $responseJson['data'];
                $filteredItems = $items;

                $upsertData = array_merge($upsertData,
                    array_map(fn($item) => [
                        #'description' => $item['name']['fi'] .'\n' .  $item['description']['fi'],
                        'description' => array_first($item['short_description']),
                        'link' => $item['id'],
                        'source' => 'events'
                    ], $filteredItems));
                sleep(0.5);
            }
            self::upsert($upsertData, uniqueBy: ['link'], update: ['description']);
        } catch (ConnectionException $e) {
            \Illuminate\Log\log('Failed to fetch events');
        }
    }

}
