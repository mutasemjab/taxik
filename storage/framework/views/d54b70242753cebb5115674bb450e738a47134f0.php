<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول — Taxi</title>
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/plugins/fontawesome-free/css/all.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/admin/css/login.css')); ?>">
</head>
<body class="taxik-login">

<div class="login-layout">

    
    <div class="login-brand">
        <div class="brand-circle-mid"></div>
        <div class="brand-content">

            <div class="brand-logo-wrap">
                <i class="fas fa-taxi"></i>
            </div>

            <div class="brand-name">TAXI</div>
            <p class="brand-tagline">
                لوحة تحكم متكاملة لإدارة رحلاتك<br>
                وسائقيك وعملائك بكل سهولة
            </p>

            <div class="brand-stats">
                <div class="brand-stat">
                    <div class="bs-num"><i class="fas fa-car"></i></div>
                    <div class="bs-lbl">سائقون</div>
                </div>
                <div class="brand-stat">
                    <div class="bs-num"><i class="fas fa-users"></i></div>
                    <div class="bs-lbl">مستخدمون</div>
                </div>
                <div class="brand-stat">
                    <div class="bs-num"><i class="fas fa-route"></i></div>
                    <div class="bs-lbl">رحلات</div>
                </div>
            </div>

        </div>
    </div>

    
    <div class="login-form-panel">
        <div class="login-box-inner">

            <div class="lf-header">
                <h2>مرحباً بك 👋</h2>
                <p>أدخل بيانات الدخول للوصول إلى لوحة التحكم</p>
            </div>

            <form action="<?php echo e(route('admin.login')); ?>" method="post" autocomplete="off">
                <?php echo csrf_field(); ?>

                
                <div class="lf-group">
                    <label for="username">اسم المستخدم</label>
                    <div class="lf-input-wrap">
                        <i class="fas fa-user lf-icon"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="أدخل اسم المستخدم"
                            value="<?php echo e(old('username')); ?>"
                            autocomplete="username"
                            required
                        >
                    </div>
                    <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="lf-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?>

                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div class="lf-group">
                    <label for="password">كلمة المرور</label>
                    <div class="lf-input-wrap">
                        <i class="fas fa-lock lf-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="أدخل كلمة المرور"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="lf-eye" id="toggle-pw" tabindex="-1">
                            <i class="fas fa-eye" id="eye-icon"></i>
                        </button>
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="lf-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?>

                        </div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <button type="submit" class="lf-btn">
                    <i class="fas fa-sign-in-alt mr-2"></i> دخول
                </button>

            </form>


        </div>
    </div>

</div>

<script>
    document.getElementById('toggle-pw').addEventListener('click', function () {
        var pw   = document.getElementById('password');
        var icon = document.getElementById('eye-icon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            pw.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
</script>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\taxik\resources\views/admin/auth/login.blade.php ENDPATH**/ ?>