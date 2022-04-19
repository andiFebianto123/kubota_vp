<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Purchase Order</title>
    </head>
    <body>
        <table>
            <thead>
            <tr>
                <th>No</th>
                @if(strpos(strtoupper(App\Helpers\Constant::getRole()), 'PTKI'))
                <th>Vendor Number</th>
                @endif
                <th>PO Number</th>
                <th>PO Date</th>
                <th>Email Flag</th>
                <th>PO Change</th>
            </tr>
            </thead>
            <tbody>
            @foreach($purchase_orders as $key => $po)
                <tr>
                    <td>{{ $key+1 }}</td>
                    @if(strpos(strtoupper(App\Helpers\Constant::getRole()), 'PTKI'))
                        <td>{{ $po->vendor_number }}</td>
                    @endif
                    <td>{{ $po->number }}</td>
                    <td>{{date("Y-m-d", strtotime($po->po_date)) }}</td>
                    <td>{{ ($po->email_flag) ? "✓":"-" }}</td>
                    <td>{{ $po->po_change }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </body>
</html>