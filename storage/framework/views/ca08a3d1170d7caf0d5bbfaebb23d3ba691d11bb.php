<?php echo $__env->make('crud::fields.inc.wrapper_start', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <label><?php echo $field['label']; ?></label>
    <?php if(sizeof($field['table_body']) > 0): ?>
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
            <?php $__currentLoopData = $field['table_body']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
            $issued_qty =  $field['current_qty']*$data->qty_per;
            $fixed_issued_qty = ($data->lot_qty > $issued_qty) ? $issued_qty : $data->lot_qty;
            $fixed_issued_qty = round($fixed_issued_qty, 2);
            if(isset($field['data_table'])){
                $fixed_issued_qty = collect($field['data_table']->attributes)->where('id', $data->id)->first()->qty;
            }
            ?>
            <tr>
                <td class="py-3"><?php echo e($key+1); ?></td>
                <td class="py-3"><?php echo e($data->seq); ?></td>
                <td class="py-3"><?php echo e($data->matl_item); ?></td>
                <td class="py-3"><?php echo e($data->description); ?></td>
                <td class="py-3"><?php echo e($data->lot); ?></td>
                <td class="py-3"><?php echo e($data->lot_qty); ?></td>
                <td class="py-3"><span class="qty-requirement"><?php echo e($fixed_issued_qty); ?></span></td>
                <td> 
                    <input type="hidden" name="material_ids[]" value="<?php echo e($data->id); ?>"> 
                    <input type="number" class="form-control form-issued" data-totalqtyper="<?php echo e($field['total_qty_per']); ?>" data-lotqty="<?php echo e($data->lot_qty); ?>" data-qtyper="<?php echo e($data->qty_per); ?>" name="<?php echo e($field['name']); ?>[]" value="<?php echo e($fixed_issued_qty); ?>"> 
                    <small class="text-danger error-form-issued" style="font-size: 11px;"><br></small>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php else: ?> 
    <p class="text-danger form-control">Material Belum Tersedia</p>
    <?php endif; ?>
<?php echo $__env->make('crud::fields.inc.wrapper_end', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>





<?php if($crud->fieldTypeNotLoaded($field)): ?>
    <?php
        $crud->markFieldTypeAsLoaded($field);
    ?>
    
    <?php $__env->startPush('crud_fields_scripts'); ?>
        <script>
            $(document).ready( function () {
                $('#checklist-table').DataTable();
                var initCurrent = parseFloat( $( "#current-qty" ).val())
                outhouseTableManager(initCurrent)
            } );
        </script>
    <?php $__env->stopPush(); ?>

<?php endif; ?>


<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/crud/fields/outhouse_table.blade.php ENDPATH**/ ?>