@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    <div class="">
        <div class="input-group group-datapicker date mb-2">
            <input
                type="text"
                class="daterange-table form-control"
                {{-- value="{{date('d/m/Y',strtotime('first day of this month'))}} - {{date('d/m/Y')}} " --}}
                value=""
                placeholder="Choose Date Range"
                >
                <div class="input-group-append">
                    <span class="input-group-text">
                    <span class="la la-calendar"></span>
                </span>
            </div>
        </div>
        <table class="table table-stripped table-responsive checklist-table">
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
    </div>
    
@include('crud::fields.inc.wrapper_end')

@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp
    @push('crud_fields_styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">
    @endpush
    @push('crud_fields_scripts')
    <script type="text/javascript" src="{{ asset('packages/moment/min/moment-with-locales.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('packages/datatables.net/js/jquery.dataTables.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('packages/datatables.net-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('packages/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('packages/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('packages/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js')}}"></script>
    <script type="text/javascript" src="{{ asset('packages/datatables.net-fixedheader-bs4/js/fixedHeader.bootstrap4.min.js')}}"></script>
        <script>
            $(document).ready( function () {
                var filterDate = false
                $('.checklist-table').DataTable();
                // $('.daterange-table').daterangepicker();
                $('#DataTables_Table_0_length').html("")
                $(".group-datapicker").detach().appendTo('#DataTables_Table_0_length')
                if ($(window).width() > 800) {
                    $(".group-datapicker").css('width', '260px')
                }
                
            
                $(function() {
                    $('.daterange-table').daterangepicker({
                        opens: 'left',
                        autoUpdateInput: false,
                        locale: {
                            cancelLabel: 'Clear'
                        }
                    }, function(start, end, label) {
                        filterDate = true
                        var valueDt = start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY')
                        $('.checklist-table').DataTable().draw()
                        $('.daterange-table').val(valueDt)

                        // minDate = start;
                        // maxDate = end;
                        // console.log(minDate);
                        console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                    });

                    $('.daterange-table').on('cancel.daterangepicker', function(ev, picker) {
                        $(this).val('');
                        filterDate = false
                        $('.checklist-table').DataTable().draw()
                    });
                });

                $.fn.dataTable.ext.search.push(
                    function( settings, data, dataIndex ) {
                        var min = $('.daterange-table').data('daterangepicker').startDate._d;
                        var max = $('.daterange-table').data('daterangepicker').endDate._d;
                        var date = new Date(data[6] );

                        if (filterDate) {
                            if (
                            ( min === null && max === null ) ||
                            ( min === null && date <= max ) ||
                            ( min <= date   && max === null ) ||
                            ( min <= date   && date <= max )
                            ) {
                                return true;
                            }
                        }else{
                            return true;
                        }

                        
                        return false;
                    }
                );
            });

        </script>
    @endpush
@endif
