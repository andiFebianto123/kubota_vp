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
                <th>PO Number</th>
                <th>Item</th>
                <th>Description</th>
                <th>Qty</th>
                <th>UM</th>
                <th>Unit Price</th>
                <th>Total Price</th>
            </tr>
            </thead>
            <tbody>
            @foreach($purchase_order_lines as $key => $po)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $po->number }} - {{ $po->po_line }}</td>
                    <td>{{ $po->item }}</td>
                    <td>{{ $po->description }}</td>
                    <td>{{ $po->order_qty }}</td>
                    <td>{{ $po->u_m }}</td>
                    <td>{{ $po->unit_price }}</td>
                    <td>{{ $po->order_qty*$po->unit_price }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </body>
</html>