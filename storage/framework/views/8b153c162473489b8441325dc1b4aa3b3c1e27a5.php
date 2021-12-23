<!-- This file is used to store sidebar items, starting with Backpack\Base 0.9.0 -->
<?php $__currentLoopData = (new App\Helpers\Sidebar())->generate(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(in_array(backpack_auth()->user()->roles->pluck('name')->first(), $menu['roles'])): ?>
    <li class="nav-item"><a class="nav-link" href="<?php echo e($menu['url']); ?>"><i class="la <?php echo e($menu['icon']); ?> nav-icon"></i> <?php echo e($menu['name']); ?></a></li>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<!-- <li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('role')); ?>'><i class='nav-icon la la-question'></i> Roles</a></li>
<li class='nav-item'><a class='nav-link' href='<?php echo e(backpack_url('permission')); ?>'><i class='nav-icon la la-question'></i> Permissions</a></li> --><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/base/inc/sidebar_content.blade.php ENDPATH**/ ?>