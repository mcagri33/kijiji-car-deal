<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listing Sold</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        h1 { font-size: 1.25rem; margin-bottom: 1rem; }
        .sold { color: #dc2626; font-weight: 600; }
        .btn { display: inline-block; padding: 10px 20px; background: #6b7280; color: white !important; text-decoration: none; border-radius: 6px; margin-top: 1rem; }
        .btn:hover { background: #4b5563; }
        hr { border: none; border-top: 1px solid #e5e7eb; margin: 1.5rem 0; }
    </style>
</head>
<body>
    <h1>Listing No Longer Available</h1>
    <p><strong>{{ $listing->title }}</strong></p>
    <p class="sold">This listing is no longer available (sold or removed).</p>
    @if($listing->price)
        <p>Last known price: ${{ number_format($listing->price) }}</p>
    @endif
    <hr>
    <a href="{{ $listing->url }}" class="btn">View Listing</a>
</body>
</html>
