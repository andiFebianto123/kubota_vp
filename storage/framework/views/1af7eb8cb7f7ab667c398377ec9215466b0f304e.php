<?php echo $__env->make('crud::fields.inc.wrapper_start', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <label><?php echo $field['label']; ?></label>
    <table class="table table-stripped table-sm table-responsive">
        <thead>
            <tr>
                <th>
                    #
                </th>
                <?php $__currentLoopData = $field['table']['table_header']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key1 => $col_header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th style="white-space: nowrap;">
                    <?php echo e($col_header); ?>

                </th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $field['table']['table_body']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <input type="checkbox" name="<?php echo e($field['name']); ?>[]" value="<?php echo e($data['value']); ?>" class="cb-check">
                </td>
                <?php $__currentLoopData = $data['column']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key1 => $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td style="white-space: nowrap;">
                    <?php echo e(substr($column, 0,60)); ?>

                </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php echo $__env->make('crud::fields.inc.wrapper_end', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>





<?php if($crud->fieldTypeNotLoaded($field)): ?>
    <?php
        $crud->markFieldTypeAsLoaded($field);
    ?>
    
    <?php $__env->startPush('crud_fields_scripts'); ?>
        <script>
            $(document).ready( function () {
                $('#checklist-table').DataTable();
            } );
            // $('.cb-all').change(function () {
            //     $(".cb-check").prop('checked', $(this).prop('checked'))
            // })
        </script>
    <?php $__env->stopPush(); ?>

<?php endif; ?>


<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/crud/fields/checklist_table.blade.php ENDPATH**/ ?>