<style>
/* CSS variables for theming */
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
  --sidebar-width: 220px;
  --transition: all 0.3s ease;
  --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
  --radius: 10px;
  --sidebar-bg: #2c3e50;
}

body.dark-mode {
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

/* Base styles */
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

/* ✅ App logo in header (exact size + left aligned inside the box) */
.logo img {
  width: 208px;
  height: 56px;
  object-fit: contain;
  object-position: left center; /* ✅ IMPORTANT: removes left “extra space” */
  display: block;
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
  /* Bypass variable to test */
  width: 240px !important;
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

.btn i {
  margin-right: 0.05px;
}

.btn {
  justify-content: center; /* Center icon + text */
  text-align: center;
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

.btn-small {
  font-size: 0.85rem;
  padding: 0.4rem 0.8rem;
}

.btn-uniform {
  width: 110px; /* or auto-adjust as needed */
  text-align: center;
}

.actions-cell .btn-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: none;
  font-size: 1rem;
  transition: var(--transition);
}

.btn-edit {
  background: rgba(76, 201, 240, 0.2);
  color: var(--success);
}

.btn-download {
  background: rgba(67, 97, 238, 0.15);
  color: var(--primary);
}

.btn-icon:hover {
  box-shadow: var(--shadow-hover);
  transform: scale(1.05);
}

/* Optional only if needed */

/* ✅ Modal Overlay Styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

/* ✅ Modal Card Styling */
.modal-card {
  background: var(--card-bg);
  color: var(--dark);
  padding: 2rem;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  width: 100%;
  max-width: 480px;
  animation: fadeIn 0.2s ease-in-out;
}

/* ✅ Modal Title */
.modal-title {
  font-size: 1.4rem;
  margin-bottom: 1.5rem;
  color: var(--primary);
  font-weight: 700;
}

/* ✅ Modal Input Styles */
.modal-card .form-group {
  margin-bottom: 1.2rem;
}

.modal-card label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.4rem;
  color: var(--dark);
}

.modal-card input.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  font-size: 1rem;
  background: var(--light);
  color: var(--dark);
  transition: var(--transition);
  transition: border 0.2s ease, background 0.2s ease, color 0.2s ease;
}

.error-msg {
  color: var(--danger);
  font-size: 0.875rem;
  margin-top: 0.25rem;
  min-height: 1em;
}

/* --- STRONGER rules for live password validation --- */
.input-wrapper.error input,
.form-control.error            { border: 1px solid var(--danger) !important; }

.input-wrapper.valid  input,
.form-control.valid             { border: 1px solid var(--success) !important; }

/* icon on the wrapper (works in every browser) */
.input-wrapper.error::after,
.input-wrapper.valid::after {
  content: '';
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  background-size: contain;
}
.input-wrapper.error::after {
  background-image:url("data:image/svg+xml,%3Csvg fill='%23f72585' height='20' viewBox='0 0 24 24'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 13l-1.41 1.41L12 13.41l-3.59 3.59L7 15l3.59-3.59L7 7.83 8.41 6.41 12 10l3.59-3.59L17 7.83l-3.59 3.59L17 15z'/%3E%3C/svg%3E");
}
.input-wrapper.valid::after  {
  background-image:url("data:image/svg+xml,%3Csvg fill='%234cc9f0' height='20' viewBox='0 0 24 24'%3E%3Cpath d='M9 16.17l-3.88-3.88L4 13.41l5 5 9-9-1.41-1.42z'/%3E%3C/svg%3E");
}


.modal-card input:focus {
  border-color: var(--primary);
  outline: none;
  box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
}

/* ✅ Modal Buttons */
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 1rem;
}

.modal-card input.valid,
.modal-card input.error {
  position: relative; /* REQUIRED to allow ::after */
  padding-right: 2.5rem; /* Ensure space for icon */
}

.modal-card input.valid,
.modal-card input.error {
  transition: border 0.2s ease, background-color 0.2s ease;
}

