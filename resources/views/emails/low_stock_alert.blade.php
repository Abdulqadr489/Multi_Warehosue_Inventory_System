<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Low Stock Alert</title>
</head>
<body>
<h2>Low Stock Alert</h2>

<p>The following inventory item has reached its minimum quantity:</p>

<ul>
    <li><strong>Product:</strong> {{ $inventory->product?->name }} ({{ $inventory->product?->sku }})</li>
    <li><strong>Current Qty:</strong> {{ $inventory->quantity }}</li>
    <li><strong>Minimum Qty:</strong> {{ $inventory->minimum_quantity }}</li>
    <li><strong>Warehouse:</strong> {{ $inventory->warehouse?->name }} – {{ $inventory->warehouse?->location }}</li>
    <li><strong>Country:</strong> {{ $inventory->warehouse?->country?->name }}</li>
</ul>

</body>
</html>
