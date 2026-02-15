<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Price Drop Alert</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        h1 { font-size: 1.25rem; margin-bottom: 1rem; }
        .price-old { text-decoration: line-through; color: #999; }
        .price-new { color: #16a34a; font-weight: 600; }
        .btn { display: inline-block; padding: 10px 20px; background: #2563eb; color: white !important; text-decoration: none; border-radius: 6px; margin-top: 1rem; }
        .btn:hover { background: #1d4ed8; }
        hr { border: none; border-top: 1px solid #e5e7eb; margin: 1.5rem 0; }
    </style>
</head>
<body>
    <h1>Price Drop Alert</h1>
    <p><strong>{{ $listing->title }}</strong></p>
    <p>
        <span class="price-old">${{ number_format($oldPrice) }}</span>
        &rarr;
        <span class="price-new">${{ number_format($newPrice) }}</span>
        <small>(saved ${{ number_format($oldPrice - $newPrice) }})</small>
    </p>
    <hr>
    <a href="{{ $listing->url }}" class="btn">View Listing</a>
</body>
</html>
