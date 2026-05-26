

<?php $__env->startSection('content'); ?>
<style>
    .loading-wrap {
        min-height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    .loading-card {
        width: 100%;
        max-width: 520px;
        background: #fff;
        border: 1px solid #f0e5e5;
        border-radius: 20px;
        box-shadow: 0 18px 40px rgba(120, 0, 0, 0.08);
        text-align: center;
        padding: 36px 28px;
    }

    .loading-spinner {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        border: 4px solid #f3dede;
        border-top-color: #800000;
        margin: 0 auto 18px;
        animation: spin 0.85s linear infinite;
    }

    .loading-title {
        color: #800000;
        font-weight: 700;
        font-size: 1.08rem;
        margin-bottom: 6px;
    }

    .loading-subtitle {
        color: #666;
        font-size: 0.92rem;
        margin: 0;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<div class="container loading-wrap">
    <div class="loading-card">
        <div class="loading-spinner" aria-hidden="true"></div>
        <h1 class="loading-title">Menyiapkan halaman Anda...</h1>
        <p class="loading-subtitle">Mohon tunggu sebentar, Anda akan diarahkan otomatis.</p>

        <noscript>
            <p class="mt-3 mb-0">
                <a href="<?php echo e($targetUrl); ?>" class="text-decoration-none fw-semibold" style="color:#800000;">Lanjutkan ke halaman tujuan</a>
            </p>
        </noscript>
    </div>
</div>

<script>
    setTimeout(function () {
        window.location.href = <?php echo json_encode($targetUrl, 15, 512) ?>;
    }, 1800);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\asus\OneDrive\Documents\GitHub\tesKP\resources\views/auth/loading.blade.php ENDPATH**/ ?>