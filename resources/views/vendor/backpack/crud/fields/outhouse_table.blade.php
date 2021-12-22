@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @if(sizeof($field['table_body']) > 0)
    <table class="table table-stripped table-sm outhouse-table">
        <thead>
            <tr>
            <th style="white-space: nowrap;">#</th>
            <th style="white-space: nowrap;">Seq</th>
            <th style="white-space: nowrap;">Item</th>
            <th style="white-space: nowrap;">Desc</th>
            <th style="white-space: nowrap;">Lot</th>
            <th style="white-space: nowrap;">Lot Qty</th>
            <th style="white-space: nowrap;">Qty Req</th>
            <th style="white-space: nowrap;">Issued Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach($field['table_body'] as $key => $data)
            @php
            $issued_qty =  $field['current_qty']*$data->qty_per;
            $fixed_issued_qty = ($data->lot_qty > $issued_qty) ? $issued_qty : $data->lot_qty;
            $fixed_issued_qty = round($fixed_issued_qty, 2);
            if(isset($field['data_table'])){
                $fixed_issued_qty = collect($field['data_table']->attributes)->where('id', $data->id)->first()->qty;
            }
            @endphp
            <tr>
                <td class="py-3">{{$key+1}}</td>
                <td class="py-3">{{$data->seq}}</td>
                <td class="py-3">{{$data->matl_item}}</td>
                <td class="py-3">{{$data->description}}</td>
                <td class="py-3">{{$data->lot}}</td>
                <td class="py-3">{{$data->lot_qty}}</td>
                <td class="py-3"><span class="qty-requirement">{{$fixed_issued_qty}}</span></td>
                <td> 
                    <input type="hidden" name="material_ids[]" value="{{$data->id}}"> 
                    <input type="number" class="form-control form-issued" data-totalqtyper="{{$field['total_qty_per']}}" data-lotqty="{{$data->lot_qty}}" data-qtyper="{{$data->qty_per}}" name="{{$field['name']}}[]" value="{{$fixed_issued_qty}}"> 
                    <small class="text-danger error-form-issued" style="font-size: 11px;"><br></small>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else 
    <p class="text-danger form-control">Material Belum Tersedia</p>
    @endif
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
                var initCurrent = parseFloat( $( "#current-qty" ).val())
                outhouseTableManager(initCurrent)
            } );
        </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
