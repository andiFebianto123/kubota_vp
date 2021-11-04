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
            <th>Vendor Number</th>
            <th>PO Date</th>
            <th>Email Flag</th>
        </tr>
        </thead>
        <tbody>
        @foreach($purchase_orders as $key => $po)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $po->number }}</td>
                <td>{{ $po->vendor_number }}</td>
                <td>{{ $po->po_date }}</td>
                <td>{{ $po->email_flag }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>