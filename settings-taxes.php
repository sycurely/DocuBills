<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$activeMenu = 'settings';
$activeTab = 'taxes';
require_once 'config.php';
require_once 'middleware.php';

if (!has_any_setting_permission()) {
  $_SESSION['access_denied'] = true;
  header('Location: access-denied.php');
  exit;
}

try {
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS taxes (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(100) NOT NULL,
      percentage DECIMAL(5,2) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  ");
} catch (Throwable $e) {
  // Leave table empty if creation fails; UI will still render.
}

function ensure_tax_columns(PDO $pdo) {
  $cols = $pdo->query("SHOW COLUMNS FROM taxes")->fetchAll(PDO::FETCH_COLUMN, 0);
  if (!in_array('tax_type', $cols, true)) {
    $pdo->exec("ALTER TABLE taxes ADD COLUMN tax_type VARCHAR(20) NOT NULL DEFAULT 'line'");
  }
  if (!in_array('calc_order', $cols, true)) {
    $pdo->exec("ALTER TABLE taxes ADD COLUMN calc_order INT NOT NULL DEFAULT 1");
  }
}

$taxRows = [];
try {
  ensure_tax_columns($pdo);
} catch (Throwable $e) {
  // Ignore column migration issues; keep UI functional.
}

try {
  $stmt = $pdo->query("SELECT id, name, percentage, tax_type, calc_order FROM taxes ORDER BY id ASC");
  $taxRows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
  $taxRows = [];
}

require 'styles.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Settings - Tax Classes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <?php require 'styles.php'; ?>
  <style>
    :root {
      --primary: #4361ee;
      --primary-light: #4895ef;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --warning: #f8961e;
      --dark: #212529;
      --light: #f8f9fa;
      --gray: #6c757d;
      --border: #dee2e6;
      --card-bg: #ffffff;
      --body-bg: #f5f7fb;
      --header-height: 70px;
      --sidebar-width: 250px;
      --transition: all 0.3s ease;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
      --radius: 10px;
      --sidebar-bg: #2c3e50;
    }
    .app-container { display: flex; min-height: 100vh; }
    .main-content { flex: 1; padding: calc(var(--header-height) + 1.5rem) 1.5rem 1.5rem; transition: var(--transition); }
    .page-header { display: flex; justify-content: flex-start; align-items: center; margin-bottom: 2rem; }
    .page-header-block { display: flex; flex-direction: column; gap: 0.35rem; }
    .page-kicker {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.85rem;
      font-weight: 700;
      color: #64748b;
      letter-spacing: 0.08em;
    }
    .page-kicker .material-symbols-outlined {
      font-size: 1.15rem;
      color: var(--primary);
      font-variation-settings: 'wght' 400;
    }
    .page-title-lg {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary);
    }
    .taxes-body { display: flex; flex-direction: column; gap: 1.5rem; }
    .taxes-actions { display: flex; justify-content: flex-end; margin-bottom: 1rem; }
    .btn { padding: 0.8rem 1.5rem; border-radius: var(--radius); border: none; font-weight: 600; cursor: pointer; transition: var(--transition); display: inline-flex; align-items: center; gap: 8px; font-size: 1rem; }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--secondary); box-shadow: var(--shadow-hover); }
    .taxes-layout { display: flex; gap: 2rem; align-items: flex-start; }
    .taxes-left { width: 100%; }
    .taxes-table-wrap { width: 100%; background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); padding: 1.35rem; }
    .taxes-table { width: 100%; border-collapse: collapse; font-size: 1.05rem; }
    .taxes-table th, .taxes-table td { border: 1px solid var(--border); padding: 0.65rem 0.8rem; text-align: left; }
    .taxes-table th { background: rgba(67, 97, 238, 0.08); color: var(--dark); font-weight: 700; }
    .taxes-table td { color: var(--dark); font-weight: 500; }
    .taxes-actions-cell a { color: var(--primary); text-decoration: none; font-weight: 600; }
    .taxes-actions-cell a:hover { color: var(--secondary); }
    .taxes-actions-cell a + span { color: var(--gray); }
    .tax-cell-name { font-weight: 600; color: var(--dark); }
    .tax-cell-percent { color: var(--dark); }
    .tax-rate-cell { display: inline-flex; align-items: center; gap: 10px; }
    .tax-rate-symbol {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 22px;
      height: 22px;
      border-radius: 6px;
      background: #eef3ff;
      color: #94a3b8;
      font-weight: 700;
      font-size: 0.85rem;
    }
    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.55);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      z-index: 1200;
    }
    .modal-backdrop.show { display: flex; }
    .modal-card {
      width: min(640px, 95vw);
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 24px 48px rgba(15, 23, 42, 0.3);
      padding: 1.6rem 1.8rem 1.8rem;
      position: relative;
    }
    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.2rem;
    }
    .modal-title {
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--primary);
    }
    .modal-close {
      background: transparent;
      border: none;
      font-size: 1.4rem;
      cursor: pointer;
      color: #64748b;
    }
    .modal-form {
      display: grid;
      gap: 1.1rem;
    }
    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      border: 0;
    }
    .modal-field label {
      display: block;
      font-size: 0.9rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      margin-bottom: 0.4rem;
    }
    .modal-field input,
    .modal-field select {
      width: 100%;
      padding: 0.8rem 0.95rem;
      border-radius: 10px;
      border: 1px solid transparent;
      border-bottom: 2px solid rgba(67, 97, 238, 0.9);
      font-size: 1rem;
      background: #eef3ff;
      color: #1f2937;
    }
    .modal-field input::placeholder {
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      font-weight: 600;
      font-size: 0.85rem;
    }
    .modal-field select.placeholder {
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      font-weight: 600;
    }
    .modal-field input:focus,
    .modal-field select:focus {
      outline: 2px solid rgba(67, 97, 238, 0.15);
      border-color: rgba(67, 97, 238, 0.6);
    }
    .input-with-icon {
      position: relative;
    }
    .input-with-icon .input-icon {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #9db0d1;
      font-weight: 700;
      font-size: 1.1rem;
      pointer-events: none;
    }
    .tax-type-toggle {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.9rem;
      margin-top: 0.35rem;
    }
    .tax-type-toggle.is-line .slider {
      background: var(--primary);
    }
    .tax-type-toggle .toggle-label {
      font-weight: 600;
      color: #94a3b8;
      cursor: pointer;
    }
    .tax-type-toggle .toggle-label.active {
      color: var(--primary);
    }
    .switch {
      position: relative;
      width: 58px;
      height: 28px;
    }
    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }
    .slider {
      position: absolute;
      cursor: pointer;
      inset: 0;
      background: #c7d2fe;
      border-radius: 999px;
      transition: 0.2s ease;
    }
    .slider::before {
      content: '';
      position: absolute;
      height: 22px;
      width: 22px;
      left: 3px;
      top: 3px;
      background: #fff;
      border-radius: 50%;
      transition: 0.2s ease;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
    }
    .switch input:checked + .slider {
      background: var(--primary);
    }
    .switch input:checked + .slider::before {
      transform: translateX(30px);
    }
    .tax-type-caption {
      text-align: center;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: #94a3b8;
      font-weight: 600;
      margin-top: 0.35rem;
    }
    .tax-calc-order-field.is-hidden {
      display: none;
    }
    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
      margin-top: 0.5rem;
    }
    .btn-secondary {
      background: #1f2937;
      color: #fff;
    }
    .btn-secondary:hover { background: #0f172a; }
    .modal-help {
      font-size: 0.85rem;
      color: #94a3b8;
    }
    @media (max-width: 768px) {
      .taxes-layout { flex-direction: column; }
      .taxes-left { width: 100%; }
    }
  </style>
</head>
<body>
  <?php require 'header.php'; ?>
  <div class="app-container">
    <?php require 'sidebar.php'; ?>
    <div class="main-content">
      <div class="page-header">
        <div class="page-header-block">
          <div class="page-kicker">
            <span class="material-symbols-outlined" aria-hidden="true">settings</span>
            SETTINGS
          </div>
          <div class="page-title-lg">Tax Class</div>
        </div>
      </div>

      <div class="taxes-body">
        <div class="taxes-layout">
          <div class="taxes-left">
            <div class="taxes-actions">
              <button type="button" class="btn btn-primary" id="newTaxBtn">New Tax Class</button>
            </div>
            <div class="taxes-table-wrap">
              <table class="taxes-table">
                <thead>
                  <tr>
                    <th>Tax Name</th>
                    <th>Tax Rate (%)</th>
                    <th>Tax Scope</th>
                    <th>Calculation Order</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($taxRows as $tax): ?>
                    <?php
                      $taxId = (int)$tax['id'];
                      $name = (string)$tax['name'];
                      $percentage = rtrim(rtrim(number_format((float)$tax['percentage'], 2, '.', ''), '0'), '.');
                      $taxTypeRaw = strtolower(trim((string)($tax['tax_type'] ?? 'line')));
                      $taxType = ($taxTypeRaw === 'invoice') ? 'invoice' : 'line';
                      $calcOrder = (int)($tax['calc_order'] ?? 1);
                      $taxScopeLabel = ($taxType === 'invoice') ? 'Total' : 'Line Item';
                      $calcLabelMap = [
                        1 => 'Tax A',
                        2 => 'Tax B',
                        3 => 'Adjusted Subtotal'
                      ];
                      $calcLabel = ($taxType === 'invoice') ? ($calcLabelMap[$calcOrder] ?? '') : '';
                    ?>
                    <tr data-tax-id="<?= $taxId ?>"
                        data-name="<?= htmlspecialchars($name) ?>"
                        data-percentage="<?= htmlspecialchars($percentage) ?>"
                        data-tax-type="<?= htmlspecialchars($taxType) ?>"
                        data-calc-order="<?= $calcOrder ?>">
                      <td class="tax-cell-name"><?= htmlspecialchars($name) ?></td>
                      <td class="tax-cell-percent">
                        <span class="tax-rate-cell">
                          <span class="tax-rate-symbol">%</span>
                          <span><?= htmlspecialchars($percentage) ?></span>
                        </span>
                      </td>
                      <td class="tax-cell-scope"><?= htmlspecialchars($taxScopeLabel) ?></td>
                      <td class="tax-cell-order"><?= htmlspecialchars($calcLabel) ?></td>
                      <td class="taxes-actions-cell">
                        <a href="#" data-action="edit">Edit</a> <span>/</span> <a href="#" data-action="delete">Delete</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-backdrop" id="taxClassModal">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="taxClassModalTitle">
      <div class="modal-header">
        <div class="modal-title" id="taxClassModalTitle">Create new tax class</div>
        <button type="button" class="modal-close" id="taxClassModalClose" aria-label="Close">&times;</button>
      </div>
      <form class="modal-form" id="taxClassForm">
        <input type="hidden" id="taxClassId" value="">
        <div class="modal-field">
          <label class="sr-only" for="taxClassName">Tax Class Name</label>
          <input type="text" id="taxClassName" name="name" placeholder="TAX CLASS NAME" required>
        </div>
        <div class="modal-field input-with-icon">
          <label class="sr-only" for="taxClassPercent">Tax Class Percentage</label>
          <input type="text" id="taxClassPercent" name="percentage" placeholder="TAX CLASS PERCENTAGE" inputmode="decimal" required>
          <span class="input-icon">%</span>
        </div>
        <div class="modal-field">
          <label class="sr-only" for="taxClassTypeToggle">Tax Class Type</label>
          <div class="tax-type-toggle">
            <span class="toggle-label" id="taxTypeLineLabel">Line-Level Tax</span>
            <label class="switch">
              <input type="checkbox" id="taxClassTypeToggle" aria-label="Tax class type">
              <span class="slider"></span>
            </label>
            <span class="toggle-label" id="taxTypeInvoiceLabel">Invoice-Level Tax</span>
          </div>
          <div class="tax-type-caption">Tax Class Type</div>
          <input type="hidden" id="taxClassType" name="tax_type" value="line">
        </div>
        <div class="modal-field tax-calc-order-field" id="taxCalcOrderField">
          <label class="sr-only" for="taxClassOrder">Tax Calculation Order</label>
          <select id="taxClassOrder" name="calc_order">
            <option value="" selected>TAX CALCULATION ORDER</option>
            <option value="1">Tax A - 20% - (Line item)</option>
            <option value="2">Tax B - 10% - (Total -> Tax A)</option>
            <option value="3">Adjusted Subtotal</option>
          </select>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" id="taxClassCancelBtn">Cancel</button>
          <button type="submit" class="btn btn-primary" id="taxClassSaveBtn">Create</button>
        </div>
      </form>
    </div>
  </div>
  <?php require 'scripts.php'; ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const newTaxBtn = document.getElementById('newTaxBtn');
      const tableBody = document.querySelector('.taxes-table tbody');
      const apiUrl = 'taxes_api.php';
      const modal = document.getElementById('taxClassModal');
      const modalTitle = document.getElementById('taxClassModalTitle');
      const modalClose = document.getElementById('taxClassModalClose');
      const modalCancel = document.getElementById('taxClassCancelBtn');
      const form = document.getElementById('taxClassForm');
      const nameInput = document.getElementById('taxClassName');
      const percentInput = document.getElementById('taxClassPercent');
      const idInput = document.getElementById('taxClassId');
      const saveBtn = document.getElementById('taxClassSaveBtn');
      const typeToggle = document.getElementById('taxClassTypeToggle');
      const typeInput = document.getElementById('taxClassType');
      const lineLabel = document.getElementById('taxTypeLineLabel');
      const invoiceLabel = document.getElementById('taxTypeInvoiceLabel');
      const orderSelect = document.getElementById('taxClassOrder');
      const calcOrderField = document.getElementById('taxCalcOrderField');
      const typeToggleWrap = document.querySelector('.tax-type-toggle');

      function buildActionCell(actions) {
        const cell = document.createElement('td');
        cell.className = 'taxes-actions-cell';
        actions.forEach((action, index) => {
          const link = document.createElement('a');
          link.href = '#';
          link.dataset.action = action;
          link.textContent = action.charAt(0).toUpperCase() + action.slice(1);
          cell.appendChild(link);
          if (index < actions.length - 1) {
            const sep = document.createElement('span');
            sep.textContent = ' / ';
            cell.appendChild(sep);
          }
        });
        return cell;
      }

      function createTextCell(text, className) {
        const cell = document.createElement('td');
        cell.className = className;
        cell.textContent = text || '';
        return cell;
      }

      function postData(payload) {
        return fetch(apiUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams(payload)
        }).then(res => res.json());
      }

      function setType(type) {
        const isInvoice = type === 'invoice';
        typeToggle.checked = isInvoice;
        typeInput.value = isInvoice ? 'invoice' : 'line';
        lineLabel.classList.toggle('active', !isInvoice);
        invoiceLabel.classList.toggle('active', isInvoice);
        if (typeToggleWrap) {
          typeToggleWrap.classList.toggle('is-line', !isInvoice);
          typeToggleWrap.classList.toggle('is-invoice', isInvoice);
        }
        if (calcOrderField) {
          calcOrderField.classList.toggle('is-hidden', !isInvoice);
        }
        if (orderSelect) {
          orderSelect.disabled = !isInvoice;
          if (!isInvoice) {
            if (!orderSelect.value) orderSelect.value = '1';
            orderSelect.classList.add('placeholder');
          } else if (orderSelect.value === '') {
            orderSelect.classList.add('placeholder');
          } else {
            orderSelect.classList.remove('placeholder');
          }
        }
      }

      function setModalOpen(isOpen) {
        if (isOpen) {
          modal.classList.add('show');
          nameInput.focus();
        } else {
          modal.classList.remove('show');
          form.reset();
          idInput.value = '';
          saveBtn.textContent = 'Create';
          setType('line');
          orderSelect.value = '';
          orderSelect.classList.add('placeholder');
        }
      }

      function openModal(mode, row) {
        if (mode === 'edit' && row) {
          const taxId = row.dataset.taxId || '';
          const name = row.dataset.name || '';
          const percentage = row.dataset.percentage || '';
          const taxType = row.dataset.taxType || 'line';
          const calcOrder = row.dataset.calcOrder || '1';
          idInput.value = taxId;
          nameInput.value = name;
          percentInput.value = percentage;
          modalTitle.textContent = 'Edit tax class';
          saveBtn.textContent = 'Save';
          setType(taxType === 'invoice' ? 'invoice' : 'line');
          orderSelect.value = calcOrder;
          orderSelect.classList.remove('placeholder');
        } else {
          modalTitle.textContent = 'Create new tax class';
          saveBtn.textContent = 'Create';
          setType('line');
          orderSelect.value = '';
          orderSelect.classList.add('placeholder');
        }
        setModalOpen(true);
      }

      function getScopeLabel(type) {
        return type === 'invoice' ? 'Total' : 'Line Item';
      }

      function getCalcLabel(order, type) {
        if (type !== 'invoice') return '';
        if (order === '1' || order === 1) return 'Tax A';
        if (order === '2' || order === 2) return 'Tax B';
        if (order === '3' || order === 3) return 'Adjusted Subtotal';
        return '';
      }

      function buildRateCell(percentage) {
        const cell = document.createElement('td');
        cell.className = 'tax-cell-percent';
        const wrap = document.createElement('span');
        wrap.className = 'tax-rate-cell';
        const sym = document.createElement('span');
        sym.className = 'tax-rate-symbol';
        sym.textContent = '%';
        const val = document.createElement('span');
        val.textContent = percentage;
        wrap.appendChild(sym);
        wrap.appendChild(val);
        cell.appendChild(wrap);
        return cell;
      }

      function buildRow(data) {
        const row = document.createElement('tr');
        row.dataset.taxId = data.id;
        row.dataset.name = data.name;
        row.dataset.percentage = data.percentage;
        row.dataset.taxType = data.taxType || 'line';
        row.dataset.calcOrder = data.calcOrder || '1';
        row.appendChild(createTextCell(data.name, 'tax-cell-name'));
        row.appendChild(buildRateCell(data.percentage));
        row.appendChild(createTextCell(getScopeLabel(row.dataset.taxType), 'tax-cell-scope'));
        row.appendChild(createTextCell(getCalcLabel(row.dataset.calcOrder, row.dataset.taxType), 'tax-cell-order'));
        row.appendChild(buildActionCell(['edit', 'delete']));
        return row;
      }

      function updateRow(row, data) {
        row.dataset.name = data.name;
        row.dataset.percentage = data.percentage;
        row.dataset.taxType = data.taxType || row.dataset.taxType || 'line';
        row.dataset.calcOrder = data.calcOrder || row.dataset.calcOrder || '1';
        row.children[0].textContent = data.name;
        const newRateCell = buildRateCell(data.percentage);
        row.replaceChild(newRateCell, row.children[1]);
        row.children[2].textContent = getScopeLabel(row.dataset.taxType);
        row.children[3].textContent = getCalcLabel(row.dataset.calcOrder, row.dataset.taxType);
      }

      newTaxBtn.addEventListener('click', () => openModal('create'));
      modalClose.addEventListener('click', () => setModalOpen(false));
      modalCancel.addEventListener('click', () => setModalOpen(false));
      orderSelect.addEventListener('change', () => {
        if (orderSelect.value === '') {
          orderSelect.classList.add('placeholder');
        } else {
          orderSelect.classList.remove('placeholder');
        }
      });
      typeToggle.addEventListener('change', () => {
        setType(typeToggle.checked ? 'invoice' : 'line');
      });
      lineLabel.addEventListener('click', () => setType('line'));
      invoiceLabel.addEventListener('click', () => setType('invoice'));
      modal.addEventListener('click', (event) => {
        if (event.target === modal) {
          setModalOpen(false);
        }
      });
      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('show')) {
          setModalOpen(false);
        }
      });

      tableBody.addEventListener('click', (event) => {
        const actionLink = event.target.closest('a[data-action]');
        if (!actionLink) return;
        event.preventDefault();

        const action = actionLink.dataset.action;
        const row = actionLink.closest('tr');
        if (!row) return;

        if (action === 'edit') {
          openModal('edit', row);
          return;
        }

        if (action === 'delete') {
          const taxId = row.dataset.taxId;
          if (!taxId) {
            row.remove();
            return;
          }
          postData({ action: 'delete', id: taxId })
            .then(result => {
              if (!result.success) {
                alert(result.message || 'Failed to delete tax.');
                return;
              }
              row.remove();
            })
            .catch(() => alert('Failed to delete tax.'));
          return;
        }
      });

      form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const name = nameInput.value.trim();
        const percentRaw = percentInput.value.trim();
        const percentValue = parseFloat(percentRaw);
        const taxType = typeInput.value === 'invoice' ? 'invoice' : 'line';
        const calcOrder = orderSelect.value || '1';

        if (!name || Number.isNaN(percentValue)) {
          alert('Please enter a tax class name and valid percentage.');
          return;
        }

        const normalized = Number.isInteger(percentValue) ? percentValue.toFixed(0) : percentValue.toString();
        const existingId = idInput.value.trim();

        saveBtn.disabled = true;
        try {
          if (existingId) {
            const result = await postData({
              action: 'update',
              id: existingId,
              name,
              percentage: normalized,
              tax_type: taxType,
              calc_order: calcOrder
            });
            if (!result.success) {
              alert(result.message || 'Failed to update tax class.');
              return;
            }
            const row = tableBody.querySelector(`tr[data-tax-id="${existingId}"]`);
            if (row) {
              updateRow(row, { name, percentage: normalized, taxType, calcOrder });
            }
          } else {
            const result = await postData({
              action: 'create',
              name,
              percentage: normalized,
              tax_type: taxType,
              calc_order: calcOrder
            });
            if (!result.success || !result.id) {
              alert(result.message || 'Failed to create tax class.');
              return;
            }
            const row = buildRow({
              id: result.id,
              name: result.name || name,
              percentage: result.percentage !== undefined ? result.percentage : normalized,
              taxType: result.tax_type || taxType,
              calcOrder: result.calc_order || calcOrder
            });
            tableBody.appendChild(row);
          }
          setModalOpen(false);
        } finally {
          saveBtn.disabled = false;
        }
      });

      setType('line');
      orderSelect.classList.add('placeholder');
    });
  </script>
</body>
</html>
