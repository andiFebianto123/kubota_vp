@php
$field['value'] = old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' ));
// make sure the value is a JSON string (not array, if it's cast in the model)
$field['value'] = is_array($field['value']) ? json_encode($field['value']) : $field['value'];

$field['init_rows'] = $field['init_rows'] ?? $field['min_rows'] ?? 1;
$field['max_rows'] = $field['max_rows'] ?? 0;
$field['min_rows'] = $field['min_rows'] ?? 0;
@endphp

@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')
<div class="row container-mass-ds">
</div>
<button class="btn btn-sm btn-link pull-right pt-4 px-0" type="button" data-toggle="modal" data-target="#modalUploadSerialNumber"> <i class="la la-file-excel"></i> Upload Excel</button>
@include('crud::fields.inc.wrapper_end')

@push('after_scripts')
<div id="modalUploadSerialNumber" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Serial Numbers</h5>
            </div>
            <div class="modal-body">
                <p>Silahkan menggunakan template di bawah ini untuk mengimport <br><a href="{{url('admin/template-serial-numbers')}}" init-url="{{url('admin/template-serial-numbers')}}" id="template-upload-sn">template-serial-number.xlsx</a></p>
                <form id="form-import-sn" action="{{url('admin/serial-number-import')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file_sn" class="form-control py-1 rect-validation">
                    <input type="hidden" name="allowed_qty" class="form-control py-1 rect-validation" id="allowed-qty">

                    <div class="mt-4 text-right">
                        <button id="btn-for-form-import-sn" type="button" class="btn btn-sm btn-outline-vp-primary" onclick="submitAfterValidMass('form-import-sn')">Import</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endpush

@push('crud_fields_scripts')
<script>
    function submitAfterValidMass(formId, massError = false) {
        var initText = $('#btn-for-' + formId).html()
        var baseUrl = $("meta[name=base_url]").attr("content");

        var imgLoading = "<img src='" + baseUrl + "/img/loading-buffering.gif' width='20px'>"
        $('#btn-for-' + formId).html(imgLoading + ' Processing...')
        $('#btn-for-' + formId).attr('disabled', 'disabled')

        var datastring = $("#" + formId).serialize()
        var formData = new FormData($("#" + formId)[0]);

        var url = $("#" + formId).attr('action')

        $('.rect-validation').css({
            "border": "1px solid #428fc7"
        })
        $('.error-message').remove()
        $(".progress-loading").remove()
        blinkElement('.btn')
        setInterval(blinkElement, 1000);

        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#btn-for-' + formId).removeAttr('disabled')
                $(".progress-loading").remove()
                $('#btn-for-' + formId).html(initText)
                if (response.status) {
                    messageStatusGeneral("#" + formId, response.message, 'success')
                    var lengthData = response.datas.length
                    // var container = $("[data-repeatable-identifier='serial_numbers']")
                    // var field_group_clone = container.clone()
                    var html = ""
                    $.each(response.datas, function(key, row) {
                        var seq = key+1
                        html += "<div class='col-md-3'>"
                        html += "<label class='solid-red-circle'>"+seq+"</label>"
                        html += "<input class='form-control' name='{{$field['name']}}' value='"+row.serial_number+"'>"
                        html += "</div>"
                    })
                    $('#modalUploadSerialNumber').modal('hide');
                    $('.container-mass-ds').html(html);
                } else {
                    messageStatusGeneral("#" + formId, response.message)
                }
            },
            error: function(xhr, status, error) {
                $('#btn-for-' + formId).html(initText)
                $('#btn-for-' + formId).removeAttr('disabled')
                $(".progress-loading").remove()
                var messageErr = "Something Went Wrong"
                if (xhr.responseJSON) {
                    messageErr = xhr.responseJSON.message
                }
                messageStatusGeneral("#" + formId, messageErr)
            }
        });
    }
</script>
@endpush
