<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel</title>
</head>
<body>
    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>PO</th>
            <th>PO LINE</th>
            <th>Item</th>
            <th>Description</th>
            <th>Unit Price</th>
            <th>Order Qty</th>
            <th>Qty</th>
            <th>DS Delivery Date</th>
            <th>Petugas Vendor</th>
            <th>No Surat Jalan</th>
        </tr>
        </thead>
        <tbody>
        @foreach($po_lines as $key => $po_line)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $po_line->po_num }}</td>
                <td>{{ $po_line->po_line }}</td>
                <td>{{ $po_line->item }}</td>
                <td>{{ $po_line->description }}</td>
                <td>{{ $po_line->unit_price }}</td>
                <td>{{ $po_line->order_qty }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>