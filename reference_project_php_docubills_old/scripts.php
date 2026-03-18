<script>
/* Theme toggle */
document.addEventListener('DOMContentLoaded', function() {
  console.log('[CustomTable] DOMContentLoaded — initializing custom-table builder');
  const themeToggle = document.getElementById('themeToggle');
  const savedTheme = localStorage.getItem('theme') || 'light';
  if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
    themeToggle.querySelector('i').classList.replace('fa-moon','fa-sun');
  }
  themeToggle.addEventListener('click', () => {
    const icon = themeToggle.querySelector('i');
    document.body.classList.toggle('dark-mode');
    if (document.body.classList.contains('dark-mode')) {
      icon.classList.replace('fa-moon','fa-sun');
      localStorage.setItem('theme','dark');
    } else {
      icon.classList.replace('fa-sun','fa-moon');
      localStorage.setItem('theme','light');
    }
  });
});

/* Profile menu toggle */
document.addEventListener('click', function(e) {
  const trigger = document.getElementById('userProfileTrigger');
  const menu    = document.getElementById('profileMenu');
  if (trigger && trigger.contains(e.target)) {
    menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
  } else if (menu) {
    menu.style.display = 'none';
  }
});

/* Password modal open/close */
function openPasswordModal() {
  document.getElementById('passwordModal').style.display = 'flex';
}
function closePasswordModal() {
  document.getElementById('passwordModal').style.display = 'none';
}

/* Submit change-password form */
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
  e.preventDefault();

  const current = document.getElementById('current_password');
  const newPass = document.getElementById('new_password');
  const confirm = document.getElementById('confirm_password');
  const submitBtn = document.getElementById('passwordSubmitBtn');

  // ① MISMATCH CHECK FIRST (so we don't clear current-password error prematurely)
  if (newPass.value !== confirm.value) {
    ['new','confirm'].forEach(f => {
      const inp = document.getElementById(f + '_password');
      const err = document.getElementById(f + '_password_error');
      err.textContent = '';
      inp.classList.remove('error','valid');
      inp.closest('.input-wrapper')?.classList.remove('error','valid');
    });
    setState(newPass, false);
    setState(confirm, false);
    document.getElementById('new_password_error').textContent     = 'Passwords do not match.';
    document.getElementById('confirm_password_error').textContent = 'Passwords do not match.';
    return;
  }

  // ② Clear any leftover new/confirm styles (because they match now)
  ['new','confirm'].forEach(f => {
    const inp = document.getElementById(f + '_password');
    const err = document.getElementById(f + '_password_error');
    err.textContent = '';
    inp.classList.remove('error','valid');
    inp.closest('.input-wrapper')?.classList.remove('error','valid');
  });

  // ③ Now clear the current-password error before AJAX
  const currErr = document.getElementById('current_password_error');
  currErr.textContent = '';
  setState(current, true);

  // ④ AJAX submit
  submitBtn.disabled   = true;
  submitBtn.textContent = 'Saving...';

  fetch('ajax-update-password.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      current_password: current.value,
      new_password:     newPass.value,
      confirm_password: confirm.value
    })
  })
  .then(res => res.json())
  .then(data => {
    if (!data.success) {
      setState(current, false);
      currErr.textContent = data.message || 'Current password is incorrect.';
      return;
    }
    closePasswordModal();
    showSuccessModal();
    [current,newPass,confirm].forEach(i => i.value = '');
  })
  .catch(() => {
    setState(current, false);
    currErr.textContent = 'Network error, please try again.';
  })
  .finally(() => {
    submitBtn.disabled   = false;
    submitBtn.textContent = 'Save';
  });
});

/* Input state helper */
function setState(el, ok) {
  const wrap = el.closest('.input-wrapper');
  if (wrap) wrap.classList.toggle('error', !ok), wrap.classList.toggle('valid', ok);
  el.classList.toggle('error', !ok);
  el.classList.toggle('valid', ok);
}

/* Success-modal helper */
function showSuccessModal() {
  const m = document.getElementById('passwordSuccessModal');
  m.style.display = 'flex';
  setTimeout(() => m.style.display = 'none', 3000);
}

function closeSuccessModal() {
  const modal = document.getElementById('passwordSuccessModal');
  if (!modal) return;
  modal.style.display = 'none';
}

/* Live check: current password */
const currentPasswordInput = document.getElementById('current_password');
if (currentPasswordInput) {
  currentPasswordInput.addEventListener('input', function() {
    const val   = this.value.trim();
    const errEl = document.getElementById('current_password_error');
    setState(this, true);
    errEl.textContent = '';
    if (val.length < 3) return;

    fetch('ajax-check-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ current_password: val })
    })
    .then(r => r.json())
    .then(data => {
      setState(this, data.valid);
      errEl.textContent = data.valid ? '' : 'Current password is incorrect.';
    })
    .catch(() => {
      setState(this, false);
      errEl.textContent = 'Check failed. Try again.';
    });
  });
}

/* Live match: new vs confirm (header modal) */
const hNewPass     = document.getElementById('header_new_password');
const hConfirmPass = document.getElementById('header_confirm_password');

