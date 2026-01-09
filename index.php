<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  // ✅ Show landing page without changing URL to /home.php
  define('DOCUBILLS_LANDING', true);
  require __DIR__ . '/homelandingpage6.php';
  exit;
}

$activeMenu = 'dashboard';
$activeTab = '';
$activeSub = '';

require_once 'config.php';
require_once 'middleware.php'; // ✅ Add this

// ✅ Deny access if permission is missing
if (!has_permission('view_dashboard')) {
  $_SESSION['access_denied'] = true;
  header("Location: access-denied.php");
  exit;
}

require 'styles.php'; // Keep this after all middleware/config checks
require 'header.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Professional Invoice Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
      // Add dark-mode class early before CSS is evaluated
      if (localStorage.getItem('darkMode') === '1') {
        document.documentElement.classList.add('dark-mode'); // Apply to <html> early
      }
  </script>
  <style>
    /* All CSS styles remain the same as before */
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

        html.dark-mode {
      --primary: #5a7dff;
      --primary-light: #6e8fff;
      --secondary: #4d45d1;
      --success: #5ed5f9;
      --danger: #ff3d96;
      --warning: #ffaa45;
      --dark: #e9ecef;
      --light: #212529;
      --gray: #adb5bd;
      --border: #495057;
      --card-bg: #2d3748;
      --body-bg: #1a202c;
      --sidebar-bg: #1e293b;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
      --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.3);
    }


    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: var(--body-bg);
      color: var(--dark);
      transition: var(--transition);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .app-container {
      display: flex;
      min-height: 100vh;
    }

    /* Header Styles */
    .header {
      background: var(--primary);
      color: white;
      padding: 0 1.5rem;
      height: var(--header-height);
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: var(--shadow);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 1.5rem;
      font-weight: 700;
    }

    .logo i {
      font-size: 1.8rem;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .theme-toggle {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      color: white;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }

    .theme-toggle:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .user-profile {
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--primary-light);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 1.2rem;
    }

    /* Sidebar Styles */
    .sidebar {
      width: var(--sidebar-width);
      background: var(--sidebar-bg);
      color: white;
      height: calc(100vh - var(--header-height));
      position: fixed;
      top: var(--header-height);
      left: 0;
      overflow-y: auto;
      transition: var(--transition);
      z-index: 90;
    }

    .sidebar-menu {
      padding: 1.5rem 0;
    }

    .menu-item {
      padding: 0.8rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 12px;
      cursor: pointer;
      transition: var(--transition);
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      font-weight: 500;
    }

    .menu-item:hover, .menu-item.active {
      background: rgba(255, 255, 255, 0.1);
      color: white;
      border-left: 4px solid var(--primary-light);
    }

    .menu-item i {
      width: 24px;
      text-align: center;
    }

    /* Main Content Styles */
    .main-content {
      flex: 1;
      margin-left: var(--sidebar-width);
      padding: calc(var(--header-height) + 1.5rem) 1.5rem 1.5rem;
      transition: var(--transition);
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }

    .page-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
    }

    .page-actions {
      display: flex;
      gap: 15px;
    }

    .btn {
      padding: 0.6rem 1.2rem;
      border-radius: var(--radius);
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--secondary);
      box-shadow: var(--shadow-hover);
    }

    .btn-outline {
      background: transparent;
      border: 1px solid var(--primary);
      color: var(--primary);
    }

    .btn-outline:hover {
      background: var(--primary);
      color: white;
    }

    /* Dashboard Grid */
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    /* Card Styles */
    .card {
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      transition: var(--transition);
      overflow: hidden;
      padding: 1.5rem;
    }

    .card:hover {
      box-shadow: var(--shadow-hover);
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.2rem;
    }

    .card-title {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--primary);
    }

    .card-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }

    .icon-revenue {
      background: rgba(76, 201, 240, 0.2);
      color: var(--success);
    }

    .icon-deficit {
      background: rgba(247, 37, 133, 0.2);
      color: var(--danger);
    }

    .card-value {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .card-label {
      color: var(--gray);
      font-size: 0.9rem;
    }

    .positive {
      color: var(--success);
    }

    .negative {
      color: var(--danger);
    }

    /* Chart Containers */
    .chart-container {
      display: grid;
      grid-template-columns: 1fr 2fr;
      gap: 1.5rem;
      margin-bottom: 1.5rem;
    }

    @media (max-width: 992px) {
      .chart-container {
        grid-template-columns: 1fr;
      }
    }

    .chart-card {
      padding: 1.5rem;
      min-height: 400px;
      display: flex;
      flex-direction: column;
    }

    .chart-header {
      margin-bottom: 1.2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .chart-title {
      font-size: 1.2rem;
      font-weight: 600;
      color: var(--primary);
    }

    .chart-controls {
      display: flex;
      gap: 10px;
    }

    .chart-select {
      padding: 0.4rem 0.8rem;
      border-radius: var(--radius);
      border: 1px solid var(--border);
      background: var(--card-bg);
      color: var(--dark);
      font-size: 0.9rem;
    }

    .chart-canvas-container {
      flex: 1;
      position: relative;
    }

    .chart-canvas {
      width: 100%;
      height: 100%;
    }

    /* Table Styles */
    .table-container {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: var(--radius);
      overflow: hidden;
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
      cursor: pointer;
      user-select: none;
      position: relative;
    }

    th:hover {
      background: rgba(67, 97, 238, 0.15);
    }

    .sort-arrow {
      margin-left: 6px;
      font-size: 0.8rem;
      color: var(--gray);
    }

    tbody tr {
      transition: var(--transition);
    }

    tbody tr:hover {
      background: rgba(67, 97, 238, 0.05);
    }

    .status-badge {
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
    }

    .status-paid {
      background: rgba(76, 201, 240, 0.2);
      color: var(--success);
    }

    .status-unpaid {
      background: rgba(247, 37, 133, 0.2);
      color: var(--danger);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        width: 70px;
      }
      
      .sidebar .menu-text {
        display: none;
      }
      
      .main-content {
        margin-left: 70px;
      }
      
      .dashboard-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 576px) {
      .header {
        padding: 0 1rem;
      }
      
      .user-name {
        display: none;
      }
      
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
      
      .chart-container {
        grid-template-columns: 1fr;
      }
    
    .profile-menu {
      position: absolute;
      top: calc(var(--header-height) + 10px);
      right: 20px;
      background: var(--card-bg);
      box-shadow: var(--shadow);
      border-radius: var(--radius);
      display: none;
      flex-direction: column;
      min-width: 180px;
      z-index: 9999;
    }
    
    .profile-menu a {
      padding: 12px 16px;
      color: var(--dark);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 500;
    }
    
    .profile-menu a:hover {
      background: rgba(0, 0, 0, 0.05);
    }
  </style>
</head>
<body>
  <div class="app-container">
    <!-- Sidebar -->
    <?php require 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
      <div class="page-header">
        <h1 class="page-title">Dashboard Overview</h1>
        <div class="page-actions">
          <button class="btn btn-outline">
            <i class="fas fa-download"></i> Export Report
          </button>
          <button class="btn btn-primary" onclick="window.location.href='create-invoice.php'">
            <i class="fas fa-plus"></i> New Invoice
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="dashboard-grid">
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Total Revenue</h2>
            <div class="card-icon icon-revenue">
              <i class="fas fa-money-bill-wave"></i>
            </div>
          </div>
          <div class="card-value positive" id="totalRevenue">CA$ 0.00</div>
          <div class="card-label">From paid invoices</div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Total Deficit</h2>
            <div class="card-icon icon-deficit">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
          </div>
          <div class="card-value negative" id="totalDeficit">CA$ 0.00</div>
          <div class="card-label">From unpaid invoices</div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Paid Invoices</h2>
            <div class="card-icon">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
          <div class="card-value" id="totalPaid">0</div>
          <div class="card-label">+0% from last month</div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Unpaid Invoices</h2>
            <div class="card-icon">
              <i class="fas fa-clock"></i>
            </div>
          </div>
          <div class="card-value" id="totalUnpaid">0</div>
          <div class="card-label">+0% from last month</div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="chart-container">
        <div class="card chart-card">
          <div class="chart-header">
            <h2 class="chart-title">Invoice Status</h2>
          </div>
          <div class="chart-canvas-container">
            <canvas id="statusChart" class="chart-canvas"></canvas>
          </div>
        </div>

        <div class="card chart-card">
          <div class="chart-header">
            <h2 class="chart-title">Invoice Activity</h2>
            <div class="chart-controls">
              <select id="chartPeriod" class="chart-select">
                <option value="daily">Last 7 Days</option>
                <option value="monthly">Last 6 Months</option>
                <option value="yearly">Last 5 Years</option>
                <option value="all">All Time</option>
              </select>
            </div>
          </div>
          <div class="chart-canvas-container">
            <canvas id="timeChart" class="chart-canvas"></canvas>
          </div>
        </div>
      </div>

      <!-- Top Clients Section -->
      <div class="dashboard-grid">
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Top Clients (Paid)</h2>
            <i class="fas fa-trophy"></i>
          </div>
          <div class="table-container">
            <table id="paidClientsTable">
              <thead>
                <tr>
                  <th onclick="sortTable('paidClientsTable', 0)">Client <span class="sort-arrow">▼</span></th>
                  <th onclick="sortTable('paidClientsTable', 1, 'number')">Invoices <span class="sort-arrow">▼</span></th>
                </tr>
              </thead>
              <tbody id="paidClientsBody">
                <!-- Will be populated by JavaScript -->
              </tbody>
            </table>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Top Clients (Unpaid)</h2>
            <i class="fas fa-exclamation-circle"></i>
          </div>
          <div class="table-container">
            <table id="unpaidClientsTable">
              <thead>
                <tr>
                  <th onclick="sortTable('unpaidClientsTable', 0)">Client <span class="sort-arrow">▼</span></th>
                  <th onclick="sortTable('unpaidClientsTable', 1, 'number')">Unpaid <span class="sort-arrow">▼</span></th>
                </tr>
              </thead>
              <tbody id="unpaidClientsBody">
                <!-- Will be populated by JavaScript -->
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Recent Invoices -->
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Recent Invoices</h2>
          <a href="history.php" style="color: var(--primary);">View All</a>
        </div>
        <div class="table-container">
          <table id="recentTable">
            <thead>
              <tr>
                <th onclick="sortTable('recentTable', 0)">Invoice <span class="sort-arrow">▼</span></th>
                <th onclick="sortTable('recentTable', 1)">Client <span class="sort-arrow">▼</span></th>
                <th onclick="sortTable('recentTable', 2, 'currency')">Amount <span class="sort-arrow">▼</span></th>
                <th onclick="sortTable('recentTable', 3)">Status <span class="sort-arrow">▼</span></th>
                <th onclick="sortTable('recentTable', 4, 'date')">Date <span class="sort-arrow">▼</span></th>
              </tr>
            </thead>
            <tbody id="recentInvoicesBody">
              <!-- Will be populated by JavaScript -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Initialize Charts
    let statusChart, timeChart;
    
    // Function to load dashboard data
    function loadDashboard(period = 'daily') {
      fetch(`dashboard-data.php?period=${period}&_=${Date.now()}`)
        .then(response => response.json())
        .then(data => {
          // Update summary cards
          // Re-fetch true totals for Paid/Unpaid cards only (all-time)
        fetch('dashboard-data.php?period=all&_=' + Date.now())
          .then(res => res.json())
          .then(summary => {
            document.getElementById('totalPaid').textContent = summary.status.paid;
            document.getElementById('totalUnpaid').textContent = summary.status.unpaid;
          })
          .catch(err => console.error('Total count fetch error:', err));

          
          // Destroy existing charts if they exist
          if (statusChart) statusChart.destroy();
          if (timeChart) timeChart.destroy();
          
          // Create Doughnut Chart (Invoice Status)
          const statusCtx = document.getElementById('statusChart').getContext('2d');
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
              plugins: {
                legend: {
                  position: 'bottom',
                  labels: {
                    font: {
                      size: 13
                    },
                    padding: 20
                  }
                },
                tooltip: {
                  callbacks: {
                    label: function(context) {
                      return `${context.label}: ${context.raw} invoices`;
                    }
                  }
                }
              },
              cutout: '70%'
            }
          });

        console.log('Invoice Activity Data:', {
              labels: data.labels,
              paid: data.paid_series,
              unpaid: data.unpaid_series
        });

          // Create Bar Chart (Invoice Activity)
          const timeCtx = document.getElementById('timeChart').getContext('2d');
          timeChart = new Chart(timeCtx, {
            type: 'bar',
            data: {
              labels: data.labels.length ? data.labels : ['No Data'],
                datasets: [
                  {
                    label: 'Paid',
                    data: data.paid_series.length ? data.paid_series : [0],
                    backgroundColor: '#4cc9f0',
                    borderRadius: 5
                  },
                  {
                    label: 'Unpaid',
                    data: data.unpaid_series.length ? data.unpaid_series : [0],
                    backgroundColor: '#f72585',
                    borderRadius: 5
                  }
                ]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: {
                  position: 'top',
                  labels: {
                    font: {
                      size: 13
                    }
                  }
                }
              },
              scales: {
                x: {
                  grid: {
                    display: false
                  }
                },
                y: {
                  beginAtZero: true,
                  ticks: {
                    precision: 0
                  }
                }
              }
            }
          });
        })
        .catch(error => console.error('Dashboard error:', error));
    }

    // Load summary data
    function loadSummary() {
      fetch('dashboard-summary.php')
        .then(res => res.json())
        .then(summary => {
          // Update revenue and deficit
          document.getElementById('totalRevenue').textContent = "CA$ " + summary.total_revenue.toFixed(2);
          document.getElementById('totalDeficit').textContent = "CA$ " + summary.total_deficit.toFixed(2);
          
          // Populate recent invoices
          const recentInvoicesBody = document.getElementById('recentInvoicesBody');
          recentInvoicesBody.innerHTML = '';
          summary.recent_invoices.forEach(invoice => {
            const row = document.createElement('tr');
            row.innerHTML = `
              <td>${invoice.invoice_number}</td>
              <td>${invoice.bill_to_name}</td>
              <td>CA$${parseFloat(invoice.total_amount).toFixed(2)}</td>
              <td><span class="status-badge ${invoice.status === 'Paid' ? 'status-paid' : 'status-unpaid'}">${invoice.status}</span></td>
              <td>${invoice.created_at}</td>
            `;
            recentInvoicesBody.appendChild(row);
          });
        })
        .catch(err => console.error('Summary load error:', err));
    }

    // Load top clients
    function loadTopClients() {
      // Fetch paid clients data
      fetch('dashboard-data.php?paid_clients=true')
        .then(res => res.json())
        .then(data => {
          const paidClientsBody = document.getElementById('paidClientsBody');
          paidClientsBody.innerHTML = '';
          
          if (data.top_clients && data.top_clients.length > 0) {
            data.top_clients.forEach(client => {
              const row = document.createElement('tr');
              row.innerHTML = `
                <td>${client.bill_to_name}</td>
                <td>${client.total}</td>
              `;
              paidClientsBody.appendChild(row);
            });
          } else {
            paidClientsBody.innerHTML = `
              <tr>
                <td colspan="2" style="text-align: center;">No paid clients found</td>
              </tr>
            `;
          }
        })
        .catch(err => console.error('Paid clients error:', err));

      // Fetch unpaid clients data
      fetch('dashboard-data.php?unpaid_clients=true')
        .then(res => res.json())
        .then(data => {
          const unpaidClientsBody = document.getElementById('unpaidClientsBody');
          unpaidClientsBody.innerHTML = '';
          
          // Populate unpaid clients table
          if (data.top_unpaid && data.top_unpaid.length > 0) {
            data.top_unpaid.forEach(client => {
              const row = document.createElement('tr');
              row.innerHTML = `
                <td>${client.bill_to_name}</td>
                <td>${client.count}</td>
              `;
              unpaidClientsBody.appendChild(row);
            });
          } else {
            unpaidClientsBody.innerHTML = `
              <tr>
                <td colspan="2" style="text-align: center;">No unpaid clients found</td>
              </tr>
            `;
          }
        })
        .catch(err => console.error('Unpaid clients error:', err));
    }

    // Enhanced Table Sorting Function
    function sortTable(tableId, columnIndex, dataType = 'text') {
      const table = document.getElementById(tableId);
      const tbody = table.tBodies[0];
      const rows = Array.from(tbody.rows);
      
      // Get current sort state
      const currentOrder = table.getAttribute('data-sort-order') || 'asc';
      const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
      table.setAttribute('data-sort-order', newOrder);
      table.setAttribute('data-sort-column', columnIndex);
      
      // Sort rows based on data type
      rows.sort((a, b) => {
        let aValue = a.cells[columnIndex].textContent.trim();
        let bValue = b.cells[columnIndex].textContent.trim();
        
        // Handle different data types
        if (dataType === 'number') {
          // Convert to numbers for proper numerical sorting
          aValue = parseInt(aValue, 10) || 0;
          bValue = parseInt(bValue, 10) || 0;
          
          if (newOrder === 'asc') {
            return aValue - bValue; // Ascending numerical order
          } else {
            return bValue - aValue; // Descending numerical order
          }
        } 
        else if (dataType === 'currency') {
          // Remove currency symbols and commas for proper numerical sorting
          aValue = parseFloat(aValue.replace(/[^\d.]/g, '')) || 0;
          bValue = parseFloat(bValue.replace(/[^\d.]/g, '')) || 0;
          
          if (newOrder === 'asc') {
            return aValue - bValue; // Ascending numerical order
          } else {
            return bValue - aValue; // Descending numerical order
          }
        }
        else if (dataType === 'date') {
          // Convert to Date objects for proper date sorting
          aValue = new Date(aValue);
          bValue = new Date(bValue);
          
          if (newOrder === 'asc') {
            return aValue - bValue; // Ascending date order
          } else {
            return bValue - aValue; // Descending date order
          }
        }
        else {
          // Standard text comparison
          if (aValue < bValue) return newOrder === 'asc' ? -1 : 1;
          if (aValue > bValue) return newOrder === 'asc' ? 1 : -1;
          return 0;
        }
      });
      
      // Re-append rows in new order
      rows.forEach(row => tbody.appendChild(row));
      
      // Update sort indicators
      const arrows = table.querySelectorAll('.sort-arrow');
      arrows.forEach(arrow => arrow.textContent = '▼');
      
      const header = table.rows[0].cells[columnIndex];
      const arrow = header.querySelector('.sort-arrow');
      if (arrow) {
        arrow.textContent = newOrder === 'asc' ? '▲' : '▼';
      }
    }
    
