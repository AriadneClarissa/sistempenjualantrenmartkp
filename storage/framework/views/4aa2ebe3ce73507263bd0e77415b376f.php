

<?php $__env->startSection('content'); ?>
<style>
    .log-page-title {
        color: #660000;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .btn-rounded-soft {
        border-radius: 999px !important;
        padding: 0.55rem 1rem;
        font-weight: 600;
    }

    .log-table thead th {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .log-pagination .page-link {
        border-radius: 999px !important;
        margin: 0 4px;
        border: 1px solid #dee2e6;
        color: #660000;
        box-shadow: none !important;
    }

    .log-pagination .page-item.active .page-link {
        background: #660000;
        border-color: #660000;
        color: #fff;
    }

    .log-pagination .page-item.disabled .page-link {
        color: #adb5bd;
    }
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3 gap-3 flex-wrap">
        <h1 class="mb-0 log-page-title">Log Aktivitas Internal</h1>
        <a href="<?php echo e(route('beranda')); ?>" class="btn btn-outline-secondary btn-rounded-soft">
            Kembali ke Beranda
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh; overflow: auto;">
            <table class="table mb-0 align-middle log-table" style="min-width: 980px;">
                <thead style="position: sticky; top: 0; z-index: 2;">
                    <tr>
                        <th>#</th>
                        <th>Waktu</th>
                        <th>Pelaku</th>
                        <th>Aksi</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($log->id); ?></td>
                        <td><?php echo e($log->created_at->copy()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s')); ?></td>
                        <td><?php echo e($log->actor ? $log->actor->name . ' (' . $log->actor->email . ')' : 'System'); ?></td>
                        <td><?php echo e($log->action); ?></td>
                        <td style="max-width:420px;overflow-wrap:break-word"><?php echo e($log->details); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada log</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <div class="log-pagination">
            <?php echo e($logs->links('vendor.pagination.rounded-indonesia')); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\asus\OneDrive\Documents\GitHub\tesKP\resources\views\admin\logs\index.blade.php ENDPATH**/ ?>