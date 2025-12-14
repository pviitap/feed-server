<?php

namespace App\Traits;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;

trait Refreshable {

    public static function getItemsForToday(): Collection {
        $summaries = self::where('created_at', '>=', now()->subDays(1))->get();
        if ($summaries->isEmpty()) {
            self::updateDB();
            $summaries = self::where('created_at', '>=', now()->subDays(1))->get();
        }
        return $summaries;
    }

    public static function updateDB(): void {
        $name = strtoupper(array_last(explode("\\", self::class)));
        \Illuminate\Log\log('Updating ' . $name . ' ...');
        if (env($name . '_URL')) {
            self::updateItems();
        } else {
            print($name . '_URL' . ' not set');
        }
    }

    private static function updateItems(): void {
        $name = strtoupper(array_last(explode("\\", self::class)));
        try {
            $upsertData = self::fetchItems( env($name .'_URL'), []);
            self::upsert($upsertData, uniqueBy: ['link'], update: ['description']);
        } catch (ConnectionException $e) {
            \Illuminate\Log\log('Failed to fetch ' . $name, [$e->getMessage()]);
        }
    }
}
