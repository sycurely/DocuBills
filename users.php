<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$activeMenu = 'settings';
$activeTab  = 'users';

require_once 'config.php';
require_once 'middleware.php';
ob_start();
require_once 'styles.php';

/* ───────────────────────────────────────────────────────────── */

$roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD']==='POST') {
    // ─── Suspend / Unsuspend Handler ───
  if (isset($_POST['toggle_suspend']) && has_permission('suspend_users')) {
    $userId = (int) $_POST['toggle_suspend'];
    $pdo->prepare("
      UPDATE users
         SET is_suspended = NOT is_suspended
       WHERE id = ?
    ")->execute([$userId]);
    $_SESSION['success'] = "User suspension status updated.";
    header("Location: users.php");
    exit;
  }

  $pdo->prepare("UPDATE users SET role_id=? WHERE id=?")
      ->execute([$_POST['role_id'],$_POST['user_id']]);
  $_SESSION['success'] = "User updated successfully.";
  header("Location: users.php");
  exit;
}

$users = $pdo->query("
  SELECT 
    u.id,
    u.username,
    u.email,
    u.avatar,
    u.created_at,
    u.role_id,
    u.is_suspended,            /* ← added */
    r.name AS role_name
  FROM users u
  LEFT JOIN roles r ON u.role_id=r.id
  WHERE u.deleted_at IS NULL
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Management</title>
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- ★ ①  SweetAlert 2 for toast notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* ───────────  THEME  ─────────── */
:root{
  --primary:#4361ee;--primary-light:#4895ef;--secondary:#3f37c9;
  --success:#4cc9f0;--danger:#f72585;--warning:#f8961e;
  --dark:#212529;--light:#f8f9fa;--gray:#6c757d;--border:#dee2e6;
  --card-bg:#ffffff;--body-bg:#f5f7fb;--header-height:70px;
  --sidebar-width:250px;--transition:.3s ease;--shadow:0 4px 6px rgba(0,0,0,.1);
  --shadow-hover:0 8px 15px rgba(0,0,0,.1);--radius:10px;--sidebar-bg:#2c3e50;
}
body{background:var(--body-bg);font-family:'Segoe UI',Tahoma,sans-serif}
.app-container{display:flex;min-height:100vh}
.main-content{flex:1;padding:calc(var(--header-height)+1.5rem) 1.5rem}
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem}
.page-title{font-size:1.8rem;font-weight:700;color:var(--primary)}
.btn{padding:.6rem 1.2rem;border-radius:var(--radius);border:none;font-weight:600;cursor:pointer;
     transition:var(--transition);display:inline-flex;align-items:center;gap:8px}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--secondary);box-shadow:var(--shadow-hover)}
button[disabled]{opacity:.55;cursor:not-allowed}

.btn-secondary {
  background: var(--light);
  color: var(--dark);
  border: 1px solid var(--border);
}
.btn-secondary:hover {
  background: #e9ecef;
}

.btn-danger {
background: #f72585;
color: white;
}
.btn-cancel {
background: #adb5bd;
color: white;
}
.btn-icon {
  width: 38px;              /* same width as avatar */
  height: 38px;             /* same height as avatar */
  padding: 0;               /* no extra padding */
  display: inline-flex;     /* inline so it flows with other buttons */
  align-items: center;      /* center icon vertically */
  justify-content: center;  /* center icon horizontally */
  vertical-align: middle;   /* align within the text line */
  border-radius: var(--radius);  /* match your other buttons */
}

.btn-icon:last-child {
  margin-left: 0.25rem;  /* push only the lock icon rightward */
}

.table-container{overflow-x:auto;margin-top:2rem}
table{width:100%;border-collapse:collapse;border-radius:var(--radius);background:var(--card-bg)}
th, td {
  padding: 1rem;
  text-align: center;
  border-bottom: 1px solid var(--border);    /* ← semicolon! */
  vertical-align: middle;
}
th{background:rgba(67,97,238,.1);color:var(--primary);font-weight:600}
tbody tr:hover{background:rgba(67,97,238,.05)}
.avatar-initials{width:38px;height:38px;border-radius:50%;background:var(--primary);color:#fff;font-weight:600;
                 display:flex;justify-content:center;align-items:center;margin:0 auto}
