<?php
    $field['wrapper'] = $field['wrapper'] ?? $field['wrapperAttributes'] ?? [];
    $field['wrapper']['data-init-function'] = $field['wrapper']['data-init-function'] ?? 'bpFieldInitUploadElement';
    $field['wrapper']['data-field-name'] = $field['wrapper']['data-field-name'] ?? $field['name'];
?>

<!-- text input -->
<?php echo $__env->make('crud::fields.inc.wrapper_start', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <label><?php echo $field['label']; ?></label>
    <?php echo $__env->make('crud::fields.inc.translatable_icon', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

	
    <?php if(!empty($field['value'])): ?>
    <div class="existing-file">
        <?php if(isset($field['disk'])): ?>
        <?php if(isset($field['temporary'])): ?>
            <a target="_blank" href="<?php echo e((asset(\Storage::disk($field['disk'])->temporaryUrl(Arr::get($field, 'prefix', '').$field['value'], Carbon\Carbon::now()->addMinutes($field['temporary']))))); ?>">
        <?php else: ?>
            <a target="_blank" href="<?php echo e((asset(\Storage::disk($field['disk'])->url(Arr::get($field, 'prefix', '').$field['value'])))); ?>">
        <?php endif; ?>
        <?php else: ?>
            <a target="_blank" href="<?php echo e((asset(Arr::get($field, 'prefix', '').$field['value']))); ?>">
        <?php endif; ?>
            <?php echo e($field['value']); ?>

        </a>
    	<a href="#" class="file_clear_button btn btn-light btn-sm float-right" title="Clear file"><i class="la la-remove"></i></a>
    	<div class="clearfix"></div>
    </div>
    <?php endif; ?>

	
    <div class="backstrap-file <?php echo e(isset($field['value']) && $field['value']!=null?'d-none':''); ?>">
        <input
            type="file"
            name="<?php echo e($field['name']); ?>"
            value="<?php echo e(old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? ''); ?>"
            <?php echo $__env->make('crud::fields.inc.attributes', ['default_class' =>  isset($field['value']) && $field['value']!=null?'file_input backstrap-file-input':'file_input backstrap-file-input'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        >
        <label class="backstrap-file-label" for="customFile"></label>
    </div>

    
    <?php if(isset($field['hint'])): ?>
        <p class="help-block"><?php echo $field['hint']; ?></p>
    <?php endif; ?>
<?php echo $__env->make('crud::fields.inc.wrapper_end', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>






<?php if($crud->fieldTypeNotLoaded($field)): ?>
    <?php
        $crud->markFieldTypeAsLoaded($field);
    ?>

    <?php $__env->startPush('crud_fields_styles'); ?>
        <style type="text/css">
            .existing-file {
                border: 1px solid rgba(0,40,100,.12);
                border-radius: 5px;
                padding-left: 10px;
                vertical-align: middle;
            }
            .existing-file a {
                padding-top: 5px;
                display: inline-block;
                font-size: 0.9em;
            }
            .backstrap-file {
              position: relative;
              display: inline-block;
              width: 100%;
              height: calc(1.5em + 0.75rem + 2px);
              margin-bottom: 0;
            }

            .backstrap-file-input {
              position: relative;
              z-index: 2;
              width: 100%;
              height: calc(1.5em + 0.75rem + 2px);
              margin: 0;
              opacity: 0;
            }

            .backstrap-file-input:focus ~ .backstrap-file-label {
              border-color: #acc5ea;
              box-shadow: 0 0 0 0rem rgba(70, 127, 208, 0.25);
            }

            .backstrap-file-input:disabled ~ .backstrap-file-label {
              background-color: #e4e7ea;
            }

            .backstrap-file-input:lang(en) ~ .backstrap-file-label::after {
              content: "Browse";
            }

            .backstrap-file-input ~ .backstrap-file-label[data-browse]::after {
              content: attr(data-browse);
            }

            .backstrap-file-label {
              position: absolute;
              top: 0;
              right: 0;
              left: 0;
              z-index: 1;
              height: calc(1.5em + 0.75rem + 2px);
              padding: 0.375rem 0.75rem;
              font-weight: 400;
              line-height: 1.5;
              color: #5c6873;
              background-color: #fff;
              border: 1px solid #e4e7ea;
              border-radius: 0.25rem;
              font-weight: 400!important;
            }

            .backstrap-file-label::after {
              position: absolute;
              top: 0;
              right: 0;
              bottom: 0;
              z-index: 3;
              display: block;
              height: calc(1.5em + 0.75rem);
              padding: 0.375rem 0.75rem;
              line-height: 1.5;
              color: #5c6873;
              content: "Browse";
              background-color: #f0f3f9;
              border-left: inherit;
              border-radius: 0 0.25rem 0.25rem 0;
            }
        </style>
    <?php $__env->stopPush(); ?>

    <?php $__env->startPush('crud_fields_scripts'); ?>
        <!-- no scripts -->
        <script>
            function bpFieldInitUploadElement(element) {
                var fileInput = element.find(".file_input");
                var fileClearButton = element.find(".file_clear_button");
                var fieldName = element.attr('data-field-name');
                var inputWrapper = element.find(".backstrap-file");
                var inputLabel = element.find(".backstrap-file-label");

                fileClearButton.click(function(e) {
                    e.preventDefault();
                    $(this).parent().addClass('d-none');

                    fileInput.parent().removeClass('d-none');
                    fileInput.attr("value", "").replaceWith(fileInput.clone(true));

                    // redo the selector, so we can use the same fileInput variable going forward
                    fileInput = element.find(".file_input");

                    // add a hidden input with the same name, so that the setXAttribute method is triggered
                    $("<input type='hidden' name='"+fieldName+"' value=''>").insertAfter(fileInput);
                });

                fileInput.change(function() {
                    var path = $(this).val();
                    var path = path.replace("C:\\fakepath\\", "");
                    inputLabel.html(path);
                    // remove the hidden input, so that the setXAttribute method is no longer triggered
                    $(this).next("input[type=hidden]").remove();
                });

            }
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/fields/upload.blade.php ENDPATH**/ ?>