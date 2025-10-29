<?php $__env->startSection('title', __('messages.Edit_Driver')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('messages.Edit_Driver')); ?></h1>
        <a href="<?php echo e(route('drivers.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?php echo e(__('messages.Back_to_List')); ?>

        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('messages.Driver_Details')); ?></h6>
        </div>
        <div class="card-body">
            <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <?php endif; ?>

            <form action="<?php echo e(route('drivers.update', $driver->id)); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Basic Information -->
                        <div class="form-group">
                            <label for="name"><?php echo e(__('messages.Name')); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo e(old('name', $driver->name)); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone"><?php echo e(__('messages.Phone')); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo e(old('phone', $driver->phone)); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><?php echo e(__('messages.Email')); ?></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo e(old('email', $driver->email)); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="password"><?php echo e(__('messages.Password')); ?></label>
                            <input type="password" class="form-control" id="password" name="password">
                            <small class="form-text text-muted"><?php echo e(__('messages.Leave_blank_to_keep_current_password')); ?></small>
                        </div>
                        
                        <div class="form-group">
                            <label><?php echo e(__('Options')); ?></label>
                            <div class="checkbox-list">
                                <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="checkbox">
                                        <input type="checkbox" name="option_ids[]" value="<?php echo e($option->id); ?>" 
                                        <?php if(isset($driver) && $driver->options->contains($option->id)): ?>
                                            checked
                                        <?php elseif(old('option_ids') && in_array($option->id, old('option_ids'))): ?>
                                            checked
                                        <?php endif; ?>
                                        >
                                        <span><?php echo e($option->name); ?> </span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php $__errorArgs = ['option_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="balance"><?php echo e(__('messages.Balance')); ?></label>
                            <input type="number" step="0.01" class="form-control" id="balance" name="balance" value="<?php echo e(old('balance', $driver->balance)); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="activate"><?php echo e(__('messages.Status')); ?></label>
                            <select class="form-control" id="activate" name="activate">
                                <option value="1" <?php echo e(old('activate', $driver->activate) == 1 ? 'selected' : ''); ?>><?php echo e(__('messages.Active')); ?></option>
                                <option value="2" <?php echo e(old('activate', $driver->activate) == 2 ? 'selected' : ''); ?>><?php echo e(__('messages.Inactive')); ?></option>
                                <option value="3" <?php echo e(old('activate', $driver->activate) == 3 ? 'selected' : ''); ?>><?php echo e(__('messages.Waiting Approve')); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Driver Photo -->
                        <div class="form-group">
                            <label for="photo"><?php echo e(__('messages.Driver_Photo')); ?></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="photo" name="photo">
                                <label class="custom-file-label" for="photo"><?php echo e(__('messages.Choose_file')); ?></label>
                            </div>
                            <div class="mt-3" id="photo-preview">
                                <?php if($driver->photo): ?>
                                <img src="<?php echo e(asset('assets/admin/uploads/' . $driver->photo)); ?>" alt="<?php echo e($driver->name); ?>" class="img-fluid img-thumbnail" style="max-height: 150px;">
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Car Information -->
                        <h5 class="mt-4 mb-3"><?php echo e(__('messages.Car_Information')); ?></h5>
                        
                        <div class="form-group">
                            <label for="model"><?php echo e(__('messages.Car_Model')); ?></label>
                            <input type="text" class="form-control" id="model" name="model" value="<?php echo e(old('model', $driver->model)); ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="production_year"><?php echo e(__('messages.Production_Year')); ?></label>
                                    <input type="text" class="form-control" id="production_year" name="production_year" value="<?php echo e(old('production_year', $driver->production_year)); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color"><?php echo e(__('messages.Color')); ?></label>
                                    <input type="text" class="form-control" id="color" name="color" value="<?php echo e(old('color', $driver->color)); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="plate_number"><?php echo e(__('messages.Plate_Number')); ?></label>
                            <input type="text" class="form-control" id="plate_number" name="plate_number" value="<?php echo e(old('plate_number', $driver->plate_number)); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="photo_of_car"><?php echo e(__('messages.Car_Photo')); ?></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="photo_of_car" name="photo_of_car">
                                <label class="custom-file-label" for="photo_of_car"><?php echo e(__('messages.Choose_file')); ?></label>
                            </div>
                            <div class="mt-3" id="car-preview">
                                <?php if($driver->photo_of_car): ?>
                                <img src="<?php echo e(asset('assets/admin/uploads/' . $driver->photo_of_car)); ?>" alt="Car Photo" class="img-fluid img-thumbnail" style="max-height: 150px;">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Documents Section -->
                <h5 class="mt-4 mb-3"><?php echo e(__('messages.Documents')); ?></h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="driving_license_front"><?php echo e(__('messages.Driving_License_Front')); ?></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="driving_license_front" name="driving_license_front">
                                <label class="custom-file-label" for="driving_license_front"><?php echo e(__('messages.Choose_file')); ?></label>
                            </div>
                            <div class="mt-3" id="driving-license-front-preview">
                                <?php if($driver->driving_license_front): ?>
                                <img src="<?php echo e(asset('assets/admin/uploads/' . $driver->driving_license_front)); ?>" alt="Driving License Front" class="img-fluid img-thumbnail" style="max-height: 150px;">
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="car_license_front"><?php echo e(__('messages.Car_License_Front')); ?></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="car_license_front" name="car_license_front">
                                <label class="custom-file-label" for="car_license_front"><?php echo e(__('messages.Choose_file')); ?></label>
                            </div>
                            <div class="mt-3" id="car-license-front-preview">
                                <?php if($driver->car_license_front): ?>
                                <img src="<?php echo e(asset('assets/admin/uploads/' . $driver->car_license_front)); ?>" alt="Car License Front" class="img-fluid img-thumbnail" style="max-height: 150px;">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="driving_license_back"><?php echo e(__('messages.Driving_License_Back')); ?></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="driving_license_back" name="driving_license_back">
                                <label class="custom-file-label" for="driving_license_back"><?php echo e(__('messages.Choose_file')); ?></label>
                            </div>
                            <div class="mt-3" id="driving-license-back-preview">
                                <?php if($driver->driving_license_back): ?>
                                <img src="<?php echo e(asset('assets/admin/uploads/' . $driver->driving_license_back)); ?>" alt="Driving License Back" class="img-fluid img-thumbnail" style="max-height: 150px;">
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="car_license_back"><?php echo e(__('messages.Car_License_Back')); ?></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="car_license_back" name="car_license_back">
                                <label class="custom-file-label" for="car_license_back"><?php echo e(__('messages.Choose_file')); ?></label>
                            </div>
                            <div class="mt-3" id="car-license-back-preview">
                                <?php if($driver->car_license_back): ?>
                                <img src="<?php echo e(asset('assets/admin/uploads/' . $driver->car_license_back)); ?>" alt="Car License Back" class="img-fluid img-thumbnail" style="max-height: 150px;">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo e(__('messages.Update')); ?>

                    </button>
                    <a href="<?php echo e(route('drivers.index')); ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> <?php echo e(__('messages.Cancel')); ?>

                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
    // Show image previews
    $(document).ready(function() {
        // Show filename on file select
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
            
            // Image preview
            if (this.files && this.files[0]) {
                let reader = new FileReader();
                let previewId = '';
                
                // Determine which preview to update
                switch(this.id) {
                    case 'photo':
                        previewId = 'photo-preview';
                        break;
                    case 'photo_of_car':
                        previewId = 'car-preview';
                        break;
                    case 'driving_license_front':
                        previewId = 'driving-license-front-preview';
                        break;
                    case 'driving_license_back':
                        previewId = 'driving-license-back-preview';
                        break;
                    case 'car_license_front':
                        previewId = 'car-license-front-preview';
                        break;
                    case 'car_license_back':
                        previewId = 'car-license-back-preview';
                        break;
                }
                
                if (previewId) {
                    reader.onload = function(e) {
                        $('#' + previewId).html('<img src="' + e.target.result + '" class="img-fluid img-thumbnail" style="max-height: 150px;">');
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\taksi\resources\views/admin/drivers/edit.blade.php ENDPATH**/ ?>