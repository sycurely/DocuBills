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
  --sidebar-width: 240px;
  --transition: all 0.3s ease;
  --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
  --radius: 10px;
  --sidebar-bg: #2c3e50;
  --icon-size: 1.1rem;
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

i.fas,
i.far,
i.fab,
i.fa,
.material-icons,
.material-icons-outlined,
.material-symbols-rounded {
  font-size: var(--icon-size);
  line-height: 1;
  vertical-align: middle;
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
  color: white;
  text-decoration: none;
}

.logo i {
  font-size: 1.8rem;
}

.logo img {
  width: 208px;
  height: 56px;
  object-fit: contain;
  object-position: left center;
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
  position: relative;
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
  overflow: hidden;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-name {
  font-weight: 500;
  color: white;
}

.profile-menu {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  background: var(--card-bg);
  box-shadow: var(--shadow-hover);
  border-radius: var(--radius);
  min-width: 180px;
  display: none;
  flex-direction: column;
  z-index: 9999;
  padding: 0.5rem 0;
}

.profile-menu a {
  padding: 0.75rem 1rem;
  color: var(--dark);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: background 0.2s;
}

.profile-menu-link {
  padding: 0.75rem 1rem;
  color: var(--dark);
  display: flex;
  align-items: center;
  gap: 10px;
}

.profile-menu a:hover {
  background: rgba(0, 0, 0, 0.05);
}

.profile-menu-link:hover {
  background: rgba(0, 0, 0, 0.05);
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
  font-size: 1rem;
}

.menu-item:hover,
.menu-item.active {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border-left: 4px solid var(--primary-light);
}

.menu-item i {
  width: 24px;
  text-align: center;
}

.menu-item .material-icons-outlined,
.submenu-item .material-icons-outlined,
.profile-menu .material-icons-outlined {
  width: 24px;
  text-align: center;
  font-size: var(--icon-size);
}

.menu-item.has-submenu .submenu-toggle-icon {
  margin-left: auto;
  transition: transform 0.3s ease;
}

.menu-item.has-submenu.active .submenu-toggle-icon {
  transform: rotate(180deg);
}

.submenu {
  display: none;
  flex-direction: column;
  padding-left: 1.5rem;
}

.submenu.show {
  display: flex;
}

.submenu-item {
  padding: 0.5rem 1rem 0.5rem 2.25rem;
  color: rgba(255, 255, 255, 0.7);
  font-size: 1rem;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: var(--transition);
}

.menu-item-logout {
  color: #ff8da0 !important;
}

.menu-item-logout:hover,
.menu-item-logout.active {
  color: #ffffff !important;
  background: rgba(247, 37, 133, 0.22) !important;
  border-left: 4px solid var(--danger) !important;
}

.submenu-item:hover,
.submenu-item.active {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.submenu-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 4px;
  height: 100%;
  background-color: var(--primary);
}

/* Main Content Styles */
.main-content-wrapper {
  flex: 1;
  margin-left: var(--sidebar-width);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  transition: var(--transition);
}

.main-content {
  flex: 1;
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

/* Button Styles */
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
  text-decoration: none;
  justify-content: center;
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

.btn-danger {
  background: var(--danger);
  color: white;
}

.btn-danger:hover {
  background: #d91a6b;
}

.btn-secondary {
  background: var(--gray);
  color: white;
}

.btn-secondary:hover {
  filter: brightness(0.92);
}

.btn-sm {
  padding: 0.45rem 0.9rem;
  font-size: 0.88rem;
}

/* Alert Styles */
.alert {
  padding: 1rem 1.5rem;
  border-radius: var(--radius);
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 500;
}

.alert-success {
  background: rgba(76, 201, 240, 0.15);
  color: var(--success);
  border: 1px solid var(--success);
}

.alert-danger {
  background: rgba(247, 37, 133, 0.15);
  color: var(--danger);
  border: 1px solid var(--danger);
}

/* Card Styles */
.card {
  background: var(--card-bg);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  transition: var(--transition);
}

.card:hover {
  box-shadow: var(--shadow-hover);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.section-title {
  margin: 0;
  color: var(--primary);
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
}

.inline-alert {
  border-radius: 8px;
  padding: 0.65rem 0.8rem;
  margin: 0.75rem 0;
  font-size: 0.92rem;
}

.inline-alert-success {
  background: rgba(76, 201, 240, 0.15);
  color: var(--success);
  border: 1px solid var(--success);
}

.inline-alert-danger {
  background: rgba(247, 37, 133, 0.15);
  color: var(--danger);
  border: 1px solid var(--danger);
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  border-radius: 999px;
  border: 1px solid var(--border);
  padding: 0.18rem 0.55rem;
  font-size: 0.79rem;
  color: var(--gray);
  background: rgba(67, 97, 238, 0.06);
}

.sticky-action-bar {
  position: sticky;
  bottom: 0;
  z-index: 12;
  margin-top: 1rem;
  background: color-mix(in srgb, var(--card-bg) 94%, transparent);
  backdrop-filter: blur(6px);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 0.7rem 0.75rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.6rem;
}

.sticky-action-bar .actions-left,
.sticky-action-bar .actions-right {
  display: inline-flex;
  gap: 0.5rem;
}

.grid-two {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.85rem;
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.settings-card,
.surface-card {
  background: var(--card-bg);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 1.5rem;
}

.settings-card h3 {
  margin: 0 0 1rem 0;
  color: var(--primary);
}

.stack-sm > * + * {
  margin-top: 0.5rem;
}

.stack-md > * + * {
  margin-top: 0.9rem;
}

.input,
.textarea,
.select {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  background: var(--card-bg);
  color: var(--dark);
}

.input:focus,
.textarea:focus,
.select:focus {
  border-color: var(--primary);
  outline: none;
  box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
}

.form-row {
  margin-bottom: 0.9rem;
}

.form-row label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.35rem;
}

.form-row input:not([type="radio"]):not([type="checkbox"]),
.form-row select,
.form-row textarea {
  width: 100%;
  padding: 0.6rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  background: var(--card-bg);
  color: var(--dark);
}

.form-row small {
  display: block;
  color: var(--gray);
  margin-top: 0.25rem;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-overlay.show {
  display: flex;
}

.modal-card {
  background: var(--card-bg);
  border-radius: var(--radius);
  padding: 2rem;
  width: 90%;
  max-width: 560px;
  box-shadow: var(--shadow-hover);
}

.modal-title {
  color: var(--primary);
  font-size: 1.35rem;
  margin-bottom: 1rem;
}

.modal-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 1.25rem;
}

.inline-form {
  display: inline;
}

.unstyled-button {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  font: inherit;
}

.full-width {
  width: 100%;
}

.text-left {
  text-align: left;
}

.text-center {
  text-align: center;
}

.justify-center {
  justify-content: center;
}

.mt-lg {
  margin-top: 1.5rem;
}

.mt-xs {
  margin-top: 0.25rem;
}

.mt-sm {
  margin-top: 0.5rem;
}

.max-w-900 {
  max-width: 900px;
}

.is-hidden {
  display: none;
}

.flex-center-between {
  display: flex;
  justify-content: space-between;
  align-items: center;
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
}

th:hover {
  background: rgba(67, 97, 238, 0.15);
}

tbody tr:hover {
  background: rgba(67, 97, 238, 0.05);
}

/* Form Styles */
.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: var(--dark);
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  background: var(--card-bg);
  color: var(--dark);
  font-size: 1rem;
  transition: var(--transition);
}

.form-control:focus {
  border-color: var(--primary);
  outline: none;
  box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    width: 70px;
  }
  
  .sidebar .menu-text {
    display: none;
  }
  
  .main-content-wrapper {
    margin-left: 70px;
  }
  
  .user-name {
    display: none;
  }
}

/* Footer Styles */
.app-footer {
  background: var(--card-bg);
  border-top: 1px solid var(--border);
  padding: 1rem 1.5rem;
  margin-top: auto;
  transition: var(--transition);
}

.app-footer .footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto;
}

.app-footer .footer-text {
  color: var(--gray);
  font-size: 0.9rem;
}

.app-footer .footer-links {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.app-footer .footer-links a {
  color: var(--gray);
  text-decoration: none;
  font-size: 0.9rem;
  transition: var(--transition);
}

.app-footer .footer-links a:hover {
  color: var(--primary);
}

.app-footer .footer-links .separator {
  color: var(--border);
}

@media (max-width: 768px) {
  .app-footer {
    padding-left: calc(70px + 1.5rem);
  }
}

@media (max-width: 576px) {
  .header {
    padding: 0 1rem;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .sticky-action-bar {
    bottom: 0.6rem;
    padding-bottom: calc(0.7rem + env(safe-area-inset-bottom, 0px));
  }

  .sticky-action-bar .actions-left,
  .sticky-action-bar .actions-right {
    width: 100%;
  }

  .sticky-action-bar .actions-right .btn {
    width: 100%;
  }

  .app-footer {
    flex-direction: column;
    gap: 0.5rem;
    text-align: center;
    padding: 1rem;
  }

  .app-footer .footer-content {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>
<?php /**PATH D:\Invoice\Docubills web\Docubills\Docubills\resources\views/partials/styles.blade.php ENDPATH**/ ?>