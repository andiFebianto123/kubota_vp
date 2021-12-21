@php
    $column['text'] = $column['value'] ?? '';
    $column['escaped'] = $column['escaped'] ?? false;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';

    if(!empty($column['text'])) {
        $column['text'] = $column['prefix'].$column['text'].$column['suffix'];
    }
@endphp

@if($column['text'] == 1)
<span class="text-success">
    <i class="la la-check"></i>
</span>
@else
<span class="text-danger">
    <i class="la la-times"></i>
</span>
@endif
