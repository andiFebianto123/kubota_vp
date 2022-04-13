<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $title }}</title>
    </head>
    <?php
        $no = 1;
    ?>
    <body>
        <table>
            <thead>
            <tr>
                @foreach($headers as $key => $header) 
                    <td>{{ $header }}</td>
                @endforeach
            </tr>
            </thead>
            <tbody>
                @foreach($datas as $data)
                <tr>
                    @foreach($headers as $key => $h)
                        @php
                            $value = $resultValue($data)[$key];
                        @endphp
                        <td>
                            @if ($value == "<number>")
                                {{ $no }}
                            @elseif (is_callable($value))
                                {!! $value($data) !!}
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                </tr>
                    <?php  $no++; ?>
                @endforeach
            </tbody>
        </table>
    </body>
</html>