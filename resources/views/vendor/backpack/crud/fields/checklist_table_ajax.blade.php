@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    <div class="">
        <div class="input-group group-datapicker date mb-2">
            <input
                type="text"
                class="daterange-table form-control"
                value=""
                placeholder="Choose Date Range"
                >
                <div class="input-group-append">
                    <span class="input-group-text">
                    <span class="la la-calendar"></span>
                </span>
            </div>
        </div>
        <table id="{{$field['name']}}" class="table table-stripped table-responsive checklist-table">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" name="select_all" value="1" id="{{$field['name']}}-select-all">
                    </th>
                    @foreach($field['table']['table_header'] as $key1 => $col_header)
                    <th class="text-nowrap">
                        {{$col_header}}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
               
            </tbody>
        </table>
        <div class="section-hidden"></div>
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
                var rowsSelected = []
                var clName = "{{$field['name']}}"

                var table = $("#"+clName).DataTable( {
                    processing: true,
                    serverSide: true,
                    ordering: false,
                    scrollX: true,
                    ajax: {
                        url: "{{$field['ajax_url']}}",
                        dataSrc: 'data',
                        data: function(data){
                            if (filterDate) {
                                var min = $('.daterange-table').data('daterangepicker').startDate.format('YYYY-MM-DD');
                                var max = $('.daterange-table').data('daterangepicker').endDate.format('YYYY-MM-DD');
                                // Append to data
                                data.from_date = min;
                                data.end_date = max;
                            }
                            
                        }
                    },
                    "order":[[1,'asc']],
                    'columnDefs': [{
                        'targets': 0,
                        'searchable': false,
                        'orderable': false,
                        'className': 'dt-body-center',
                        'checkboxes': true,
                        'render': function (data, type, full, meta){
                            return '<input type="checkbox" name="id[]" value="' + $('<div/>').text(data).html() + '">';
                        }
                        },{
                            'targets': 1,
                            'className': 'text-nowrap',
                        }
                    ],
                    'rowCallback': function(row, data, dataIndex){
                        var rowId = data[0];
                        if($.inArray(rowId, rowsSelected) !== -1){
                            $(row).find('input[type="checkbox"]').prop('checked', true);
                            $(row).addClass('selected');
                        }
                    }
                } );

                $('thead input[name="select_all"]', table.table().container()).on('click', function(e){
                    if(this.checked){
                        $("#"+clName+" tbody input[type='checkbox']:not(:checked)").trigger('click');
                    } else {
                        $("#"+clName+" tbody input[type='checkbox']:checked").trigger('click');
                    }

                    e.stopPropagation();
                });


                $("#"+clName+" tbody").on('click', 'input[type="checkbox"]', function(e){
                    var $row = $(this).closest('tr');
                    var data = table.row($row).data();
                    var rowId = data[0];
                    var index = $.inArray(rowId, rowsSelected);
                    var hiddenHtml = ""

                    if(this.checked && index === -1){
                        rowsSelected.push(rowId);
                    } else if (!this.checked && index !== -1){
                        rowsSelected.splice(index, 1);
                    }

                    $.each(rowsSelected, function( index, value ) {
                        hiddenHtml += "<input type='hidden' name='"+clName+"[]' value='"+value+"'>"
                    });

                    $(".section-hidden").html(hiddenHtml)

                    if(this.checked){
                        $row.addClass('selected');
                    } else {
                        $row.removeClass('selected');
                    }
                    e.stopPropagation();
                });

                $('.daterange-table').daterangepicker();
                $("#"+clName+"_length").html("")
                $(".group-datapicker").detach().appendTo("#"+clName+"_length")
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
                        table.draw()
                        $('.daterange-table').val(valueDt)
                    });

                    $('.daterange-table').on('cancel.daterangepicker', function(ev, picker) {
                        $(this).val('');
                        filterDate = false
                        table.draw()
                    });
                });


            });

        </script>
    @endpush
@endif
