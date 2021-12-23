<!-- number input -->
<?php echo $__env->make('crud::fields.inc.wrapper_start', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <label><?php echo $field['label']; ?></label>
    <?php echo $__env->make('crud::fields.inc.translatable_icon', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php if(isset($field['prefix']) || isset($field['suffix'])): ?> <div class="input-group"> <?php endif; ?>
        <?php if(isset($field['prefix'])): ?> <div class="input-group-prepend"><span class="input-group-text"><?php echo $field['prefix']; ?></span></div> <?php endif; ?>
        <span class="info-qty text-danger"></span>
        <input
            id="current-qty"
        	type="number"
        	name="<?php echo e($field['name']); ?>"
            value="<?php echo e(old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? ''); ?>"
            <?php echo $__env->make('crud::fields.inc.attributes', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        	>
        <?php if(isset($field['suffix'])): ?> <div class="input-group-append"><span class="input-group-text"><?php echo $field['suffix']; ?></span></div> <?php endif; ?>

    <?php if(isset($field['prefix']) || isset($field['suffix'])): ?> </div> <?php endif; ?>

    
    <?php if(isset($field['hint'])): ?>
        <p class="help-block"><?php echo $field['hint']; ?></p>
    <?php endif; ?>
<?php echo $__env->make('crud::fields.inc.wrapper_end', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->startPush('crud_fields_scripts'); ?>
<script>
    $( document ).ready(function() {
        var maxQty = parseFloat( $( "#current-qty" ).data('max'))
        var initCurrent = parseFloat( $( "#current-qty" ).val())
        var initUrl = $('#template-upload-sn').attr('init-url')
        if (parseFloat(initCurrent) > parseFloat(maxQty)) {
            $('.info-qty').html('<small>Jumlah Qty melebihi batas maksimal ('+maxQty+')</small>')
        }
        $('#template-upload-sn').attr('href', initUrl+'?qty='+maxQty)
    
        $( "#current-qty" ).keyup(function() {
            var initUrl = $('#template-upload-sn').attr('init-url')
            var currentQty = parseFloat($(this).val())
            $('#template-upload-sn').attr('href', initUrl+'?qty='+currentQty)
            $('#allowed-qty').val(currentQty)

            if (parseFloat(currentQty) > parseFloat(maxQty)) {
                var message = "Jumlah qty melebihi batas (max. "+maxQty+")"
                $('.info-qty').html('<small>'+message+'</small>')
                $('.list-error').html('<li>'+message+'</li>')
            }else{
                $('.info-qty').html('')
                $('.list-error').html('')
            }
            if($('*').hasClass('form-issued')){
                outhouseTableManager(currentQty)
            } 
        });
    });

    
</script>

<?php $__env->stopPush(); ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/fields/number_qty.blade.php ENDPATH**/ ?>