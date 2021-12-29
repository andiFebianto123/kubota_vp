<nav class="navbar navbar-expand-lg navbar-filters mb-0 pb-0 pt-0">
      <!-- Brand and toggle get grouped for better mobile display -->
      <a class="nav-item d-none d-lg-block"><span class="la la-filter"></span></a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#bp-filters-navbar" aria-controls="bp-filters-navbar" aria-expanded="false" aria-label="<?php echo e(trans('backpack::crud.toggle_filters')); ?>">
        <span class="la la-filter"></span> <?php echo e(trans('backpack::crud.filters')); ?>

      </button>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="bp-filters-navbar">
        <ul class="nav navbar-nav">
          <!-- THE ACTUAL FILTERS -->
          <?php $__currentLoopData = $crud->filters(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(isset($filter->options['custom_table']) && $filter->options['custom_table'] == true): ?>
              <?php echo $__env->make($filter->getViewWithNamespace(), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>
    			<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
      </div><!-- /.navbar-collapse -->
  </nav>

<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/inc/filters_navbar_custom.blade.php ENDPATH**/ ?>