document.addEventListener('DOMContentLoaded', () => {
      try {
        const btn = document.getElementById('themeToggle');
        if (btn) {
          const icon = btn.querySelector('i');
          const darkPref = localStorage.getItem('darkMode');
          const isDarkInitially = darkPref === '1';
          document.documentElement.classList.toggle('dark-mode', isDarkInitially);
          icon.className = `fas ${isDarkInitially ? 'fa-sun' : 'fa-moon'}`;
          btn.addEventListener('click', () => {
            const nowDark = document.documentElement.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', nowDark ? '1' : '0');
            icon.className = `fas ${nowDark ? 'fa-sun' : 'fa-moon'}`;
          });
        }
    
        // ✅ Wait for DOM paint cycle before loading dashboard
        requestAnimationFrame(() => {
          setTimeout(() => {
            loadDashboard();
            loadSummary();
            loadTopClients();
    
            const period = document.getElementById('chartPeriod');
            if (period) {
              period.addEventListener('change', function () {
                loadDashboard(this.value);
              });
            }
          }, 100); // 100ms delay ensures canvas + DOM is ready
        });
    
      } catch (err) {
        console.error('Error in DOMContentLoaded:', err);
      }
    });

  </script>
 <?php require 'scripts.php'; ?>
</body>
</html>