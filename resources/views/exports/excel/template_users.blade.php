<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User</title>
</head>
<body>
    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>User Name</th>
            <th>Email</th>
            <th>Vendor Number</th>
            <th>Role</th>
            <th>Active</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $key => $user)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->vend_num }}</td>
                <td>{{ $user->nama_role }}</td>
                <td>
                    @if($user->is_active)
                        <span>A</span>
                    @else
                        <span>I</span>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>