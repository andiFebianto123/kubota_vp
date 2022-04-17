@if ($crud->hasAccess('advanced_export_excel'))
    <button id="btn-for-form-adv-export-excel" class="btn btn-sm btn-primary-vp" data-route="{{$crud->exportRoute}}" type="button" onclick="exportExcel(this)" ><i class="la la-file-excel"></i> Export</button>
@endif

@push('after_scripts')
<script>
    function exportExcel(button){
        var route = $(button).attr('data-route');
        var expFilename = "{{(isset($crud->exportFilename)) ? $crud->exportFilename : date('YmdHis').'.xlsx'}}"

        ajaxDownloadFile('form-adv-export-excel', {action:route, filename:expFilename, data: {}})
    }
    
</script>
@endpush