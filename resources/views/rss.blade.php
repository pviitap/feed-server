<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>feed-server</title>
        <link>http://localhost:8000/rss</link>
        <description>feed-server</description>
        <language>en-us</language>
        @foreach ($items as $item)
        <item>
            <title>{{ $item->key }}</title>
            <description>{{ $item->description }}</description>
            <dc:creator> {{ $item->source }}</dc:creator>
            <dc:date> {{ $item-> created_at }}</dc:date>
        </item>
        @endforeach
    </channel>
</rss>
