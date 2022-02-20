<div class="modal fade" id="modal-error-bulk" role="dialog" style="z-index:1051;" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title">Import Error Message</h4>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="overflow-hidden p-2">
                    <table id="crudTableBulkMessage"
                        class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs" cellspacing="0"
                        cellspacing="0">
                        <thead>
                            <tr>
                            <th data-orderable="true" id="table-bulk-head-id">Row</th>
                            <th data-orderable="true">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    if(crud == null){
        var crud = {
      functionsToRunOnDataTablesDrawEvent: [],
      addFunctionToDataTablesDrawEventQueue: function (functionName) {
          if (this.functionsToRunOnDataTablesDrawEvent.indexOf(functionName) == -1) {
          this.functionsToRunOnDataTablesDrawEvent.push(functionName);
        }
      },
      responsiveToggle: function(dt) {
          $(dt.table().header()).find('th').toggleClass('all');
          dt.responsive.rebuild();
          dt.responsive.recalc();
      },
      executeFunctionByName: function(str, args) {
        var arr = str.split('.');
        var fn = window[ arr[0] ];

        for (var i = 1; i < arr.length; i++)
        { fn = fn[ arr[i] ]; }
        fn.apply(window, args);
      },
      dataTableConfiguration: {
        responsive: false,
        scrollX: true,
        autoWidth: false,
        pageLength: {{ config('backpack.crud.operations.list.defaultPageLength') }},
        lengthMenu: [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
        /* Disable initial sort */
        aaSorting: [],
        language: {
              "emptyTable":     "{{ trans('backpack::crud.emptyTable') }}",
              "info":           "{{ trans('backpack::crud.info') }}",
              "infoEmpty":      "{{ trans('backpack::crud.infoEmpty') }}",
              "infoFiltered":   "{{ trans('backpack::crud.infoFiltered') }}",
              "infoPostFix":    "{{ trans('backpack::crud.infoPostFix') }}",
              "thousands":      "{{ trans('backpack::crud.thousands') }}",
              "lengthMenu":     "{{ trans('backpack::crud.lengthMenu') }}",
              "loadingRecords": "{{ trans('backpack::crud.loadingRecords') }}",
              "processing":     "<img src='{{ asset('packages/backpack/crud/img/ajax-loader.gif') }}' alt='{{ trans('backpack::crud.processing') }}'>",
              "search":         "<span class='d-none d-sm-inline'>{{ trans('backpack::crud.search') }}</span>",
              "zeroRecords":    "{{ trans('backpack::crud.zeroRecords') }}",
              "paginate": {
                  "first":      "{{ trans('backpack::crud.paginate.first') }}",
                  "last":       "{{ trans('backpack::crud.paginate.last') }}",
                  "next":       ">",
                  "previous":   "<"
              },
              "aria": {
                  "sortAscending":  "{{ trans('backpack::crud.aria.sortAscending') }}",
                  "sortDescending": "{{ trans('backpack::crud.aria.sortDescending') }}"
              },
              "buttons": {
                  "copy":   "{{ trans('backpack::crud.export.copy') }}",
                  "excel":  "{{ trans('backpack::crud.export.excel') }}",
                  "csv":    "{{ trans('backpack::crud.export.csv') }}",
                  "pdf":    "{{ trans('backpack::crud.export.pdf') }}",
                  "print":  "{{ trans('backpack::crud.export.print') }}",
                  "colvis": "{{ trans('backpack::crud.export.column_visibility') }}"
              },
          },
          processing: true,
          serverSide: true,
          dom:
            "<'row hidden'<'col-sm-6 hidden-xs'i><'col-sm-6 hidden-print'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-2 '<'col-sm-6 col-md-4'l><'col-sm-2 col-md-4 text-center'B><'col-sm-6 col-md-4 hidden-print'p>>",
      }
        }
    }
    var crudBulkMessages = jQuery.extend(true, {}, crud);
    crudBulkMessages.dataTableConfiguration.responsive = false;
    crudBulkMessages.dataTableConfiguration.scrollX = true;
    delete crudBulkMessages.dataTableConfiguration.serverSide;
    delete crudBulkMessages.dataTableConfiguration.processing;
    delete crudBulkMessages.dataTableConfiguration.ajax;
    crudBulkMessages.dataTableConfiguration.columns = [
        { "data": "row" },
        { "data": "message" },
    ];
    $('#modal-error-bulk').on('shown.bs.modal', function (e) {
        if(crudBulkMessages.table != null){
            crudBulkMessages.table.columns.adjust();
        }
    });
    var resizeTimerBulkMessage;
    function resizeCrudTableBulkMessageColumnWidths() {
        clearTimeout(resizeTimerBulkMessage);
        resizeTimerBulkMessage = setTimeout(function() {
        // Run code here, resizing has "stopped"
        if(crudBulkMessages.table != null){
        crudBulkMessages.table.columns.adjust();
        }
        }, 250);
    }
    $(window).on('resize', function(e) {
        resizeCrudTableBulkMessageColumnWidths();
    });
    $(document).on('expanded.pushMenu', function(e) {
        resizeCrudTableBulkMessageColumnWidths();
    });
    $(document).on('collapsed.pushMenu', function(e) {
        resizeCrudTableBulkMessageColumnWidths();
    });
</script>
