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
                <th>Serial Number</th>
            </tr>
        </thead>
        <tbody>
            @if($qty > 0)
            @for($i = 0; $i < $qty; $i++)
            <tr>
                <td>{{$i+1}}</td>
            </tr>
            @endfor
            @endif
        </tbody>
    </table>
</body>
</html>