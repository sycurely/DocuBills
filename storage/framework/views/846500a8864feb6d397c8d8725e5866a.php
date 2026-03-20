

<?php $__env->startSection('title', 'Email Templates'); ?>

<?php $__env->startPush('styles'); ?>
<style>

    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --dark: #212529;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
      --radius: 10px;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .page-title {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary);
    }

    .btn {
      padding: 0.75rem 1.5rem;
      border-radius: var(--radius);
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--secondary);
    }

    .btn-danger {
      background: var(--danger);
      color: white;
    }

    .btn-secondary {
      background: #6c757d;
      color: white;
    }

    .table-container {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid var(--border);
    }

    th {
      background: rgba(67, 97, 238, 0.1);
      color: var(--primary);
      font-weight: 600;
    }

    tbody tr:hover {
      background: rgba(67, 97, 238, 0.05);
    }

    .badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.875rem;
      font-weight: 600;
      display: inline-block;
    }

    .badge-primary {
      background: var(--primary);
      color: white;
    }
  
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

  <div class="container">
    <div class="page-header">
      <h1 class="page-title">Email Templates</h1>
      <?php if(has_permission('add_email_template')): ?>
        <a href="<?php echo e(route('email-templates.create')); ?>" class="btn btn-primary">
          <i class="fas fa-plus"></i> Add Template
        </a>
      <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
      <div style="background: var(--success); color: white; padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
        <?php echo e(session('success')); ?>

      </div>
    <?php endif; ?>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Template Name</th>
            <th>Subject</th>
            <th>Created By</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $__empty_1 = true; $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
              <td><strong><?php echo e($template->template_name); ?></strong></td>
              <td><?php echo e(Str::limit($template->subject, 50)); ?></td>
              <td><?php echo e($template->creator->username ?? 'N/A'); ?></td>
              <td>
                <div style="display: flex; gap: 0.5rem;">
                  <a href="<?php echo e(route('email-templates.show', $template)); ?>" class="btn" style="padding: 0.5rem; background: var(--primary); color: white;">
                    <i class="fas fa-eye"></i>
                  </a>
                  <?php if(has_permission('edit_email_template')): ?>
                    <a href="<?php echo e(route('email-templates.edit', $template)); ?>" class="btn" style="padding: 0.5rem; background: #f8961e; color: white;">
                      <i class="fas fa-edit"></i>
                    </a>
                  <?php endif; ?>
                  <?php if(has_permission('delete_email_template')): ?>
                    <form method="POST" action="<?php echo e(route('email-templates.destroy', $template)); ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this template?');">
                      <?php echo csrf_field(); ?>
                      <?php echo method_field('DELETE'); ?>
                      <button type="submit" class="btn" style="padding: 0.5rem; background: var(--danger); color: white;">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
              <td colspan="4" style="text-align: center; padding: 2rem;">
                No email templates found.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/email-templates/index.blade.php ENDPATH**/ ?>