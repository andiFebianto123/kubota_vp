@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    <table class="table table-stripped table-sm table-responsive">
        <thead>
            <tr>
                <th>
                    #
                </th>
                @foreach($field['table']['table_header'] as $key1 => $col_header)
                <th style="white-space: nowrap;">
                    {{$col_header}}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($field['table']['table_body'] as $key => $data)
            <tr>
                <td>
                    <input type="checkbox" name="{{$field['name']}}[]" value="{{$data['value']}}" class="cb-check">
                </td>
                @foreach($data['column'] as $key1 => $column)
                <td style="white-space: nowrap;">
                    {{substr($column, 0,60)}}
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp
    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script>
            $(document).ready( function () {
                $('#checklist-table').DataTable();
            } );
            // $('.cb-all').change(function () {
            //     $(".cb-check").prop('checked', $(this).prop('checked'))
            // })
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
