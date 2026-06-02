

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('admin._header', ['activePage' => 'payment'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="fw-bold ms-0">Manajemen Metode Pembayaran</h4>
    </div>

    <div class="mb-4">
        <form action="<?php echo e(route('admin.payment_methods.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="row g-2">
                <div class="col-md-3">
                    <input name="name" class="form-control" placeholder="Nama metode (Misal: BCA)" required>
                </div>
                <div class="col-md-3">
                    <input name="account_name" class="form-control" placeholder="Pemilik rekening">
                </div>
                <div class="col-md-3">
                    <input name="account_number" class="form-control" placeholder="No. rekening">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary px-4">Tambah</button>
                </div>
            </div>
        </form>
    </div>

    <div class="card shadow-sm w-100">
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle w-100 mb-0" style="font-size: 0.95rem; table-layout: fixed;">
                    <thead class="table-light">
                        <tr>
                            <th class="py-1 px-2" style="width: 5%;">#</th>
                            <th class="py-1 px-2" style="width: 25%;">Nama Metode</th>
                            <th class="py-1 px-2" style="width: 30%;">Pemilik Rekening</th>
                            <th class="py-1 px-2" style="width: 30%;">No. Rekening</th>
                            <th class="py-1 px-2" style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $methods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="py-1 px-2 text-truncate"><?php echo e($index + 1); ?></td>
                            <td class="py-1 px-2 text-truncate"><?php echo e($m->name); ?></td>
                            <td class="py-1 px-2 text-truncate"><?php echo e($m->account_name ?? '-'); ?></td>
                            <td class="py-1 px-2 text-truncate"><?php echo e($m->account_number ?? '-'); ?></td>
                            <td class="py-1 px-2 text-truncate">
                                <form action="<?php echo e(route('admin.payment_methods.destroy', $m->id)); ?>" method="POST" class="m-0">
                                    <?php echo csrf_field(); ?> 
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger px-3 py-0">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="py-3 px-2 text-center text-muted">Belum ada metode pembayaran yang ditambahkan.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\asus\OneDrive\Documents\GitHub\tesKP\resources\views\admin\payment_methods\index.blade.php ENDPATH**/ ?>