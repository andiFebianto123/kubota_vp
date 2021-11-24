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
            <th>DS Delivery Date</th>
            <th>Petugas Vendor</th>
            <th>No Surat Jalan</th>
            <th>Order Qty</th>
        </tr>
        </thead>
        <tbody>
        @foreach($po_lines as $key => $po_line)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $po_line->po_num }}</td>
                <td>{{ $po_line->po_line }}</td>
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