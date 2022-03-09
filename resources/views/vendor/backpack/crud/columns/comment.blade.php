{{-- regular object attribute --}}
@php
	$value = data_get($entry, $column['name']);
    $value = is_array($value) ? json_encode($value) : $value;

    $column['escaped'] = $column['escaped'] ?? true;
    $column['limit'] = $column['limit'] ?? 40;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['text'] = $column['prefix'].Str::limit($value, $column['limit'], '[...]').$column['suffix'];
@endphp

<div>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        @if($column['escaped'])
            @if($entry->status == 1)
                @if($entry->user == backpack_user()->id)
                    <a href="javascript:void(0)" 
                        class="text-success" 
                        id="comment-{{ $entry->id }}"
                        data-id-tax-invoice = "{{ $entry->id }}"
                        data-route="{{ url('admin/send-comments') }}"
                        onclick="openCommentModal('{{ $entry->id }}','{{$entry->executed_flag}}')"
                    >
                        <strong>{{ $column['text'] }}</strong>
                    </a>
                @else
                    <a href="javascript:void(0)" 
                        class="text-info" 
                        data-toggle="modal" 
                        data-id-tax-invoice="{{ $entry->id }}" 
                        data-target=".bd-example-modal-lg" 
                        data-route="{{ url('admin/send-comments') }}"
                        onclick="openCommentModal('{{ $entry->id }}','{{$entry->executed_flag}}')"
                        id="comment-{{ $entry->id }}"
                    >
                        <strong>{{ $column['text'] }}</strong>
                    </a>
                @endif
            @else
                <a href="javascript:void(0)" 
                    id="comment-{{ $entry->id }}"
                    data-id-tax-invoice="{{ $entry->id }}" 
                    class="text-dark"
                    onclick="openCommentModal('{{ $entry->id }}','{{$entry->executed_flag}}')"
                    data-route="{{ url('admin/send-comments') }}"
                >
                    {{ $column['text'] }}
                </a>
            @endif
            @if($entry->executed_flag == 0)
                @if($entry->comment == null)
                    <a href="javascript:void(0)" 
                        id="comment" 
                        data-id-tax-invoice="{{ $entry->id }}" 
                        class="text-info"
                        onclick="openCommentModal('{{$entry->executed_flag}}')"
                        data-route="{{ url('admin/send-comments') }}"
                    >
                        <i>Add Comment</i>
                    </a>
                @endif
            @endif
        @else
            {!! $column['text'] !!}
        @endif
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</div>
