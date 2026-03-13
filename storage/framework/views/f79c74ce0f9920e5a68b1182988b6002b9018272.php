<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title><?php echo $__env->yieldContent('title'); ?></title>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/plugins/fontawesome-free/css/all.min.css')); ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/dist/css/adminlte.min.css')); ?>">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.4/dist/select2-bootstrap4.min.css" rel="stylesheet" />

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/fonts/SansPro/SansPro.min.css')); ?>">
    <?php if(App::getLocale() == 'ar'): ?>
        <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/bootstrap_rtl-v4.2.1/bootstrap.min.css')); ?>">
        <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/bootstrap_rtl-v4.2.1/custom_rtl.css')); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/mycustomstyle.css')); ?>">
    <?php echo $__env->yieldContent('css'); ?>
</head>
<body class="hold-transition sidebar-mini">
    <?php $user = auth()->user(); ?>
    <div class="wrapper">
        <!-- Navbar -->
        <?php echo $__env->make('admin.includes.navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!-- Main Sidebar Container -->
        <?php echo $__env->make('admin.includes.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!-- Content Wrapper. Contains page content -->
        <?php echo $__env->make('admin.includes.content', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!-- Footer -->
        <?php echo $__env->make('admin.includes.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->

    <!-- Bootstrap 4 -->
    <script src="<?php echo e(asset('assets/admin/plugins/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?php echo e(asset('assets/admin/dist/js/adminlte.min.js')); ?>"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="<?php echo e(asset('assets/admin/js/general.js')); ?>"></script>

    <!-- Universal Select2 Initialization -->
    <script>
        $(document).ready(function() {
            // Initialize Select2 for all selects with class 'select2'
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder') || '<?php echo e(__("messages.Select_Option")); ?>';
                },
                allowClear: true,
                <?php if(App::getLocale() == 'ar'): ?>
                language: {
                    noResults: function() {
                        return "لا توجد نتائج";
                    },
                    searching: function() {
                        return "جاري البحث...";
                    }
                },
                dir: 'rtl'
                <?php else: ?>
                language: {
                    noResults: function() {
                        return "No results found";
                    },
                    searching: function() {
                        return "Searching...";
                    }
                }
                <?php endif; ?>
            });

            // Re-initialize Select2 when new content is dynamically added
            $(document).on('DOMNodeInserted', function(e) {
                if ($(e.target).hasClass('select2') && !$(e.target).hasClass('select2-hidden-accessible')) {
                    $(e.target).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        allowClear: true
                    });
                }
            });
        });
    </script>

    <?php echo $__env->yieldContent('script'); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\taxik\resources\views/layouts/admin.blade.php ENDPATH**/ ?>