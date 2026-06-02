

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('admin._header', ['activePage' => 'internal_users'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<style>
    .password-field-wrap {
        position: relative;
    }

    .password-field-wrap .form-control {
        padding-right: 44px;
    }

    .password-eye-btn {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        color: #6c757d;
        padding: 0;
        line-height: 1;
        z-index: 3;
    }

    .password-eye-btn:hover,
    .password-eye-btn:focus {
        color: #495057;
        outline: none;
        box-shadow: none;
    }

    .no-native-password-reveal::-ms-reveal,
    .no-native-password-reveal::-ms-clear,
    .no-native-password-reveal::-webkit-password-toggle-button,
    .no-native-password-reveal::-webkit-credentials-auto-fill-button {
        display: none;
        visibility: hidden;
        pointer-events: none;
    }
</style>

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold ms-0" id="internal-user-title">Buat Akun User Internal Baru</h4>
    </div>

    <div class="card p-4 shadow-sm w-100">
        <form action="<?php echo e(route('admin.admins.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="row g-3">

                <?php if($errors->any()): ?>
                    <div class="col-12">
                        <div class="alert alert-danger small mb-0">
                            <ul class="mb-0 ps-3">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="col-md-6">
                    <label class="form-label" id="name-label">Nama Lengkap User Internal</label>
                    <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="name-input" placeholder="Nama user internal" value="<?php echo e(old('name')); ?>" required>
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label" id="email-label">Email User Internal</label>
                    <input type="email" name="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="email-input" placeholder="Email user internal" value="<?php echo e(old('email')); ?>" required>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" id="role-select" class="form-select <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                        <option value="" <?php echo e(old('role') ? '' : 'selected'); ?>>-- Pilih Role --</option>
                        <option value="admin" <?php echo e(old('role') === 'admin' ? 'selected' : ''); ?>>Admin</option>
                        <option value="kasir" <?php echo e(old('role') === 'kasir' ? 'selected' : ''); ?>>Kasir</option>
                    </select>
                    <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="password-field-wrap">
                        <input type="password" name="default_password" id="default-password" class="form-control no-native-password-reveal <?php $__errorArgs = ['default_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Masukkan password manual" required>
                        <button type="button" class="password-eye-btn" id="toggle-password" aria-label="Tampilkan atau sembunyikan password">
                            <i class="bi bi-eye" id="toggle-password-icon"></i>
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">Minimal 8 karakter, wajib diisi manual.</small>
                    <?php $__errorArgs = ['default_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="col-12 mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="send_email" id="send_email" value="1" checked>
                        <label class="form-check-label" for="send_email" id="send-email-label">
                            Kirim email berisi kredensial ke user internal baru
                        </label>
                    </div>
                </div>

                <div class="col-12 text-end mt-4">
                    <a href="<?php echo e(route('admin.users.internal')); ?>" class="btn btn-outline-secondary me-2 px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">Buat Akun</button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role-select');
        const titleEl = document.getElementById('internal-user-title');
        const nameLabel = document.getElementById('name-label');
        const emailLabel = document.getElementById('email-label');
        const nameInput = document.getElementById('name-input');
        const emailInput = document.getElementById('email-input');
        const sendEmailLabel = document.getElementById('send-email-label');
        const passwordInput = document.getElementById('default-password');
        const togglePasswordButton = document.getElementById('toggle-password');
        const togglePasswordIcon = document.getElementById('toggle-password-icon');

        const applyRoleCopy = (role) => {
            if (role === 'admin') {
                titleEl.textContent = 'Buat Akun Admin Baru';
                nameLabel.textContent = 'Nama Lengkap Admin';
                emailLabel.textContent = 'Email Admin';
                nameInput.placeholder = 'Nama admin';
                emailInput.placeholder = 'Email admin';
                sendEmailLabel.textContent = 'Kirim email berisi kredensial ke admin baru';
                return;
            }

            if (role === 'kasir') {
                titleEl.textContent = 'Buat Akun Kasir Baru';
                nameLabel.textContent = 'Nama Lengkap Kasir';
                emailLabel.textContent = 'Email Kasir';
                nameInput.placeholder = 'Nama kasir';
                emailInput.placeholder = 'Email kasir';
                sendEmailLabel.textContent = 'Kirim email berisi kredensial ke kasir baru';
                return;
            }

            titleEl.textContent = 'Buat Akun User Internal Baru';
            nameLabel.textContent = 'Nama Lengkap User Internal';
            emailLabel.textContent = 'Email User Internal';
            nameInput.placeholder = 'Nama user internal';
            emailInput.placeholder = 'Email user internal';
            sendEmailLabel.textContent = 'Kirim email berisi kredensial ke user internal baru';
        };

        applyRoleCopy(roleSelect.value);
        roleSelect.addEventListener('change', function () {
            applyRoleCopy(this.value);
        });

        if (togglePasswordButton && passwordInput && togglePasswordIcon) {
            togglePasswordButton.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                togglePasswordIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\asus\OneDrive\Documents\GitHub\tesKP\resources\views\admin\admins\create.blade.php ENDPATH**/ ?>