/* ✅ Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to   { opacity: 1; transform: scale(1); }
}

input.valid::after,
input.error::after {
  content: '';
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  background-size: contain;
}

input.valid {
  border: 1px solid var(--success);
  background-color: #e0f7fa;
  background-image: url("data:image/svg+xml,%3Csvg fill='%234cc9f0' height='20' viewBox='0 0 24 24' width='20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M9 16.17l-3.88-3.88L4 13.41l5 5 9-9-1.41-1.42z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 2.5rem;
}

input.error {
  border: 1px solid var(--danger);
  background-color: #ffe5ec;
  background-image: url("data:image/svg+xml,%3Csvg fill='%23f72585' height='20' viewBox='0 0 24 24' width='20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 13l-1.41 1.41L12 13.41l-3.59 3.59L7 15l3.59-3.59L7 7.83 8.41 6.41 12 10l3.59-3.59L17 7.83l-3.59 3.59L17 15z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 2.5rem;
}

.input-wrapper {
  position: relative;
}

.input-wrapper input.valid::after,
.input-wrapper input.error::after {
  content: '';
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  background-repeat: no-repeat;
  background-size: contain;
}

.input-wrapper input.error::after {
  background-image: url("data:image/svg+xml,%3Csvg fill='%23f72585' height='20' viewBox='0 0 24 24' width='20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 13l-1.41 1.41L12 13.41l-3.59 3.59L7 15l3.59-3.59L7 7.83 8.41 6.41 12 10l3.59-3.59L17 7.83l-3.59 3.59L17 15z'/%3E%3C/svg%3E");
}

.input-wrapper input.valid::after {
  background-image: url("data:image/svg+xml,%3Csvg fill='%234cc9f0' height='20' viewBox='0 0 24 24' width='20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M9 16.17l-3.88-3.88L4 13.41l5 5 9-9-1.41-1.42z'/%3E%3C/svg%3E");
}

.form-control {
  width: 100%;
  padding: 0.8rem;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  background: var(--card-bg);
  color: var(--dark);
  font-size: 1rem;
  transition: var(--transition);
}

/* Body text in table */
table td {
  color: var(--dark);
  transition: color var(--transition);
}

/* Table headings - always white text */
table th {
  background: var(--primary);
  color: white;
  font-weight: 600;
  transition: background var(--transition), color var(--transition);
}

/* Dark mode rows */
.dark-mode table td {
  color: white;
}

/* Center-align Invoice # and Amount columns */
.history-table th:nth-child(1),
.history-table td:nth-child(1),
.history-table th:nth-child(2),
.history-table td:nth-child(2) {
  text-align: center;
}

/* Center-align content in the Date and Client columns */
.history-table td:nth-child(3),
.history-table th:nth-child(3),
.history-table td:nth-child(4),
.history-table th:nth-child(4) {
  text-align: center;
}

/* Center-align Status and Action columns */
.history-table th:nth-child(5),
.history-table td:nth-child(5),
.history-table th:nth-child(6),
.history-table td:nth-child(6) {
  text-align: center;
}

tbody tr:hover {
  background: rgba(67, 97, 238, 0.05);
}


   a.btn {
   text-decoration: none;
}

   a.btn:hover {
   text-decoration: none;
}

.user-profile {
  display: flex;
  align-items: center;
  cursor: pointer;
  margin-left: 1rem;
  position: relative;
}

.user-avatar {
  width: 35px;
  height: 35px;
  background-color: #4cc9f0;
  color: white;
  border-radius: 50%;
  font-weight: bold;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-right: 0.5rem;
}

.user-name {
  font-weight: 500;
  color: white;
}

.profile-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
  padding: 0.5rem 0;
  border-radius: 8px;
  min-width: 180px;
  display: none;
  flex-direction: column;
  z-index: 99;
}

.profile-menu a {
  padding: 0.5rem 1rem;
  color: #333;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.2s;
}

.profile-menu a:hover {
  background: #f1f1f1;
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
}
</style>