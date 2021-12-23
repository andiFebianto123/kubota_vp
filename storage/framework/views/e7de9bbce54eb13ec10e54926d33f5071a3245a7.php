

<?php if($crud->hasAccess('create')): ?>
	<?php if(isset($crud->button_create)): ?>
	<a href="<?php echo e(url($crud->route.'/create')); ?>" class="btn btn-primary btn-primary-vp" data-style="zoom-in"><span class="ladda-label"><i class="la la-plus"></i> <?php echo e($crud->button_create); ?></span></a>
	<?php else: ?>
	<a href="<?php echo e(url($crud->route.'/create')); ?>" class="btn btn-primary btn-primary-vp" data-style="zoom-in"><span class="ladda-label"><i class="la la-plus"></i> Create</span></a>
	<?php endif; ?>
<?php endif; ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/crud/buttons/create.blade.php ENDPATH**/ ?>