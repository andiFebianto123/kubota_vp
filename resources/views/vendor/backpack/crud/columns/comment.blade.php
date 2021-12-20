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
                        id="comment"
                        data-id-tax-invoice = "{{ $entry->id }}"
                    >
                        <strong>{{ $column['text'] }}</strong>
                    </a>
                @else
                    <a href="javascript:void(0)" class="text-info" data-toggle="modal" data-target=".bd-example-modal-lg" id="comment"><strong>{{ $column['text'] }}</strong></span>
                @endif
            @else
                <a href="javascript:void(0)" id="comment" data-id-tax-invoice="{{ $entry->id }}" class="text-dark">{{ $column['text'] }}</span>
            @endif
            @if($entry->comment == null)
                <a href="javascript:void(0)" id="comment" class="text-info"><i>Add Comment</i></a>
            @endif
        @else
            {!! $column['text'] !!}
        @endif
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</div>
<script>
    var entries = {!! json_encode($entry) !!};
    console.log(entries);
    if(typeof openCommentModal != 'function'){
        function openCommentModal(){
            $('a#comment').click(function(e){
                $('.comment-modal').removeAttr('data-id-tax-invoice');
                let tax_id = $(this).attr('data-id-tax-invoice');
                if(tax_id !== undefined){
                    $('.comment-modal').attr('data-id-tax-invoice', tax_id);
                }
                $('.comment-modal').modal('show');
            });
        }
        openCommentModal();
    }
</script>
