<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Low Stock Report</title>
</head>
<body>
<h2>Low Stock Report</h2>

@if($products->isEmpty())
    <p>No low stock items found.</p>
@else
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
        <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Current Qty</th>
            <th>Minimum Qty</th>
            <th>Warehouse</th>
            <th>Location</th>
            <th>Country</th>
            <th>Supplier</th>
            <th>Supplier Contact</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $item)
            <tr>
                <td>{{ $item['product_name'] }}</td>
                <td>{{ $item['sku'] }}</td>
                <td>{{ $item['current_quantity'] }}</td>
                <td>{{ $item['minimum_required'] }}</td>
                <td>{{ $item['warehouse_name'] }}</td>
                <td>{{ $item['warehouse_location'] }}</td>
                <td>{{ $item['country'] }}</td>
                <td>{{ $item['supplier_name'] }}</td>
                <td>{{ $item['supplier_contact_info'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
</body>
</html>
