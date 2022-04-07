<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mass DS</title>
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
                <th>Due Date</th>
                @if(App\Helpers\Constant::checkPermission('Show Price In PO Menu'))
                <th>Unit Price</th>
                @endif
                <th>Available Qty</th>
                <th>Order Qty</th>
                <th>Change</th>
                <th>Qty</th>
                <th>DS Delivery Date (ex. 2021-12-30)</th>
                <th>Petugas Vendor</th>
                <th>No Surat Jalan</th>
            </tr>
            </thead>
            <tbody>
            @foreach($po_lines as $key => $po_line)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $po_line['po_num'] }}</td>
                    <td>{{ $po_line['po_line'] }}</td>
                    <td>{{ $po_line['item'] }}</td>
                    <td>{{ $po_line['description'] }}</td>
                    <td>{{ date('Y-m-d', strtotime($po_line['due_date'])) }}</td>
                    @if(App\Helpers\Constant::checkPermission('Show Price In PO Menu'))
                    <td>{{ $po_line['unit_price'] }}</td>
                    @endif
                    <td>{{ $po_line['available_qty'] }}</td>
                    <td>{{ $po_line['order_qty'] }}</td>
                    <td>{{ $po_line['po_change'] }}</td>
                    <td></td>
                    <td></td>
                    <td>{{Auth::guard('backpack')->user()->name}}</td>
                    <td></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </body>
</html>