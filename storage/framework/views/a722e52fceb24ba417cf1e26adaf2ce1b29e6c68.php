<?php $constant = app('App\Helpers\Constant'); ?>


<?php
$defaultBreadcrumbs = [
trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
$crud->entity_name_plural => url($crud->route),
trans('backpack::crud.preview') => false,
];

// if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
$breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
?>

<?php $__env->startSection('header'); ?>
<section class="container-fluid d-print-none">
    <a href="javascript: window.print();" class="btn float-right"><i class="la la-print"></i></a>
    <h2>
        <span class="text-capitalize"><?php echo e($entry->number); ?></span>
        <small>Preview</small>
        <?php if($crud->hasAccess('list')): ?>
        <small class=""><a href="javascript:history.back()" class="font-sm"><i class="la la-angle-double-left"></i> <?php echo e(trans('backpack::crud.back_to_all')); ?> <span><?php echo e($crud->entity_name_plural); ?></span></a></small>
        <?php endif; ?>
    </h2>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="<?php echo e($crud->getShowContentClass()); ?>">
        <!-- Default box -->
        <div class="">
            <?php if($crud->model->translationEnabled()): ?>
            <div class="row">
                <div class="col-md-12 mb-2">
                    <!-- Change translation button group -->
                    <div class="btn-group float-right">
                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php echo e(trans('backpack::crud.language')); ?>: <?php echo e($crud->model->getAvailableLocales()[request()->input('locale')?request()->input('locale'):App::getLocale()]); ?> &nbsp; <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <?php $__currentLoopData = $crud->model->getAvailableLocales(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $locale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="dropdown-item" href="<?php echo e(url($crud->route.'/'.$entry->getKey().'/show')); ?>?locale=<?php echo e($key); ?>"><?php echo e($locale); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-header bg-secondary">
            <label class="font-weight-bold mb-0">Detail</label> 
        </div>
        <div class="card no-padding no-border">
            <table class="table">
                <tr>
                    <td>PO Number</td>
                    <td>: <?php echo e($entry->po_num); ?></td>
                </tr>
                <tr>
                    <td>Vendor</td>
                    <td>: <?php echo e($entry->vendor->vend_num); ?></td>
                </tr>
                <tr>
                    <td>PO Date</td>
                    <td>: <?php echo e(date('Y-m-d', strtotime($entry->po_date))); ?></td>
                </tr>
                <tr>
                    <td>Email Sent</td>
                    <td>: <?php echo e(($entry->email_flag) ? "✓":"-"); ?></td>
                </tr>
                <tr>
                    <td>Order Sheet</td>
                    <td>: 
                        <?php if($constant::checkPermission('Read PO Detail')): ?>
                            <a href="<?php echo e(url('admin/order-sheet-export-pdf/'.$entry->po_num)); ?>" class="btn btn-sm btn-danger" target="_blank"><i class="la la-file-pdf"></i> PDF</a>
                            <a class="btn btn-sm btn-primary-vp" target="_blank" href="<?php echo e(url('admin/order-sheet-export-excel/'.$entry->po_num)); ?>"><i class="la la-file-excel"></i> Excel</a>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div><!-- /.box-body -->
    </div><!-- /.box -->
    

    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary-vp">
               <label class="font-weight-bold mb-0">PO Line</label> 
            </div>
            <div class="card-body">
                <?php if(sizeof($po_lines) > 0): ?>
                <div>
                    <!-- <button class="btn btn-sm btn-default" type="button" data-toggle="modal" data-target="#importMassDS"><i class="la la-cloud-upload-alt"></i> Import (<span class="total-mass">0</span>)</button> -->
                </div>
                <table class="table table-striped mb-0 table-responsive">
                    <thead>
                        <tr>
                            <th>
                                <?php if(backpack_auth()->user()->hasRole('Admin PTKI')): ?>
                                    <input type="checkbox" id="check-all-cb" class="check-all">
                                <?php endif; ?>
                            </th>
                            <th>PO Number</th>
                            <th>Status</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>UM</th>
                            <th>Due Date</th>
                            <th>Tax (%)</th>
                            <th>Unit Price (<?php echo e($entry->vendor->currency); ?>)</th>
                            <th>Total Price (<?php echo e($entry->vendor->currency); ?>)</th>
                            <th>Status Accept</th>
                            <th>Read By</th>
                            <th>Read At</th>
                            <?php if(backpack_auth()->user()->hasRole('Admin PTKI')): ?>
                                <th>Created At</th>
                            <?php endif; ?>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $po_lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $po_line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <?php if($po_line->read_at == null && backpack_auth()->user()->hasRole('Admin PTKI')): ?>
                                <input type="checkbox" name="po_line_ids[]" value="<?php echo e($po_line->id); ?>" class="check-po-lines check-<?php echo e($po_line->id); ?>">
                                <!-- <input type="checkbox" class="check-read-po-lines check-read-<?php echo e($po_line->id); ?>"> -->
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap"><?php echo e($entry->po_num); ?>-<?php echo e($po_line->po_line); ?></td>
                            <td>
                                <span class="<?php echo e($arr_po_line_status[$po_line->status]['color']); ?>">
                                    <?php echo e($arr_po_line_status[$po_line->status]['text']); ?>

                                </span>
                            </td>
                            <td><?php echo e($po_line->item); ?></td>
                            <td><?php echo e($po_line->description); ?></td>
                            <td><?php echo $po_line->change_order_qty; ?></td>
                            <td><?php echo e($po_line->u_m); ?></td>
                            <td><?php echo $po_line->change_due_date; ?></td>
                            <td><?php echo e($po_line->tax); ?></td>
                            <td class="text-nowrap"><?php echo $po_line->change_unit_price; ?></td>
                            <td class="text-nowrap"><?php echo $po_line->change_total_price; ?></td>
                            <td><?php echo $po_line->reformat_flag_accept; ?></td>
                            <td><?php echo e($po_line->read_by_user); ?></td>
                            <td><?php echo e($po_line->read_at); ?></td>
                            <?php if(backpack_auth()->user()->hasRole('Admin PTKI')): ?>
                                <td><?php echo e($po_line->created_at); ?></td>
                            <?php endif; ?>
                            <td class="text-nowrap"><!-- Single edit button -->
                                <?php if($po_line->read_at): ?>
                                    <?php if($po_line->status == "O" && $po_line->accept_flag == 1): ?>
                                        <?php if($constant::checkPermission('Read PO Detail')): ?>
                                            <a href="<?php echo e(url('admin/purchase-order-line')); ?>/<?php echo e($po_line->id); ?>/show" class="btn btn-sm btn-link"><i class="la la-eye"></i> View</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if(backpack_auth()->user()->hasRole('Admin PTKI') && sizeof($po_line->delivery) == 0): ?>
                                        <?php if($po_line->count_ds == 0): ?>
                                            <?php if($constant::checkPermission('Unread PO Detail')): ?>
                                                <a href="<?php echo e(url('admin/purchase-order-line')); ?>/<?php echo e($po_line->id); ?>/unread" class="btn btn-sm btn-link"><i class="la la-book"></i> Unread</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>    
                                <?php else: ?>
                                    <?php if(backpack_auth()->user()->hasRole('Admin PTKI')): ?>
                                        <?php if($constant::checkPermission('Read PO Detail')): ?>
                                            <button class="btn btn-sm btn-link"  type="button" data-toggle="modal" onclick="acceptPoLines([<?php echo e($po_line->id); ?>])" data-target="#modalAccept"><i class="la la-check"></i> Accept</button>
                                            <button class="btn btn-sm btn-link"  type="button" data-toggle="modal"  onclick="rejectPoLines([<?php echo e($po_line->id); ?>])" data-target="#modalReject"><i class="la la-times"></i> Reject</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </tbody>
                </table>
                <div class="section-buttons"></div>

                <?php else: ?>
                <p class="text-center">
                    No Data Available
                </p>
                <?php endif; ?>
                
            </div>

        </div><!-- /.box-body -->
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary-vp">
               <label class="font-weight-bold mb-0">PO Change History</label> 
            </div>
            <div class="card-body">
                <?php if(sizeof($po_changes_lines) > 0): ?>
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>PO Line</th>
                            <th>Issued Date</th>
                            <th>Change</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $po_changes_lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $po_line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($po_line->po_num); ?></td>
                            <td><?php echo e($po_line->po_line); ?></td>
                            <td><?php echo e(date('Y-m-d', strtotime($po_line->po_change_date))); ?></td>
                            <td><?php echo e($po_line->po_change); ?></td>
                            <td>
                                <?php if($constant::checkPermission('Read PO Detail')): ?>
                                    <a href="<?php echo e(url('admin/purchase-order')); ?>/<?php echo e($po_line->po_num); ?>/<?php echo e($po_line->po_line); ?>/detail-change" class="btn btn-sm btn-link"><i class="la la-eye"></i> View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-center">
                    No Data Available
                </p>
                <?php endif; ?>
            </div>

        </div><!-- /.box-body -->
    </div>
</div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('after_styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/show.css').'?v='.config('backpack.base.cachebusting_string')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('after_scripts'); ?>

<!-- Modal -->

<div id="modalAccept" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Accept PO Line</h5>
        </div>
        <div class="modal-body">
            <p class="text-accept"></p>
            <form id="form-accept-po-line" action="<?php echo e(url('admin/purchase-order-accept-po-line')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="po_line_ids" class="val-accept">
                <input type="hidden" name="po_id" value="<?php echo e($entry->id); ?>">
                <div class="mt-4 text-right">
                    <button id="btn-for-form-accept-po-line" type="button" class="btn btn-sm btn-outline-primary" onclick="submitAfterValid('form-accept-po-line')">Submit</a>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Close</button>
                </div>      
            </form>
        </div>
    </div>
  </div>
</div>

<div id="modalReject" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Reject PO Line</h5>
        </div>
        <div class="modal-body">
            <p class="text-reject"></p>
            <form id="form-reject-po-line" action="<?php echo e(url('admin/purchase-order-reject-po-line')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <label for="">Write Reason</label>
                <textarea name="reason" class="form-control" id="" cols="30" rows="10"></textarea>
                <input type="hidden" name="po_line_ids" class="val-reject">
                <input type="hidden" name="po_id" value="<?php echo e($entry->id); ?>">
                <div class="mt-4 text-right">
                    <button id="btn-for-form-reject-po-line" type="button" class="btn btn-sm btn-outline-primary" onclick="submitAfterValid('form-reject-po-line')">Submit</a>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Close</button>
                </div>      
            </form>
        </div>
    </div>
  </div>
</div>
<script src="<?php echo e(asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
<script src="<?php echo e(asset('packages/backpack/crud/js/show.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
<script>
var anyChecked = false
var anyReadChecked = false
var totalPoLine = $('.check-po-lines').length
var totalPoLineRead = $('.check-read-po-lines').length
var totalChecked = 0
var totalCheckedRead = 0
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
$('#check-all-cb').change(function () {
    totalChecked = 0
    $(".check-po-lines").prop('checked', $(this).prop('checked'))
    anyChecked = $(this).prop('checked')
    if ($(this).prop('checked')) {
        totalChecked = totalPoLine
    }
    
    callButton(anyChecked)
})

$('.check-po-lines').change(function () {
    if ($(this).prop('checked')==true){
        $(this).prop('checked', true) 
        totalChecked ++
    }else{
        $(this).prop('checked', false)
        totalChecked --
    }
    
    if (totalChecked > 0) {
        callButton(true) 
        if (totalChecked == totalPoLine) {
            $('#check-all-cb').prop('checked', true)
        }
    }else{
        $('#check-all-cb').prop('checked', false)
        callButton(false) 
    }
})

$('#check-all-cb-read').change(function () {
    totalCheckedRead = 0
    $(".check-read-po-lines").prop('checked', $(this).prop('checked'))
    anyReadChecked = $(this).prop('checked')
    if ($(this).prop('checked')) {
        totalCheckedRead = totalPoLineRead
    }

    $(".total-mass").text(totalCheckedRead)
})

$('.check-read-po-lines').change(function () {
    if ($(this).prop('checked')==true){
        $(this).prop('checked', true) 
        totalCheckedRead ++
    }else{
        $(this).prop('checked', false)
        totalCheckedRead --
    }
    
    if (totalCheckedRead > 0) {
        if (totalCheckedRead == totalPoLineRead) {
            $('#check-all-cb-read').prop('checked', true)
        }
    }else{
        $('#check-all-cb-read').prop('checked', false)
    }
    $(".total-mass").text(totalCheckedRead)

})

function changeBtn(v){
    var htmlButton = ""
    var arrPoLines = []
    $( ".check-po-lines" ).each(function() {
        if($(this).prop('checked')==true){
            arrPoLines.push($(this).val())
        }
    })
    if (v == 1){
        htmlButton += "<button class='btn btn-sm btn-primary-vp' data-toggle='modal' onclick='acceptPoLines(["+arrPoLines+"])' data-target='#modalAccept'><i class='la la-check-circle'></i> Submit</button>"
    }else{
        htmlButton += "<button class='btn btn-sm btn-primary-vp' data-toggle='modal' onclick='rejectPoLines(["+arrPoLines+"])' data-target='#modalReject'><i class='la la-check-circle'></i> Submit</button>"
    }
    $(".button-area").html(htmlButton)
}

function callButton(anyChecked){
    var arrPoLines = []
    $( ".check-po-lines" ).each(function() {
        if($(this).prop('checked')==true){
            arrPoLines.push($(this).val())
        }
    })
    var htmlBtnAccOrder = "<input type='radio' name='flag_accept' class='radio-flag-accept' onclick='changeBtn(1)' value='1' checked> Accept "
    htmlBtnAccOrder += "<input type='radio' name='flag_accept' class='radio-flag-accept'  onclick='changeBtn(2)' value='2'> Reject <br>"
    htmlBtnAccOrder += "<div class='button-area'>"
    htmlBtnAccOrder += "<button class='btn btn-sm btn-primary-vp' data-toggle='modal' onclick='acceptPoLines(["+arrPoLines+"])' data-target='#modalAccept'><i class='la la-check-circle'></i> Submit</button>"
    htmlBtnAccOrder += "</div>"
    if (anyChecked) {
        $(".section-buttons").html(htmlBtnAccOrder)
    }else{
        $(".section-buttons").html("")
    }
 }


 function acceptPoLines(arrPoLines){
     var strPoLines = JSON.stringify(arrPoLines)
     var lengthPoLines = arrPoLines.length
     $('.val-accept').val(strPoLines)
     $('.text-accept').text('Accept '+lengthPoLines+' Po Line?')
 }

 function rejectPoLines(arrPoLines){
     var strPoLines = JSON.stringify(arrPoLines)
     var lengthPoLines = arrPoLines.length
     $('.val-reject').val(strPoLines)
     $('.text-reject').text('Reject '+lengthPoLines+' Po Line?')
 }

 function createDs(num, url){
    $('.text-count-ds').text("Anda Sudah Memiliki "+num+" DS. Apakah yakin akan melanjutkan menambah DS?")
    $('.goto-create').attr('href', url)
 }
 
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make(backpack_view('blank'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/crud/purchase-order-show.blade.php ENDPATH**/ ?>