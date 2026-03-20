<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startPush('styles'); ?>
<style>
  .dash-shell {
    display: grid;
    gap: 1.25rem;
  }

  .dash-hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 1rem;
    padding: 1.5rem;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(67, 97, 238, 0.16), rgba(72, 149, 239, 0.08));
    border: 1px solid rgba(67, 97, 238, 0.2);
  }

  .dash-heading {
    margin: 0;
    font-size: 1.75rem;
    color: var(--primary);
  }

  .dash-subheading {
    margin: 0.35rem 0 0;
    color: var(--gray);
    font-size: 0.95rem;
  }

  .dash-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
  }

  .dash-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
  }

  .dash-kpi {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1rem;
    display: grid;
    gap: 0.55rem;
  }

  .dash-kpi-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: var(--gray);
    font-size: 0.9rem;
    font-weight: 600;
  }

  .dash-kpi-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .dash-kpi-icon.success {
    background: rgba(76, 201, 240, 0.18);
    color: var(--success);
  }

  .dash-kpi-icon.danger {
    background: rgba(247, 37, 133, 0.16);
    color: var(--danger);
  }

  .dash-kpi-icon.neutral {
    background: rgba(67, 97, 238, 0.12);
    color: var(--primary);
  }

  .dash-kpi-value {
    margin: 0;
    font-size: 1.75rem;
    line-height: 1;
    font-weight: 700;
    color: var(--dark);
  }

  .dash-kpi-note {
    margin: 0;
    color: var(--gray);
    font-size: 0.85rem;
  }

  .dash-grid-2 {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
    gap: 1rem;
  }

  .dash-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1rem;
    box-shadow: var(--shadow);
  }

  .dash-card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.8rem;
    margin-bottom: 0.8rem;
  }

  .dash-card-title {
    margin: 0;
    font-size: 1.05rem;
    color: var(--primary);
  }

  .dash-card-subtitle {
    margin: 0.2rem 0 0;
    font-size: 0.85rem;
    color: var(--gray);
  }

  .dash-period {
    min-width: 150px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--card-bg);
    color: var(--dark);
    padding: 0.45rem 0.55rem;
  }

  .dash-chart-wrap {
    position: relative;
    height: 300px;
  }

  .dash-table {
    width: 100%;
    border-collapse: collapse;
  }

  .dash-table th,
  .dash-table td {
    padding: 0.8rem 0.7rem;
    border-bottom: 1px solid var(--border);
    text-align: left;
  }

  .dash-table th {
    font-size: 0.85rem;
    color: var(--primary);
    background: rgba(67, 97, 238, 0.08);
    user-select: none;
    cursor: pointer;
    white-space: nowrap;
  }

  .dash-table tbody tr:hover {
    background: rgba(67, 97, 238, 0.04);
  }

  .dash-sort-indicator {
    color: var(--gray);
    margin-left: 0.3rem;
  }

  .dash-status {
    display: inline-flex;
    padding: 0.25rem 0.65rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
  }

  .dash-status-paid {
    background: rgba(76, 201, 240, 0.18);
    color: var(--success);
  }

  .dash-status-unpaid {
    background: rgba(247, 37, 133, 0.16);
    color: var(--danger);
  }

  .dash-empty {
    text-align: center;
    color: var(--gray);
    padding: 1rem;
  }

  .dash-grid-2-equal {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
  }

  @media (max-width: 1120px) {
    .dash-grid-4 {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .dash-grid-2 {
      grid-template-columns: 1fr;
    }
  }

  @media (max-width: 768px) {
    .dash-hero {
      flex-direction: column;
      align-items: flex-start;
    }

    .dash-grid-2-equal,
    .dash-grid-4 {
      grid-template-columns: 1fr;
    }

    .dash-chart-wrap {
      height: 260px;
    }
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="dash-shell">
  <section class="dash-hero">
    <div>
      <h1 class="dash-heading">Dashboard Overview</h1>
      <p class="dash-subheading">Track revenue, invoice health, and client activity in one view.</p>
    </div>
    <div class="dash-actions">
      <a href="<?php echo e(route('invoices.create')); ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        New Invoice
      </a>
      <a href="<?php echo e(route('invoices.index')); ?>" class="btn btn-outline">
        <i class="fas fa-file-invoice"></i>
        View Invoices
      </a>
    </div>
  </section>

  <section class="dash-grid-4">
    <article class="dash-kpi">
      <div class="dash-kpi-top">
        <span>Total Revenue</span>
        <span class="dash-kpi-icon success"><i class="fas fa-arrow-trend-up"></i></span>
      </div>
      <p class="dash-kpi-value" id="totalRevenue">$0.00</p>
      <p class="dash-kpi-note">From paid invoices</p>
    </article>

    <article class="dash-kpi">
      <div class="dash-kpi-top">
        <span>Total Deficit</span>
        <span class="dash-kpi-icon danger"><i class="fas fa-arrow-trend-down"></i></span>
      </div>
      <p class="dash-kpi-value" id="totalDeficit">$0.00</p>
      <p class="dash-kpi-note">From unpaid invoices</p>
    </article>

    <article class="dash-kpi">
      <div class="dash-kpi-top">
        <span>Paid Invoices</span>
        <span class="dash-kpi-icon neutral"><i class="fas fa-circle-check"></i></span>
      </div>
      <p class="dash-kpi-value" id="totalPaid">0</p>
      <p class="dash-kpi-note">Total paid records</p>
    </article>

    <article class="dash-kpi">
      <div class="dash-kpi-top">
        <span>Unpaid Invoices</span>
        <span class="dash-kpi-icon neutral"><i class="fas fa-clock"></i></span>
      </div>
      <p class="dash-kpi-value" id="totalUnpaid">0</p>
      <p class="dash-kpi-note">Outstanding invoices</p>
    </article>
  </section>

  <section class="dash-grid-2">
    <article class="dash-card">
      <div class="dash-card-head">
        <div>
          <h2 class="dash-card-title">Invoice Status</h2>
          <p class="dash-card-subtitle">Paid vs unpaid split</p>
        </div>
      </div>
      <div class="dash-chart-wrap">
        <canvas id="statusChart"></canvas>
      </div>
    </article>

    <article class="dash-card">
      <div class="dash-card-head">
        <div>
          <h2 class="dash-card-title">Invoice Activity</h2>
          <p class="dash-card-subtitle">Compare paid and unpaid trends</p>
        </div>
        <select id="chartPeriod" class="dash-period">
          <option value="daily">Last 7 Days</option>
          <option value="monthly">Last 6 Months</option>
          <option value="yearly">Last 5 Years</option>
          <option value="all">All Time</option>
        </select>
      </div>
      <div class="dash-chart-wrap">
        <canvas id="timeChart"></canvas>
      </div>
    </article>
  </section>

  <section class="dash-grid-2-equal">
    <article class="dash-card">
      <div class="dash-card-head">
        <div>
          <h2 class="dash-card-title">Top Clients (Paid)</h2>
          <p class="dash-card-subtitle">Clients with most closed invoices</p>
        </div>
      </div>
      <div class="table-container">
        <table id="paidClientsTable" class="dash-table">
          <thead>
            <tr>
              <th onclick="sortTable('paidClientsTable', 0)">Client <span class="dash-sort-indicator">v</span></th>
              <th onclick="sortTable('paidClientsTable', 1, 'number')">Invoices <span class="dash-sort-indicator">v</span></th>
            </tr>
          </thead>
          <tbody id="paidClientsBody">
            <tr><td colspan="2" class="dash-empty">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </article>

    <article class="dash-card">
      <div class="dash-card-head">
        <div>
          <h2 class="dash-card-title">Top Clients (Unpaid)</h2>
          <p class="dash-card-subtitle">Clients requiring follow-up</p>
        </div>
      </div>
      <div class="table-container">
        <table id="unpaidClientsTable" class="dash-table">
          <thead>
            <tr>
              <th onclick="sortTable('unpaidClientsTable', 0)">Client <span class="dash-sort-indicator">v</span></th>
              <th onclick="sortTable('unpaidClientsTable', 1, 'number')">Unpaid <span class="dash-sort-indicator">v</span></th>
            </tr>
          </thead>
          <tbody id="unpaidClientsBody">
            <tr><td colspan="2" class="dash-empty">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </article>
  </section>

  <section class="dash-card">
    <div class="dash-card-head">
      <div>
        <h2 class="dash-card-title">Recent Invoices</h2>
        <p class="dash-card-subtitle">Most recently created invoice records</p>
      </div>
      <a href="<?php echo e(route('invoices.index')); ?>" class="btn btn-outline">View All</a>
    </div>
    <div class="table-container">
      <table id="recentTable" class="dash-table">
        <thead>
          <tr>
            <th onclick="sortTable('recentTable', 0)">Invoice <span class="dash-sort-indicator">v</span></th>
            <th onclick="sortTable('recentTable', 1)">Client <span class="dash-sort-indicator">v</span></th>
            <th onclick="sortTable('recentTable', 2, 'currency')">Amount <span class="dash-sort-indicator">v</span></th>
            <th onclick="sortTable('recentTable', 3)">Status <span class="dash-sort-indicator">v</span></th>
            <th onclick="sortTable('recentTable', 4, 'date')">Date <span class="dash-sort-indicator">v</span></th>
          </tr>
        </thead>
        <tbody id="recentInvoicesBody">
          <tr><td colspan="5" class="dash-empty">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  if (localStorage.getItem('darkMode') === '1') {
    document.documentElement.classList.add('dark-mode');
  }

  let statusChart;
  let timeChart;
  const currencySymbol = '<?php echo e(setting("currency_symbol") ?: "$"); ?>';

  function renderStatusChart(data) {
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    if (statusChart) {
      statusChart.destroy();
    }

    statusChart = new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: ['Paid', 'Unpaid'],
        datasets: [{
          data: [data.status.paid, data.status.unpaid],
          backgroundColor: ['#4cc9f0', '#f72585'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
          legend: {
            position: 'bottom'
          },
          tooltip: {
            callbacks: {
              label(context) {
                return context.label + ': ' + context.raw + ' invoices';
              }
            }
          }
        }
      }
    });
  }

  function renderTrendChart(data) {
    const trendCtx = document.getElementById('timeChart').getContext('2d');
    if (timeChart) {
      timeChart.destroy();
    }

    timeChart = new Chart(trendCtx, {
      type: 'bar',
      data: {
        labels: data.labels.length ? data.labels : ['No Data'],
        datasets: [
          {
            label: 'Paid',
            data: data.paid_series.length ? data.paid_series : [0],
            backgroundColor: '#4cc9f0',
            borderRadius: 6
          },
          {
            label: 'Unpaid',
            data: data.unpaid_series.length ? data.unpaid_series : [0],
            backgroundColor: '#f72585',
            borderRadius: 6
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top'
          }
        },
        scales: {
          x: {
            grid: { display: false }
          },
          y: {
            beginAtZero: true,
            ticks: { precision: 0 }
          }
        }
      }
    });
  }

  function loadDashboard(period = 'daily') {
    fetch(`<?php echo e(route('api.dashboard.data')); ?>?period=${period}&_=${Date.now()}`)
      .then(response => response.json())
      .then(data => {
        renderStatusChart(data);
        renderTrendChart(data);
      })
      .catch(error => {
        console.error('Dashboard fetch error:', error);
      });

    fetch(`<?php echo e(route('api.dashboard.data')); ?>?period=all&_=${Date.now()}`)
      .then(response => response.json())
      .then(summary => {
        document.getElementById('totalPaid').textContent = summary.status.paid;
        document.getElementById('totalUnpaid').textContent = summary.status.unpaid;
      })
      .catch(error => {
        console.error('Dashboard summary counts error:', error);
      });
  }

  function loadSummary() {
    fetch('<?php echo e(route("api.dashboard.summary")); ?>')
      .then(response => response.json())
      .then(summary => {
        document.getElementById('totalRevenue').textContent = currencySymbol + parseFloat(summary.total_revenue).toFixed(2);
        document.getElementById('totalDeficit').textContent = currencySymbol + parseFloat(summary.total_deficit).toFixed(2);

        const recentInvoicesBody = document.getElementById('recentInvoicesBody');
        recentInvoicesBody.innerHTML = '';

        if (!summary.recent_invoices || summary.recent_invoices.length === 0) {
          recentInvoicesBody.innerHTML = '<tr><td colspan="5" class="dash-empty">No invoices found</td></tr>';
          return;
        }

        summary.recent_invoices.forEach(invoice => {
          const row = document.createElement('tr');
          const amount = parseFloat(invoice.total_amount || 0).toFixed(2);
          const statusClass = invoice.status === 'Paid' ? 'dash-status-paid' : 'dash-status-unpaid';

          row.innerHTML = `
            <td>${invoice.invoice_number}</td>
            <td>${invoice.bill_to_name}</td>
            <td>${currencySymbol}${amount}</td>
            <td><span class="dash-status ${statusClass}">${invoice.status}</span></td>
            <td>${invoice.created_at}</td>
          `;

          recentInvoicesBody.appendChild(row);
        });
      })
      .catch(error => {
        console.error('Summary load error:', error);
      });
  }

  function renderClientRows(tableBodyId, rows, valueKey, emptyMessage) {
    const tableBody = document.getElementById(tableBodyId);
    tableBody.innerHTML = '';

    if (!rows || rows.length === 0) {
      tableBody.innerHTML = `<tr><td colspan="2" class="dash-empty">${emptyMessage}</td></tr>`;
      return;
    }

    rows.forEach(client => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${client.bill_to_name}</td>
        <td>${client[valueKey]}</td>
      `;
      tableBody.appendChild(row);
    });
  }

  function loadTopClients() {
    fetch('<?php echo e(route("api.dashboard.data")); ?>?paid_clients=true')
      .then(response => response.json())
      .then(data => {
        renderClientRows('paidClientsBody', data.top_clients, 'total', 'No paid clients found');
      })
      .catch(error => {
        console.error('Paid clients load error:', error);
      });

    fetch('<?php echo e(route("api.dashboard.data")); ?>?unpaid_clients=true')
      .then(response => response.json())
      .then(data => {
        renderClientRows('unpaidClientsBody', data.top_unpaid, 'count', 'No unpaid clients found');
      })
      .catch(error => {
        console.error('Unpaid clients load error:', error);
      });
  }

  function sortTable(tableId, columnIndex, dataType = 'text') {
    const table = document.getElementById(tableId);
    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows);

    if (rows.length < 2) {
      return;
    }

    const currentOrder = table.getAttribute('data-sort-order') || 'asc';
    const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

    table.setAttribute('data-sort-order', newOrder);
    table.setAttribute('data-sort-column', columnIndex);

    rows.sort((rowA, rowB) => {
      let valueA = rowA.cells[columnIndex].textContent.trim();
      let valueB = rowB.cells[columnIndex].textContent.trim();

      if (dataType === 'number') {
        valueA = parseInt(valueA, 10) || 0;
        valueB = parseInt(valueB, 10) || 0;
      } else if (dataType === 'currency') {
        valueA = parseFloat(valueA.replace(/[^\d.]/g, '')) || 0;
        valueB = parseFloat(valueB.replace(/[^\d.]/g, '')) || 0;
      } else if (dataType === 'date') {
        valueA = new Date(valueA).getTime() || 0;
        valueB = new Date(valueB).getTime() || 0;
      }

      if (valueA < valueB) {
        return newOrder === 'asc' ? -1 : 1;
      }

      if (valueA > valueB) {
        return newOrder === 'asc' ? 1 : -1;
      }

      return 0;
    });

    rows.forEach(row => {
      tbody.appendChild(row);
    });

    table.querySelectorAll('.dash-sort-indicator').forEach(indicator => {
      indicator.textContent = 'v';
    });

    const header = table.rows[0].cells[columnIndex];
    const indicator = header.querySelector('.dash-sort-indicator');
    if (indicator) {
      indicator.textContent = newOrder === 'asc' ? '^' : 'v';
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
    loadSummary();
    loadTopClients();

    const periodSelect = document.getElementById('chartPeriod');
    if (periodSelect) {
      periodSelect.addEventListener('change', function onChange() {
        loadDashboard(this.value);
      });
    }
  });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/dashboard/index.blade.php ENDPATH**/ ?>