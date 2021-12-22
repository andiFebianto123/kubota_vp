<li class="nav-item dropdown pr-4">
  <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" style="position: relative;width: 35px;height: 35px;margin: 0 10px;">
    
    <span class="backpack-avatar-menu-container" style="position: absolute;left: 0;width: 100%;background-color: #ffffff;border-radius: 50%;color: #000000; font-weight:bold;line-height: 35px;">
      <?php echo e(backpack_user()->getAttribute('name') ? mb_substr(backpack_user()->name, 0, 1, 'UTF-8') : 'A'); ?>

    </span>
  </a>
  <div class="dropdown-menu <?php echo e(config('backpack.base.html_direction') == 'rtl' ? 'dropdown-menu-left' : 'dropdown-menu-right'); ?> mr-4 pb-1 pt-1">
    <a class="dropdown-item" href="<?php echo e(route('backpack.account.info')); ?>"><i class="la la-user"></i> <?php echo e(trans('backpack::base.my_account')); ?></a>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="<?php echo e(backpack_url('logout')); ?>"><i class="la la-lock"></i> <?php echo e(trans('backpack::base.logout')); ?></a>
  </div>
</li>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/base/inc/menu_user_dropdown.blade.php ENDPATH**/ ?>