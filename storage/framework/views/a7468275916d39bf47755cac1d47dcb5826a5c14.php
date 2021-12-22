<header class="<?php echo e(config('backpack.base.header_class')); ?>">
  <!-- Logo -->
  <button class="navbar-toggler sidebar-toggler d-lg-none mr-auto ml-3" type="button" data-toggle="sidebar-show" aria-label="<?php echo e(trans('backpack::base.toggle_navigation')); ?>">
    <!-- <span class="navbar-toggler-icon"></span> -->
    <span class="la la-icons icon-toggle-menu"></span>
  </button>
  <a class="navbar-brand text-white" href="<?php echo e(url(config('backpack.base.home_link'))); ?>" title="<?php echo e(config('backpack.base.project_name')); ?>">
    <?php echo config('backpack.base.project_logo'); ?>

  </a>
  <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button" data-toggle="sidebar-lg-show" aria-label="<?php echo e(trans('backpack::base.toggle_navigation')); ?>">
    <!-- <span class="navbar-toggler-icon"></span> -->
    <span class="la la-icons icon-toggle-menu"></span>
  </button>

  <?php echo $__env->make(backpack_view('inc.menu'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</header>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/base/inc/main_header.blade.php ENDPATH**/ ?>