<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>feed-server</title>
    </head>
    <body>
        <div>
            <ul>
                @foreach ($items as $item)
                <li>{{ $item->description }}</li>
                @endforeach
            </ul>
        </div>
    </body>
</html>
