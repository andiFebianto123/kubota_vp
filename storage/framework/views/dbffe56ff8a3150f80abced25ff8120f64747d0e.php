
<?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <!-- load the view from type and view_namespace attribute if set -->
    <?php
        $fieldsViewNamespace = $field['view_namespace'] ?? 'crud::fields';
    ?>

    <?php echo $__env->make($fieldsViewNamespace.'.'.$field['type'], ['field' => $field], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/inc/show_fields.blade.php ENDPATH**/ ?>