@inject('constant', 'App\Helpers\Constant')
<table>
    <thead>
        @if($type == 'week')
            <tr>
                <th rowspan="2" style="border:1px solid black; width: 30px;">Nama Item</th>
            @foreach($crud['columnHeader'] as $header)
                <th colspan="4" style="text-align:center; border:1px solid black;">
                    {!! $header !!}
                </th>
            @endforeach
                {{-- <th></th> --}}
            </tr>
        @endif
        @if($type == 'days')
            <tr>
                <th rowspan="2" style="border:1px solid black; width: 30px;">Nama Item</th>
            @foreach($crud['columnHeader'] as $header)
                <?php
                    $key = $header['key'].'-01';
                    $newDate = new DateTime($key);
                    $key = $newDate->format('F Y');
                    $colspan = count($header['data']);
                ?>
                <th colspan="{{ $colspan }}" class="" style="border:1px solid black; text-align: center;">
                    <strong>{!! $key !!}</strong>
                </th>
            @endforeach
                {{-- <th></th> --}}
            </tr>
            @endif
            <tr>
            {{-- Table columns --}}
            @foreach ($crud['columns'] as $column)
                @if(($column['label'] != 'Nama Item') && ($type == 'week' || $type == 'days'))
                <?php
                    $style = "";
                    if($type == 'week'){
                        if($column['type'] == 'forecast'){
                            $style = "
                                border: 1px solid black; 
                                width: 25px;
                                text-align: center;
                            ";
                        }
                    }else if($type == 'days'){
                        $getKey = explode('-', $column['label']);
                        if($column['label'] != "Nama Item"){
                            $search = $constant::getColumnHeaderDays($crud['columnHeader'], "{$getKey[0]}-{$getKey[1]}", $column['label']);
                            if($search['search'] == 0){
                                $style = "
                                    border-left: 1px solid #ddd;
                                    text-align: center;
                                ";
                            }
                            $column['label'] = "{$getKey[2]}";
                        }
                    }
                ?>
                @endif
                @php
                    if($type == 'month'){
                        $style = "
                            border: 1px solid black; 
                            width: 15px;
                            text-align: center;
                        ";
                        if($column['label'] == 'Nama Item'){
                            $style = "
                                border: 1px solid black; 
                                width: 30px;
                                text-align: center;
                            ";
                        }
                    }
                if(($type == 'week' || $type == 'days') && $column['label'] == 'Nama Item'){
                    continue;
                }
                @endphp
                <th
                    style="{{ $style ?? '' }}"
                >
                    @if(isset($column['link']))
                    <a href="{{url('admin/forecast')}}{{$column['link']}}{{$column['label']}}">{{$column['label']}}</a>
                    @else
                    <strong>{!! $column['label'] !!}</strong>
                    @endif
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($result as $forecast)
        @php
                $style = "
                    border: 1px solid black; 
                ";
        @endphp
            <tr>
                @foreach($forecast as $value)
                    <td style="{{ $style }}">{!! $value !!}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>