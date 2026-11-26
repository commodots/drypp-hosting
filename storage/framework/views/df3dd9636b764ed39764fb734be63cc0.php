<?php $__env->startSection('title', 'Support and Renewals'); ?>
<?php $__env->startSection('content'); ?>
    <div class="admin-page-heading">
        <div>
            <h1>Support and Renewals</h1>
            <p>Identify services needing fulfilment attention and services approaching their recorded expiry date.</p>
        </div>
    </div>
    <div class="admin-grid">
        <section class="admin-panel">
            <h2>Needs attention</h2>
            <?php if($supportItems->isEmpty()): ?>
                <div class="admin-empty">No pending fulfilment or provisioning issues.</div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Customer</th>
                                <th>Order</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $supportItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><strong><?php echo e($service->name); ?></strong><br><span><?php echo e($service->identifier ?: ucfirst($service->service_type)); ?></span>
                                    </td>
                                    <td><?php echo e($service->user?->name); ?><br><span><?php echo e($service->user?->email); ?></span></td>
                                    <td><?php echo e($service->order?->order_number ?: '—'); ?></td>
                                    <td><span class="admin-status"
                                            data-state="<?php echo e(str_contains($service->status, 'failed') ? 'failed' : 'pending'); ?>"><?php echo e(str_replace('_', ' ', ucfirst($service->status))); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:16px"><?php echo e($supportItems->links()); ?></div>
            <?php endif; ?>
        </section>
        <section class="admin-panel">
            <h2>Expiring in 30 days or overdue</h2>
            <?php if($renewals->isEmpty()): ?>
                <div class="admin-empty">No service expiry dates are recorded in the next 30 days.</div>
            <?php else: ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Customer</th>
                                <th>Expires</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $renewals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><strong><?php echo e($service->name); ?></strong><br><span><?php echo e($service->identifier ?: ucfirst($service->service_type)); ?></span>
                                    </td>
                                    <td><?php echo e($service->user?->name); ?><br><span><?php echo e($service->user?->email); ?></span></td>
                                    <td><?php echo e($service->expires_at?->format('M j, Y')); ?></td>
                                    <td><span class="admin-status"
                                            data-state="<?php echo e($service->expires_at?->isPast() ? 'failed' : 'pending'); ?>"><?php echo e($service->expires_at?->isPast() ? 'Overdue' : 'Upcoming'); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:16px"><?php echo e($renewals->links()); ?></div>
            <?php endif; ?>
        </section>
    </div>
    <div class="admin-alert admin-alert-info" role="note">Support tickets and automated renewal billing are not connected
        in this release. Use these lists to identify follow-up work; no charges or provider changes are made here.</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\NEMO\drypp-hosting\resources\views/admin/operations/support-renewals.blade.php ENDPATH**/ ?>