<button class="btn btn-sm btn-default" type="button" data-toggle="modal" data-target="#importMassDS"><i class="la la-cloud-upload-alt"></i> Import</button>

<?php $__env->startPush('after_scripts'); ?>
<div id="importMassDS" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Import Mass Delivery Sheet</h5>
        </div>
        <div class="modal-body">
            <p>Silahkan menggunakan template di bawah ini untuk mengimport <br><a href="<?php echo e(url('admin/template-mass-ds')); ?>">template-delivery-sheet.xlsx</a></p>
            <form id="form-import-ds" action="<?php echo e(url('admin/purchase-order-import-ds')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <input type="file" name="file_po" class="form-control py-1 rect-validation">

                <div class="mt-4 text-right">
                    <button id="btn-for-form-import-ds" type="button" class="btn btn-sm btn-outline-primary" onclick="submitAfterValid('form-import-ds')">Import</a>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Close</button>
                </div>      
            </form>
        </div>
    </div>
  </div>
</div>

<?php $__env->stopPush(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/crud/buttons/massds.blade.php ENDPATH**/ ?>