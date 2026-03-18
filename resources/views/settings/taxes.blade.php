@extends('layouts.app')

@php
  $activeMenu = 'settings';
  $activeTab = 'taxes';
@endphp

@section('title', 'Settings - Tax Classes')

@push('styles')
<style>
  .tax-shell {
    max-width: 1200px;
    margin: 0 auto;
  }

  .tax-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
  }

  .tax-toolbar h2 {
    margin: 0;
    color: var(--dark);
  }

  .tax-table td.actions {
    min-width: 190px;
  }

  .tax-action-group {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  .tax-modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }

  .tax-modal-close {
    background: transparent;
    border: none;
    color: var(--gray);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .tax-modal-close:hover {
    color: var(--danger);
  }

  .tax-inline-alert {
    margin-bottom: 1rem;
  }

  .tax-cancel-btn {
    background: var(--gray);
    color: white;
  }

  .tax-cancel-btn:hover {
    filter: brightness(0.92);
  }

  @media (max-width: 768px) {
    .tax-toolbar {
      flex-direction: column;
      align-items: flex-start;
    }

    .tax-action-group {
      width: 100%;
    }

    .tax-action-group .btn {
      flex: 1;
    }
  }
</style>
@endpush

@section('content')
  <div class="tax-shell">
    <div class="page-header">
      <h1 class="page-title">Tax Classes</h1>
      <p class="page-subtitle">Manage tax classes for invoices.</p>
    </div>

    <div id="alert-container" class="tax-inline-alert"></div>

    <div class="card">
      <div class="tax-toolbar">
        <h2>Tax Classes</h2>
        <button class="btn btn-primary" type="button" onclick="openModal()">
          <span class="material-icons-outlined">add</span> Add Tax Class
        </button>
      </div>

      <div class="table-container">
        <table class="tax-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Percentage</th>
              <th>Tax Scope</th>
              <th>Calculation Order</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="taxes-table-body">
            @foreach($taxes as $tax)
            @php
              $scopeLabel = $tax->tax_type === 'invoice' ? 'Total' : 'Line Item';
              $calcLabel = '';
              if ($tax->tax_type === 'invoice') {
                  $calcLabel = match ((int) $tax->calc_order) {
                      1 => 'Tax A',
                      2 => 'Tax B',
                      3 => 'Adjusted Subtotal',
                      default => 'Order ' . (int) $tax->calc_order,
                  };
              }
            @endphp
            <tr data-id="{{ $tax->id }}">
              <td class="tax-cell-name">{{ $tax->name }}</td>
              <td class="tax-cell-percent">{{ number_format($tax->percentage, 2) }}%</td>
              <td>{{ $scopeLabel }}</td>
              <td>{{ $calcLabel }}</td>
              <td class="actions">
                <div class="tax-action-group">
                  <button class="btn btn-primary btn-sm" type="button" onclick="editTax({{ $tax->id }}, @js($tax->name), {{ (float) $tax->percentage }}, @js($tax->tax_type), {{ (int) $tax->calc_order }})">
                    <span class="material-icons-outlined">edit</span> Edit
                  </button>
                  <button class="btn btn-danger btn-sm" type="button" onclick="deleteTax({{ $tax->id }})">
                    <span class="material-icons-outlined">delete</span> Delete
                  </button>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="taxModal" aria-hidden="true">
    <div class="modal-card">
      <div class="tax-modal-head">
        <h3 class="modal-title" id="modalTitle">Add Tax Class</h3>
        <button class="tax-modal-close" type="button" onclick="closeModal()" aria-label="Close tax modal">
          <span class="material-icons-outlined">close</span>
        </button>
      </div>
      <form id="taxForm" onsubmit="saveTax(event)">
        <input type="hidden" id="taxId" name="id">
        <div class="form-group">
          <label for="taxName">Name *</label>
          <input type="text" id="taxName" name="name" class="form-control" required maxlength="100">
        </div>
        <div class="form-group">
          <label for="taxPercentage">Percentage *</label>
          <input type="number" id="taxPercentage" name="percentage" class="form-control" step="0.01" min="0" max="100" required>
        </div>
        <div class="form-group">
          <label for="taxType">Tax Scope *</label>
          <select id="taxType" name="tax_type" class="form-control" required onchange="toggleCalcOrder()">
            <option value="line">Line Item</option>
            <option value="invoice">Total</option>
          </select>
        </div>
        <div class="form-group is-hidden" id="calcOrderGroup">
          <label for="taxCalcOrder">Calculation Order</label>
          <select id="taxCalcOrder" name="calc_order" class="form-control">
            <option value="1">Tax A</option>
            <option value="2">Tax B</option>
            <option value="3">Adjusted Subtotal</option>
          </select>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn tax-cancel-btn" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <span class="material-icons-outlined">save</span> Save
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  function showAlert(message, type = 'success') {
    const container = document.getElementById('alert-container');
    const cls = type === 'danger' ? 'inline-alert inline-alert-danger' : 'inline-alert inline-alert-success';
    container.innerHTML = `<div class="${cls}">${message}</div>`;
    setTimeout(() => {
      container.innerHTML = '';
    }, 5000);
  }

  function openModal(tax = null) {
    const modal = document.getElementById('taxModal');
    modal.classList.add('show');
    modal.setAttribute('aria-hidden', 'false');

    if (tax) {
      document.getElementById('modalTitle').textContent = 'Edit Tax Class';
      document.getElementById('taxId').value = tax.id;
      document.getElementById('taxName').value = tax.name;
      document.getElementById('taxPercentage').value = tax.percentage;
      document.getElementById('taxType').value = tax.tax_type;
      document.getElementById('taxCalcOrder').value = tax.calc_order;
      toggleCalcOrder();
      return;
    }

    document.getElementById('modalTitle').textContent = 'Add Tax Class';
    document.getElementById('taxForm').reset();
    document.getElementById('taxId').value = '';
    toggleCalcOrder();
  }

  function closeModal() {
    const modal = document.getElementById('taxModal');
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    document.getElementById('taxForm').reset();
  }

  function toggleCalcOrder() {
    const taxType = document.getElementById('taxType').value;
    const calcOrderInput = document.getElementById('taxCalcOrder');
    const calcOrderGroup = document.getElementById('calcOrderGroup');
    const isInvoice = taxType === 'invoice';

    calcOrderGroup.classList.toggle('is-hidden', !isInvoice);
    calcOrderInput.disabled = !isInvoice;

    if (!isInvoice || !calcOrderInput.value) {
      calcOrderInput.value = '1';
    }
  }

  function editTax(id, name, percentage, taxType, calcOrder) {
    openModal({ id, name, percentage, tax_type: taxType, calc_order: calcOrder });
  }

  async function saveTax(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);

    const action = data.id ? 'update' : 'create';
    data.action = action;
    if (data.tax_type !== 'invoice') {
      data.calc_order = '1';
    } else if (!data.calc_order) {
      data.calc_order = '1';
    }

    try {
      const response = await fetch('{{ route("api.taxes") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (result.success) {
        showAlert(action === 'create' ? 'Tax class created successfully!' : 'Tax class updated successfully!');
        closeModal();
        setTimeout(() => location.reload(), 1000);
      } else {
        showAlert(result.message || 'An error occurred', 'danger');
      }
    } catch (error) {
      showAlert('An error occurred: ' + error.message, 'danger');
    }
  }

  async function deleteTax(id) {
    if (!confirm('Are you sure you want to delete this tax class?')) {
      return;
    }

    try {
      const response = await fetch('{{ route("api.taxes") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ action: 'delete', id })
      });

      const result = await response.json();

      if (result.success) {
        showAlert('Tax class deleted successfully!');
        setTimeout(() => location.reload(), 1000);
      } else {
        showAlert(result.message || 'An error occurred', 'danger');
      }
    } catch (error) {
      showAlert('An error occurred: ' + error.message, 'danger');
    }
  }

  document.getElementById('taxModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeModal();
    }
  });

  toggleCalcOrder();
</script>
@endpush
