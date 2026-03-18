<?php
$activeMenu = 'invoice-fields';
require_once 'config.php';
require 'styles.php';

// Handle field addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_field'])) {
    $heading = trim($_POST['heading']);
    $field_key = trim($_POST['field_key']);
    $field_type = $_POST['field_type'];

    if ($heading && $field_key) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM invoice_fields");
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count < 8) {
            $stmt = $pdo->prepare("INSERT INTO invoice_fields (heading, field_key, field_type) VALUES (?, ?, ?)");
            $stmt->execute([$heading, $field_key, $field_type]);
            $success = "Field added successfully.";
        } else {
            $error = "Maximum of 8 fields allowed.";
        }
    } else {
        $error = "Heading and Field Key are required.";
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM invoice_fields WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: invoice-fields.php");
    exit;
}

// Fetch all fields
$stmt = $pdo->query("SELECT * FROM invoice_fields ORDER BY id ASC");
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require 'sidebar.php'; ?>
<div class="main-content">
  <div class="container">
    <h2 class="mb-4">Custom Invoice Item Fields</h2>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="mb-4">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Column Heading</label>
          <input type="text" name="heading" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Field Key</label>
          <input type="text" name="field_key" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Field Type</label>
          <select name="field_type" class="form-select">
            <option value="text">Text</option>
            <option value="number">Number</option>
            <option value="dropdown">Dropdown</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" name="add_field" class="btn btn-primary w-100">Add Field</button>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center">
        <thead style="background: rgba(67, 97, 238, 0.1); color: var(--primary); font-weight: 600;">
          <tr>
            <th>#</th>
            <th>Column Heading</th>
            <th>Field Key</th>
            <th>Field Type</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($fields as $index => $field): ?>
            <tr>
              <td><?= $index + 1 ?></td>
              <td><?= htmlspecialchars($field['heading']) ?></td>
              <td><?= htmlspecialchars($field['field_key']) ?></td>
              <td><?= ucfirst($field['field_type']) ?></td>
              <td>
                <a href="?delete=<?= $field['id'] ?>" onclick="return confirm('Delete this field?')" class="btn btn-sm btn-danger">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require 'scripts.php'; ?>
