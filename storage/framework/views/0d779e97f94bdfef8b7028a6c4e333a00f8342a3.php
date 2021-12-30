
<?php
	$value = data_get($entry, $column['name']);
    $value = is_array($value) ? json_encode($value) : $value;

    $column['escaped'] = $column['escaped'] ?? true;
    $column['limit'] = $column['limit'] ?? 40;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['text'] = $column['prefix'].Str::limit($value, $column['limit'], '[...]').$column['suffix'];
?>

<div>
    <?php echo $__env->renderWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path'])); ?>
        <?php if($column['escaped']): ?>
            <?php if($entry->status == 1): ?>
                <?php if($entry->user == backpack_user()->id): ?>
                    <a href="javascript:void(0)" 
                        class="text-success" 
                        id="comment"
                        data-id-tax-invoice = "<?php echo e($entry->id); ?>"
                        data-route="<?php echo e(url('admin/send-comments')); ?>"
                    >
                        <strong><?php echo e($column['text']); ?></strong>
                    </a>
                <?php else: ?>
                    <a href="javascript:void(0)" 
                        class="text-info" 
                        data-toggle="modal" 
                        data-id-tax-invoice="<?php echo e($entry->id); ?>" 
                        data-target=".bd-example-modal-lg" 
                        data-route="<?php echo e(url('admin/send-comments')); ?>"
                        id="comment"
                    >
                        <strong><?php echo e($column['text']); ?></strong>
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="javascript:void(0)" 
                    id="comment" 
                    data-id-tax-invoice="<?php echo e($entry->id); ?>" 
                    class="text-dark"
                    data-route="<?php echo e(url('admin/send-comments')); ?>"
                >
                    <?php echo e($column['text']); ?>

                </a>
            <?php endif; ?>
            <?php if($entry->comment == null): ?>
                <a href="javascript:void(0)" 
                    id="comment" 
                    data-id-tax-invoice="<?php echo e($entry->id); ?>" 
                    class="text-info"
                    data-route="<?php echo e(url('admin/send-comments')); ?>"
                >
                    <i>Add Comment</i>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <?php echo $column['text']; ?>

        <?php endif; ?>
    <?php echo $__env->renderWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path'])); ?>
</div>
<script>
    // var entries = <?php echo json_encode($entry); ?>;
    // console.log(entries);
    if(typeof openCommentModal != 'function'){
        function openCommentModal(){
            $('a#comment').click(function(e){
                $('.comment-modal').removeAttr('data-id-tax-invoice');
                let tax_id = $(this).attr('data-id-tax-invoice');
                let route = $(this).attr('data-route');
                if(tax_id !== undefined){
                    $('.comment-modal').attr('data-id-tax-invoice', tax_id);
                    $('.comment-modal').attr('data-route', route);
                }
                $('.comment-modal').modal('show');
            });
        }
    }
    openCommentModal();
</script>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/columns/comment.blade.php ENDPATH**/ ?>