<!-- password -->

@php
    // autocomplete off, if not otherwise specified
    if (!isset($field['attributes']['autocomplete'])) {
        $field['attributes']['autocomplete'] = "off";
    }
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <div class="input-group">
        <input
    	type="password"
    	name="{{ $field['name'] }}"
        @include('crud::fields.inc.attributes')
    	>
        <div class="input-group-append">
            <span class="input-group-text show-password" style="cursor: pointer">
                <i class="la la-eye" aria-hidden="true"></i>
            </span>
        </div>
    </div>
    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')


@if ($crud->fieldTypeNotLoaded($field)) 
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp
    <script>
        $("span.show-password").mousedown(function(){
            $(this).parent().prev().attr('type','text');
        }).mouseup(function(){
            $(this).parent().prev().attr('type','password');
        }).mouseout(function(){
            $(this).parent().prev().attr('type','password');
        });

        $("span.show-password").each(function(i, el){
            onLongPress(el, function(){
                $(el).parent().prev().attr('type','text');
            }, function(){
                $(el).parent().prev().attr('type','password');
            })
        });

        function onLongPress(element, callback, cancel) {
            element.addEventListener('touchstart', () => { 
                callback();
            });

            element.addEventListener('touchend', cancel);
            element.addEventListener('touchmove', function(e){
                var selectedElement = document.elementFromPoint(e.touches[0].clientX, e.touches[0].clientY);
                if (!selectedElement.classList.contains('show-password') && !selectedElement.classList.contains('input-group-append') && !selectedElement.classList.contains('la-eye')){
                    cancel();
                }
            });
        }
    </script>
@endif