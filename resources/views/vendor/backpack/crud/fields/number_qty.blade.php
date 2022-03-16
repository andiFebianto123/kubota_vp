<!-- number input -->
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <div class="input-group-prepend"><span class="input-group-text">{!! $field['prefix'] !!}</span></div> @endif
        <span class="info-qty text-danger"></span>
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
    $( document ).ready(function() {
        var maxQty = parseFloat( $( "#current-qty" ).data('max'))
        var initCurrent = parseFloat( $( "#current-qty" ).val())
        var initUrl = $('#template-upload-sn').attr('init-url')
        if (parseFloat(initCurrent) > parseFloat(maxQty)) {
            $('.info-qty').html('<small>Jumlah Qty melebihi batas maksimal ('+maxQty+')</small>')
        }

        $('#template-upload-sn').attr('href', initUrl+'?qty='+maxQty)
        $('#allowed-qty').val(initUrl)
        $( "#current-qty" ).keyup(function() {
            var initUrl = $('#template-upload-sn').attr('init-url')
            var currentQty = parseFloat($(this).val())
            $('#template-upload-sn').attr('href', initUrl+'?qty='+currentQty)
            $('#allowed-qty').val(currentQty)
            if (parseFloat(currentQty) > parseFloat(maxQty)) {
                var message = "Jumlah qty melebihi batas (max. "+maxQty+")"
                $('.info-qty').html('<small>'+message+'</small>')
                $('.list-error').html('<li>'+message+'</li>')
            }else{
                $('.info-qty').html('')
                $('.list-error').html('')
            }
            if($('*').hasClass('form-issued')){
                outhouseTableManager(currentQty)
            } 
        });
    });

    
</script>

@endpush
