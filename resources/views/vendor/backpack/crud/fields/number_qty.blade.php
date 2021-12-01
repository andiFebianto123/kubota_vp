<!-- number input -->
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <div class="input-group-prepend"><span class="input-group-text">{!! $field['prefix'] !!}</span></div> @endif
        <span class="info-qty text-danger">@if($field['default'] <= 0)<small> Jumlah Qty Sudah Penuh </small>@endif</span>
        <input
            id="current-qty"
        	type="number"
        	name="{{ $field['name'] }}"
            value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
            @include('crud::fields.inc.attributes')
        	>
        @if(isset($field['suffix'])) <div class="input-group-append"><span class="input-group-text">{!! $field['suffix'] !!}</span></div> @endif

    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')
@push('crud_fields_scripts')
<script>
    var actualQty = "{{$field['default']}}"
$( "#current-qty" ).keyup(function() {
    if (parseFloat(actualQty) < parseFloat($(this).val())) {
        $('.info-qty').html('<small>Jumlah Qty melebihi batas maksimal ('+actualQty+')</small>')
    }else{
        $('.info-qty').html('')
    }
});
</script>

@endpush
