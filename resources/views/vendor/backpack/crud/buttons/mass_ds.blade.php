<button class="btn btn-sm btn-default" type="button" data-toggle="modal"  data-backdrop="static" data-keyboard="false" data-target="#importMassDS"><i class="la la-cloud-upload-alt"></i> Import</button>

@push('after_scripts')
<div id="importMassDS" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Import Mass Delivery Sheet</h5>
        </div>
        <div class="modal-body">
            <p>Silahkan menggunakan template di bawah ini untuk mengimport </p>
            <div class="" style="border: 1px solid #9e9e9e; padding: 0px 12px;">
                Template : 
                <button type="button" id="btn-for-form-template-mass-ds" class="btn btn-link" onclick="downloadTemplateMassDs(this)">template-delivery-sheet.xlsx</button>
            </div>
            <div class ="mt-3" style="border-top: 1px solid gray;">&nbsp</div>
            <p>Silahkan upload file untuk diimport melalui button dibawah ini </p>
            <form id="form-import-ds" action="{{url('admin/purchase-order-import-ds')}}" method="post">
                @csrf
                <p></p>
                <input id="field_file_ds" type="file" name="file_po" class="form-control py-1 rect-validation">

                <div class="mt-4 text-right">
                    <button id="btn-for-form-import-ds" type="button" class="btn btn-sm m-1 btn-outline-vp-primary" onclick="submitAfterValid('form-import-ds')">Import</a>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Close</button>
                </div>      
            </form>
        </div>
    </div>
  </div>
</div>

<script>
    $("#field_file_ds").click(function(){
        $("#field_file_ds").val('');
    });
    function downloadTemplateMassDs(){
        var urlTemplateMassDs = "{{url('admin/template-mass-ds')}}"
        submitAjaxValid('form-template-mass-ds', {action:urlTemplateMassDs, data: {}})
    }
    
</script>
@endpush