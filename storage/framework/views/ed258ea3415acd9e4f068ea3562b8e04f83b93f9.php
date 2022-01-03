<?php $__currentLoopData = (new App\Helpers\Sidebar())->generate(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($menu['access']): ?>
    <li class="nav-item <?php if($menu['childrens']): ?> nav-dropdown <?php endif; ?>">
        <a class="nav-link parent <?php if($menu['childrens']): ?> nav-dropdown-toggle <?php endif; ?>" href="<?php echo e($menu['url']); ?>">
            <i class="nav-icon la <?php echo e($menu['icon']); ?>"></i> <?php echo e($menu['name']); ?>

        </a>
        <?php if($menu['childrens']): ?>
        <ul class="nav-dropdown-items">
            <?php $__currentLoopData = $menu['childrens']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key2 => $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="nav-item">
                <a class="nav-link childs" href="<?php echo e($child['url']); ?>">
                <span>• <?php echo e($child['name']); ?></span>
                </a>
            </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <?php endif; ?>
    </li>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/base/inc/sidebar_content.blade.php ENDPATH**/ ?>