if (hNewPass && hConfirmPass) {
  function validateHeaderPasswords() {
    const errNew     = document.getElementById('header_new_password_error');
    const errConfirm = document.getElementById('header_confirm_password_error');

    errNew.textContent = '';
    errConfirm.textContent = '';

    if (!hConfirmPass.value) {
      setState(hNewPass, true);
      setState(hConfirmPass, true);
      return;
    }

    const match = hNewPass.value === hConfirmPass.value;
    setState(hNewPass, match);
    setState(hConfirmPass, match);
    if (!match) {
      errNew.textContent     = 'Passwords do not match.';
      errConfirm.textContent = 'Passwords do not match.';
    }
  }

  hNewPass.addEventListener('input', validateHeaderPasswords);
  hConfirmPass.addEventListener('input', validateHeaderPasswords);
}

function openHeaderPasswordModal() {
  document.getElementById('headerPasswordModal').style.display = 'flex';
}
function closeHeaderPasswordModal() {
  const modal = document.getElementById('headerPasswordModal');
  if (!modal) return;

  modal.style.display = 'none';

  // ✅ Clear all inputs
  ['header_current_password', 'header_new_password', 'header_confirm_password'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });

  // ✅ Clear all error messages
  ['header_current_password_error', 'header_new_password_error', 'header_confirm_password_error'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = '';
  });

  // ✅ Reset input visual states
  ['header_current_password', 'header_new_password', 'header_confirm_password'].forEach(id => {
    const el = document.getElementById(id);
    const wrapper = el?.closest('.input-wrapper');
    if (el) {
      el.classList.remove('error', 'valid');
    }
    if (wrapper) {
      wrapper.classList.remove('error', 'valid');
    }
  });
}

/* Live check: current password (header modal) */
const hCurrentPass = document.getElementById('header_current_password');

if (hCurrentPass) {
  hCurrentPass.addEventListener('input', function() {
    const val   = this.value.trim();
    const errEl = document.getElementById('header_current_password_error');
    setState(this, true);
    errEl.textContent = '';
    if (val.length < 3) return;

    fetch('ajax-check-password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ current_password: val })
    })
    .then(r => r.json())
    .then(data => {
      setState(this, data.valid);
      errEl.textContent = data.valid ? '' : 'Current password is incorrect.';
    })
    .catch(() => {
      setState(this, false);
      errEl.textContent = 'Check failed. Try again.';
    });
  });
}

// ────────────────────────────────────────────────────────────────────
// Invoice Source Toggle & Custom-Table Builder
document.addEventListener('DOMContentLoaded', function() {
  const googleSection  = document.getElementById('google-section');
  const uploadSection  = document.getElementById('upload-section');
  const customBuilder  = document.getElementById('custom-table-builder');
  const radios         = document.querySelectorAll('input[name="invoice_source"]');

  // 1. Toggle sections when a radio changes
  radios.forEach(radio => {
    radio.addEventListener('change', function() {
      switch (this.value) {
        case 'google':
          googleSection.style.display = 'block';
          uploadSection.style.display = 'none';
          customBuilder.style.display = 'none';
          break;
        case 'upload':
          googleSection.style.display = 'none';
          uploadSection.style.display = 'block';
          customBuilder.style.display = 'none';
          break;
        case 'custom':
          googleSection.style.display = 'none';
          uploadSection.style.display = 'none';
          customBuilder.style.display = 'block';
          break;
      }
    });
  });

  // 2. Build the custom table when “Generate Table” is clicked
  const genBtn       = document.getElementById('generate-custom-table');
  const colSelect    = document.getElementById('custom-col-count');
  const tblContainer = document.getElementById('custom-table-container');

  genBtn?.addEventListener('click', function() {
    const colCount = parseInt(colSelect.value, 10);
    tblContainer.innerHTML = ''; // clear previous table

    // Create table element
    const table = document.createElement('table');
    table.classList.add('table', 'table-bordered', 'mt-2');

    // Create header
    const thead = table.createTHead();
    const headerRow = thead.insertRow();
    for (let i = 0; i < colCount; i++) {
      const th = document.createElement('th');
      th.contentEditable = 'true';
      th.innerText = `Header ${i+1}`;
      headerRow.appendChild(th);
    }

    // Create first body row
    const tbody = table.createTBody();
    const row = tbody.insertRow();
    for (let i = 0; i < colCount; i++) {
      const td = document.createElement('td');
      td.contentEditable = 'true';
      td.innerHTML = '&nbsp;';   // ensure cell has height
      row.appendChild(td);
    }

    // Append table to container
    tblContainer.appendChild(table);

    // Add “Add Row” button
    const addRowBtn = document.createElement('button');
    addRowBtn.type = 'button';
    addRowBtn.className = 'btn btn-sm btn-outline-primary mt-2';
    addRowBtn.innerText = 'Add Row';
    addRowBtn.addEventListener('click', () => {
      const newRow = tbody.insertRow();
      for (let i = 0; i < colCount; i++) {
        const td = document.createElement('td');
        td.contentEditable = 'true';
        td.innerHTML = '&nbsp;';  // ensure new cell is visible
        newRow.appendChild(td);
      }
    });

    tblContainer.appendChild(addRowBtn);
  });
});

</script>
