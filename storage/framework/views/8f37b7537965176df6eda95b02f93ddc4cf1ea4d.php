<?php $constant = app('App\Helpers\Constant'); ?>
<table>
    <thead>
        <?php if($type == 'week'): ?>
            <tr>
                <th rowspan="2" style="border:1px solid black; width: 30px;">Nama Item</th>
            <?php $__currentLoopData = $crud['columnHeader']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th colspan="4" style="text-align:center; border:1px solid black;">
                    <?php echo $header; ?>

                </th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
            </tr>
        <?php endif; ?>
        <?php if($type == 'days'): ?>
            <tr>
                <th rowspan="2" style="border:1px solid black; width: 30px;">Nama Item</th>
            <?php $__currentLoopData = $crud['columnHeader']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $key = $header['key'].'-01';
                    $newDate = new DateTime($key);
                    $key = $newDate->format('F Y');
                    $colspan = count($header['data']);
                ?>
                <th colspan="<?php echo e($colspan); ?>" class="" style="border:1px solid black; text-align: center;">
                    <strong><?php echo $key; ?></strong>
                </th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
            </tr>
            <?php endif; ?>
            <tr>
            
            <?php $__currentLoopData = $crud['columns']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(($column['label'] != 'Nama Item') && ($type == 'week' || $type == 'days')): ?>
                <?php
                    $style = "";
                    if($type == 'week'){
                        if($column['type'] == 'forecast'){
                            $style = "
                                border: 1px solid black; 
                                width: 25px;
                                text-align: center;
                            ";
                        }
                    }else if($type == 'days'){
                        $getKey = explode('-', $column['label']);
                        if($column['label'] != "Nama Item"){
                            $search = $constant::getColumnHeaderDays($crud['columnHeader'], "{$getKey[0]}-{$getKey[1]}", $column['label']);
                            if($search['search'] == 0){
                                $style = "
                                    border-left: 1px solid #ddd;
                                    text-align: center;
                                ";
                            }
                            $column['label'] = "{$getKey[2]}";
                        }
                    }
                ?>
                <?php endif; ?>
                <?php
                    if($type == 'month'){
                        $style = "
                            border: 1px solid black; 
                            width: 15px;
                            text-align: center;
                        ";
                        if($column['label'] == 'Nama Item'){
                            $style = "
                                border: 1px solid black; 
                                width: 30px;
                                text-align: center;
                            ";
                        }
                    }
                if(($type == 'week' || $type == 'days') && $column['label'] == 'Nama Item'){
                    continue;
                }
                ?>
                <th
                    style="<?php echo e($style ?? ''); ?>"
                >
                    <?php if(isset($column['link'])): ?>
                    <a href="<?php echo e(url('admin/forecast')); ?><?php echo e($column['link']); ?><?php echo e($column['label']); ?>"><?php echo e($column['label']); ?></a>
                    <?php else: ?>
                    <strong><?php echo $column['label']; ?></strong>
                    <?php endif; ?>
                </th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $result; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $forecast): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
                $style = "
                    border: 1px solid black; 
                ";
        ?>
            <tr>
                <?php $__currentLoopData = $forecast; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td style="<?php echo e($style); ?>"><?php echo $value; ?></td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/exports/excel/forecast.blade.php ENDPATH**/ ?>