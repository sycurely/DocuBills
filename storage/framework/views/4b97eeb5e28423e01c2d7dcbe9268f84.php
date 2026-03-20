<?php
  $activeMenu = 'settings';
  $activeTab = 'permissions';
?>

<?php $__env->startSection('title', 'Settings - Permissions'); ?>

<?php $__env->startPush('styles'); ?>
<style>
  .role-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  .role-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }
  .role-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary);
  }
  .permission-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 0.75rem 1rem;
  }
  .perm-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.5rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--card-bg);
  }
  .perm-item input {
    margin: 0;
  }
  .perm-item span {
    font-size: 0.95rem;
  }
  .role-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
  }
  .btn-sm {
    padding: 0.45rem 0.9rem;
    font-size: 0.9rem;
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
  <div class="page-header">
    <h1 class="page-title">Permission Matrix</h1>
    <p class="page-subtitle">Assign permissions to roles.</p>
  </div>

  <?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
  <?php endif; ?>
  <?php if($errors->any()): ?>
    <div class="alert alert-danger">
      <strong>There were some problems with your input.</strong>
    </div>
  <?php endif; ?>

  <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="role-card">
      <form method="POST" action="<?php echo e(route('settings.permissions.update', $role)); ?>">
        <?php echo csrf_field(); ?>
        <div class="role-header">
          <div class="role-title"><?php echo e(ucwords(str_replace('_', ' ', $role->name))); ?></div>
          <div class="role-actions">
            <button type="button" class="btn btn-outline btn-sm" onclick="applyRecommended('<?php echo e($role->name); ?>', '<?php echo e($role->id); ?>')">Apply Recommended</button>
            <button type="submit" class="btn btn-primary btn-sm">Save</button>
          </div>
        </div>

        <div class="permission-grid" id="role-<?php echo e($role->id); ?>">
          <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label class="perm-item">
              <input type="checkbox" name="permissions[]" value="<?php echo e($permission->id); ?>"
                <?php echo e($role->permissions->contains('id', $permission->id) ? 'checked' : ''); ?>>
              <span><?php echo e($permission->name); ?></span>
            </label>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </form>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
  async function applyRecommended(roleName, roleId) {
    try {
      const url = '<?php echo e(route("api.settings.recommended-permissions")); ?>' + '?role=' + encodeURIComponent(roleName);
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) throw new Error('Failed to load recommended permissions');
      const data = await res.json();

      const grid = document.getElementById('role-' + roleId);
      if (!grid) return;

      const selected = new Set(data || []);
      grid.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
        const label = cb.nextElementSibling ? cb.nextElementSibling.textContent.trim() : '';
        cb.checked = selected.has(label);
      });
    } catch (e) {
      alert('Error: ' + e.message);
    }
  }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/settings/permissions.blade.php ENDPATH**/ ?>