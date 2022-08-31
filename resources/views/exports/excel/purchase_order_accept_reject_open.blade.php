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
                <th>Status Order</th>
                <th>Item</th>
                <th>Description</th>
                <th>Due Date</th>
                @if(App\Helpers\Constant::checkPermission('Show Price In PO A/R/Open Menu'))
                <th>Unit Price</th>
                @endif
                <th>Order Qty</th>
                <th>Change</th>
                <th>Status A/R/O</th>
            </tr>
            </thead>
            <tbody>
            @foreach($po_lines as $key => $po_line)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>{{ $po_line['po_num'] }}</td>
                    <td>{{ $po_line['po_line'] }}</td>
                    <td>{{$arr_po_line_status[$po_line['status']]['text']}}</td>
                    <td>{{ $po_line['item'] }}</td>
                    <td>{{ $po_line['description'] }}</td>
                    <td>{{ date('Y-m-d', strtotime($po_line['due_date'])) }}</td>
                    @if(App\Helpers\Constant::checkPermission('Show Price In PO A/R/Open Menu'))
                    <td>{{ $po_line['unit_price'] }}</td>
                    @endif
                    <td>{{ $po_line['order_qty'] }}</td>
                    <td>{{ $po_line['po_change'] }}</td>
                    <td>{{ $arr_po_line_aro[$po_line['accept_flag']]}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </body>
</html>