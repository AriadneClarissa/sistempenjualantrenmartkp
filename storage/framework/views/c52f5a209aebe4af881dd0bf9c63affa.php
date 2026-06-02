<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="mb-4 text-sm text-gray-600">
        <?php echo e(__('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.')); ?>

    </div>

    <?php if(session('status') == 'verification-link-sent'): ?>
        <div class="mb-4 font-medium text-sm text-green-600">
            <?php echo e(__('A new verification link has been sent to the email address you provided during registration.')); ?>

        </div>
    <?php endif; ?>

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="<?php echo e(route('verification.send')); ?>">
            <?php echo csrf_field(); ?>

            <div>
                <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <?php echo e(__('Resend Verification Email')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
            </div>
        </form>

        <form id="logoutForm" method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>

            <button type="button" id="logoutTrigger" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <?php echo e(__('Log Out')); ?>

            </button>
        </form>
    </div>

    <div id="logoutConfirmModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
        <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-lg">
            <h3 class="text-base font-semibold text-gray-900">Konfirmasi Keluar</h3>
            <p class="mt-2 text-sm text-gray-600">Yakin ingin keluar dari akun?</p>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" id="logoutCancel" class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="button" id="logoutConfirm" class="rounded-md bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700">Ya, Keluar</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const logoutForm = document.getElementById('logoutForm');
            const trigger = document.getElementById('logoutTrigger');
            const modal = document.getElementById('logoutConfirmModal');
            const cancelBtn = document.getElementById('logoutCancel');
            const confirmBtn = document.getElementById('logoutConfirm');

            if (!logoutForm || !trigger || !modal) return;

            const openModal = function () {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            const closeModal = function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            trigger.addEventListener('click', openModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
            if (confirmBtn) confirmBtn.addEventListener('click', function () {
                logoutForm.submit();
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });
        });
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH C:\Users\asus\OneDrive\Documents\GitHub\tesKP\resources\views\auth\verify-email.blade.php ENDPATH**/ ?>