.user-avatar{width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--primary);margin:0 auto}

/* 1) Make the Actions cell a left-packed flex box */

.actions-cell {
  display: flex;
  align-items: center;
  justify-content: center;  /* center the buttons horizontally */
  gap: 0.5rem;
}

td.actions-cell {
  padding-left: 1rem;
  padding-right: 1rem;
}

/* ───────────  MODALS  ─────────── */
.modal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0; top: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  width: 100%;
  max-width: 500px;
  text-align: center;
  position: relative;
}

.close-modal {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 1.5rem;
  cursor: pointer;
}
.close-modal:hover {
  color: var(--danger);
}

.modal-title {
  color: var(--primary);
  font-weight: 700;
  margin-bottom: 1rem;
}

.confirmation-message {
  font-size: 1rem;
  margin-bottom: 1rem;
}

.btn-group {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn-danger {
  background: #f72585;
  color: white;
}

.btn-cancel {
  background: #adb5bd;
  color: white;
}

/* SweetAlert2 – make it match history.php style */
/* 1) Match font */
.swal2-custom-popup {
  font-family: 'Segoe UI', Tahoma, sans-serif;
}

.swal2-custom-title {
  font-size: 1.25rem;     /* your history.php heading size */
  font-weight: 700;
  color: var(--dark);
}

.swal2-custom-content {
  font-size: 1rem;
  color: var(--dark);
  margin-top: 0.5rem;
}

/* 2) Icon circle */
.swal2-custom-icon {
  border: 2px solid var(--warning);
  color: var(--warning);
  width: 3.5rem; height: 3.5rem;
  line-height: 3.5rem;
  margin: 0 auto 1rem;
}

/* 3) Restore button spacing if needed */
.ml-2 { margin-left: 0.5rem !important; }

/* SweetAlert2 default action wrapper */
.swal2-actions .swal2-confirm + .swal2-cancel {
  margin-left: 0.5rem;
}

/* Universal popup font override */
.swal2-popup {
  font-family: 'Segoe UI', Tahoma, sans-serif !important;
}

.btn-space-left {
  margin-left: .5rem;
}

.form-group{margin-bottom:18px}.form-group label{display:block;margin-bottom:6px;font-weight:600}
.form-group input,.form-group select{width:100%;padding:10px;font-size:1rem;border-radius:var(--radius);border:1px solid var(--border)}
.form-actions{text-align:right;margin-top:20px}
.text-success{color:var(--success)!important}.text-danger{color:var(--danger)!important}
input.is-valid{border:2px solid var(--success)!important}
input.is-invalid{border:2px solid var(--danger)!important}

/* edit-specific borders confined to modal bodies */
#editUserContent input.is-valid,#editUserContent input.is-valid:focus{border:2px solid var(--success)!important;outline:none}
#editUserContent input.is-invalid,#editUserContent input.is-invalid:focus{border:2px solid var(--danger)!important;outline:none}
#addUserModal   input.is-valid,#addUserModal   input.is-valid:focus {border:2px solid var(--success)!important}
#addUserModal   input.is-invalid,#addUserModal input.is-invalid:focus{border:2px solid var(--danger)!important}
</style>
</head>
<body>
<div class="app-container">
<?php require 'sidebar.php'; ?>
<div class="main-content">
<?php require 'header.php'; ?>

<div class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">User Management</h1>
    <button class="btn btn-primary" onclick="openAddUserModal()">New User</button>
  </div>

  <!-- ★ ②  SweetAlert toast output (replaces alert-success/alert-danger divs) -->
  <?php
  foreach (['success','error'] as $key){
    if (!empty($_SESSION[$key])){
      $msg  = addslashes($_SESSION[$key]);   // escape quotes for JS
      $icon = $key==='success' ? 'success' : 'error';
      echo "<script>
              document.addEventListener('DOMContentLoaded',()=>{
                Swal.fire({
                  toast:true,position:'top-end',icon:'$icon',
                  title:'$msg',showConfirmButton:false,
                  timer:2200,timerProgressBar:true
                });
              });
            </script>";
      unset($_SESSION[$key]);
    }
  }
  ?>

  <div class="table-container">
    <table>
      <thead><tr><th>Avatar</th><th>Username</th><th>Email</th><th>Role</th><th>Created At</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($users as $u):
        $initial = strtoupper(substr($u['username'],0,1));
        if(strpos($u['username'],'@')!==false){
          $p=explode('@',$u['username'])[0];$parts=explode('.',$p);
          if(count($parts)>1) $initial=strtoupper($parts[0][0].$parts[1][0]);
        } ?>
        <tr>
          <td>
            <?php if($u['avatar']):?><img src="<?=htmlspecialchars($u['avatar'])?>" class="user-avatar">
            <?php else:?><div class="avatar-initials"><?=$initial?></div><?php endif;?>
          </td>
          <td><?=htmlspecialchars($u['username'])?></td>
          <td><?=htmlspecialchars($u['email'])?></td>
          <td><?=ucwords(str_replace('_',' ',$u['role_name']??'Unassigned'))?></td>
          <td><?=date('Y-m-d',strtotime($u['created_at']))?></td>
          
           <td class="actions-cell">
            <!-- VIEW button -->
            <button class="btn btn-sm btn-primary" onclick="openViewModal(<?= $u['id'] ?>)">
              <i class="fas fa-eye"></i>
            </button>
            
            <!-- EDIT button (if allowed) -->
            <?php if (has_permission('edit_user')): ?>
              <button class="btn btn-sm btn-warning" onclick="openEditModal(<?= $u['id'] ?>)">
                <i class="fas fa-edit"></i>
              </button>
            <?php endif; ?>
            
            <!-- DELETE button -->
            <button class="btn btn-sm btn-danger delete-user-btn"
                    data-id="<?= $u['id'] ?>"
                    data-username="<?= htmlspecialchars($u['username']) ?>">
              <i class="fas fa-trash-alt"></i>
            </button>
            
            <!-- SUSPEND/UNSUSPEND button --> 
            <?php if (has_permission('suspend_users')): ?>
              <form method="POST" class="suspend-form" style="display:inline;margin-left:4px">
                  <input type="hidden" name="toggle_suspend" value="<?= $u['id'] ?>">
                  <?php if ($u['is_suspended']): ?>
                    <button
                      type="button"
                      class="btn btn-sm btn-danger btn-icon suspend-toggle"
                      title="Unsuspend user">
                      <span style="color:#f8961e;font-size:1.2em;line-height:1;">🔒</span>
                    </button>
                  <?php else: ?>
                    <button
                      type="button"
                      class="btn btn-sm btn-success btn-icon suspend-toggle"
                      title="Suspend user">
                      <span style="color:#fff;font-size:1.2em;line-height:1;">🔓</span>
                    </button>
                  <?php endif; ?>
                </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach;?>
      </tbody>
    </table>
  </div><!-- table-container -->
</div><!-- page-wrapper -->
</div><!-- main-content -->
</div><!-- app-container -->

<!-- ────────── ADD USER MODAL ────────── -->
<div id="addUserModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeAddUserModal()">&times;</span>
    <h2 class="modal-title">Add New User</h2>

    <form id="addUserForm" method="POST" action="add_user.php">
      <div class="form-group"><label>Full Name</label><input type="text" name="full_name" required></div>

      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" id="add-username" required>
        <small id="add-username-help" style="font-size:.9em"></small>
      </div>

      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" id="add-email" required>
        <small id="add-email-help" style="font-size:.9em"></small>
      </div>

      <div class="form-group"><label>Temporary Password</label><input type="password" name="password" required></div>

      <div class="form-group">
        <label>Assign Role</label>
        <select name="role_id" required>
          <option value="">Select Role</option>
          <?php foreach($roles as $r):?><option value="<?=$r['id']?>"><?=htmlspecialchars($r['name'])?></option><?php endforeach;?>
        </select>
      </div>

      <div class="form-actions"><button type="submit" class="btn btn-primary" disabled>Create User</button></div>
    </form>
  </div>
</div>

<!-- ────────── VIEW / EDIT MODALS (content filled via AJAX) ────────── -->
<div id="viewUserModal" class="modal"><div class="modal-content" id="viewUserContent"></div></div>
<div id="editUserModal" class="modal"><div class="modal-content" id="editUserContent"></div></div>

<!-- ────────── CORE JS  ────────── -->
<script>
const checkURL = 'check_availability.php';   /* single source of truth */

/* reusable fetch helper */
function apiCheck(field,val,userId=0){
  const url = `${checkURL}?field=${field}&value=${encodeURIComponent(val)}&user_id=${userId}`;
  return fetch(url).then(r=>r.json()).catch(()=>({status:'error'}));
}

/* ─── ADD-USER validation ─────────────────────────────────────────── */
function attachAddUserValidation(){
  const form   = document.getElementById('addUserForm');
  const uField = document.getElementById('add-username');
  const eField = document.getElementById('add-email');
  const uHelp  = document.getElementById('add-username-help');
  const eHelp  = document.getElementById('add-email-help');
  const submit = form.querySelector('button[type="submit"]');
  let uOK=false,eOK=false;

  const mark=(help,input,ok,msgOK,msgBad)=>{
    help.classList.remove('text-success','text-danger');
    input.classList.remove('is-valid','is-invalid');
    if(ok===null){help.textContent='';update();return;}
    help.textContent = ok?msgOK:msgBad;
    help.classList.add(ok?'text-success':'text-danger');
    input.classList.add(ok?'is-valid':'is-invalid');
    if(input===uField) uOK=ok; if(input===eField) eOK=ok; update();
  };
  const update=()=>{submit.disabled = !(uOK && eOK);};

  const check=(fld,val)=>{
    const help=fld==='username'?uHelp:eHelp;
    const inp =fld==='username'?uField:eField;
    if(!val){mark(help,inp,null);return;}
    apiCheck(fld,val).then(j=>mark(
      help,inp,
      j.status==='available',
      fld==='email'?'Email address is available':'Username is available',
      fld==='email'?'Email address is already taken':'Username is already taken'
    ));
  };

  uField.addEventListener('input',()=>check('username',uField.value));
  eField.addEventListener('input',()=>check('email',   eField.value));
  /* run once so the button starts disabled */
  update();
}

/* ─── EDIT-USER validation ────────────────────────────────────────── */
function attachEditUserValidation(wrapper,userId){
  const form   = wrapper.querySelector('#editUserForm');
  const uField = wrapper.querySelector('#username');
  const eField = wrapper.querySelector('#email');
  const uHelp  = wrapper.querySelector('#username-help');
  const eHelp  = wrapper.querySelector('#email-help');
  const submit = form.querySelector('button[type="submit"]');
  let uOK=true,eOK=true;

  const mark=(field,ok)=>{
    const help=field==='username'?uHelp:eHelp;
    const inp =field==='username'?uField:eField;
    help.classList.remove('text-success','text-danger');
    inp .classList.remove('is-valid','is-invalid');
    help.textContent = ok
      ? (field==='email'?'Email address is available':'Username is available')
      : (field==='email'?'Email address is already taken':'Username is already taken');
    help.classList.add(ok?'text-success':'text-danger');
    inp .classList.add(ok?'is-valid':'is-invalid');
    if(field==='username') uOK=ok; if(field==='email') eOK=ok; submit.disabled=!(uOK&&eOK);
  };

  const check=(f,v)=>{
    if(!v){mark(f,true);return;}
    apiCheck(f,v,userId).then(j=>mark(f,j.status==='available'));
  };

  check('username',uField.value); check('email',eField.value);
  submit.disabled=!(uOK&&eOK);

  uField.addEventListener('input',()=>check('username',uField.value));
  eField.addEventListener('input',()=>check('email',   eField.value));

  form.addEventListener('submit',e=>{
    if(!uOK||!eOK){e.preventDefault();alert('❌ Fix username/email first.');}
  });
}

/* ─── modal helpers ──────────────────────────────────────────────── */
function openAddUserModal(){
  document.getElementById('addUserModal').style.display='flex';
  attachAddUserValidation();
}
function closeAddUserModal(){document.getElementById('addUserModal').style.display='none';}

function openViewModal(id){
  fetch('get_user.php?id='+id).then(r=>r.text()).then(html=>{
    document.getElementById('viewUserContent').innerHTML=html;
    document.getElementById('viewUserModal').style.display='flex';
  });
}
function openEditModal(id){
  fetch('edit_user.php?id='+id).then(r=>r.text()).then(html=>{
    const wrap=document.getElementById('editUserContent');
    wrap.innerHTML=html; attachEditUserValidation(wrap,id);
    document.getElementById('editUserModal').style.display='flex';
  });
}
function closeModal(id){document.getElementById(id).style.display='none';}

/* close any modal when clicking backdrop */
window.onclick=e=>{
  ['addUserModal','viewUserModal','editUserModal'].forEach(id=>{
    if(e.target===document.getElementById(id)) closeModal(id);
  });
};
</script>

<!-- ────────── DELETE USER MODAL ────────── -->
<div class="modal" id="deleteUserModal">
  <div class="modal-content">
    <span class="close-modal" id="closeDeleteUserModal">&times;</span>
    <h2 class="modal-title">Confirm Deletion</h2>
    <div class="confirmation-message">
      Are you sure you want to delete user 
      <strong id="deleteUsername"></strong>?
    </div>
    <p>This action will move the user to Trash Bin and can be restored.</p>
    <form method="POST" action="soft_delete_user.php"
          onsubmit="return !!document.getElementById('delete_user_id').value;">
      <input type="hidden" name="user_id" id="delete_user_id" value="">
      <div class="btn-group">
        <button type="button" class="btn btn-secondary" id="cancelDeleteUser">Cancel</button>
        <button type="submit" class="btn btn-danger">
          <i class="fas fa-trash"></i> Delete User
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1) Open
  document.querySelectorAll('.delete-user-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      document.getElementById('delete_user_id').value   = btn.dataset.id;
      document.getElementById('deleteUsername').textContent = btn.dataset.username;
      document.getElementById('deleteUserModal').style.display = 'flex';
    });
  });

  // 2) Cancel button
  document.getElementById('cancelDeleteUser').onclick = ()=>{
    document.getElementById('deleteUserModal').style.display = 'none';
  };

  // 3) “×” icon
  document.getElementById('closeDeleteUserModal').onclick = ()=>{
    document.getElementById('deleteUserModal').style.display = 'none';
  };

  // 4) Click outside
  window.addEventListener('click', e=>{
    if (e.target.id === 'deleteUserModal') {
      document.getElementById('deleteUserModal').style.display = 'none';
    }
  });
});
</script>

<script>
  document.querySelectorAll('.suspend-toggle').forEach(button => {
  button.addEventListener('click', async () => {
    const form        = button.closest('.suspend-form');
    const isSuspended = button.title.startsWith('Unsuspend');
    const action      = isSuspended ? 'Unsuspend' : 'Suspend';

    const result = await Swal.fire({
      title:              `${action} this user?`,
      text:               `Are you sure you want to ${action.toLowerCase()} this user?`,
      icon:               'warning',

      width:              400,
      padding:            '1.5rem',
      background:         '#ffffff',
      iconColor:          '#f8961e',

      showCancelButton:   true,
      confirmButtonText:  action,
      cancelButtonText:   'Cancel',

      buttonsStyling:     false,
      customClass: {
        popup:           'swal2-custom-popup',
        title:           'swal2-custom-title',
        content:         'swal2-custom-content',
        icon:            'swal2-custom-icon',
        confirmButton:   action === 'Suspend' 
                           ? 'btn btn-danger' 
                           : 'btn btn-success',     // green for Unsuspend
        cancelButton:    'btn btn-secondary ml-2'
      }
    });

    if (result.isConfirmed) {
      form.submit();
    }
  });
});
</script>

<?php require 'scripts.php'; ?>
</body>
</html>