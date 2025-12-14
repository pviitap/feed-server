<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\Http;
use Date;

class Summary extends Model
{
    public static function getItemsForToday() {
        $summaries = self::where('created_at', '>=', now()->subDays(1))->get();
        if ($summaries->isEmpty()) {
            self::updateDB();
            $summaries = self::where('created_at', '>=', now()->subDays(1))->get();
        }
        return $summaries;
    }

    public static function updateDB(): void {

        [$r1, $r2, $r3] = Concurrency::run([
            fn () => self::updateItems(Posting::getItemsForToday(), 'POSTING'),
            fn () => self::updateItems(News::getItemsForToday(), 'NEWS'),
            fn () => self::updateItems(Event::getItemsForToday(), 'EVENT')
        ]);
    }

    public static function updateItems(Collection $items, string $type): void {
        $descriptions = $items->pluck('description')->toArray();
        $response = Http::timeout(60)->withHeaders([
                'content-type' => 'application/json',
                'Authorization' => 'Bearer ' . env('COMPLETIONS_KEY')
            ]
        )->post(env('COMPLETIONS_URL'),[
                'model' => env('COMPLETIONS_MODEL'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => env($type . '_PROMPT') .' ' . json_encode($descriptions)
                    ]
                ],
                'max_tokens' => 10000
            ]
        );
        $content = $response->json()['choices'][0]['message']['content'];

        $upsertData = [[
            'key' => $type .'_' . Date::now()->format('Y-m-d'),
            'description' => $content,
        ]];
        self::upsert($upsertData, uniqueBy: ['key'], update: ['description']);
    }
}
