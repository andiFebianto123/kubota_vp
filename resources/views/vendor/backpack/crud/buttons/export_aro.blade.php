@if ($crud->hasAccess('export_aro_excel'))
    <button id="btn-for-export-aro-excel" class="btn btn-sm btn-primary-vp" data-route="{{$crud->exportAroRoute}}" type="button" onclick="exportExcelAro(this)" ><i class="la la-file-excel"></i> Export A/R/O</button>
@endif

@push('after_scripts')
<script>
    function exportExcelAro(button){

        var route = $(button).attr('data-route');
        var expFilename = "{{(isset($crud->exportAroFilename)) ? $crud->exportFilename : date('YmdHis').'.xlsx'}}"

        submitAjaxValid('export-aro-excel', {action:route, data: {}})
    }
    
</script>
@endpush