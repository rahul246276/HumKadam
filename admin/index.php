<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once __DIR__ . '/../config/database.php';
$conn = getDBConnection();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Stats
$total   = $conn->query("SELECT COUNT(*) AS c FROM contact_submissions")->fetch_assoc()['c'];
$new_c   = $conn->query("SELECT COUNT(*) AS c FROM contact_submissions WHERE status='new'")->fetch_assoc()['c'];
$read_c  = $conn->query("SELECT COUNT(*) AS c FROM contact_submissions WHERE status='read'")->fetch_assoc()['c'];
$resp_c  = $conn->query("SELECT COUNT(*) AS c FROM contact_submissions WHERE status='responded'")->fetch_assoc()['c'];

// Flash message
$flash = null;
if (!empty($_GET['message'])) $flash = ['type'=>'success','text'=>htmlspecialchars($_GET['message'])];
if (!empty($_GET['error']))   $flash = ['type'=>'error',  'text'=>htmlspecialchars($_GET['error'])];

$contacts_result = $conn->query("SELECT * FROM contact_submissions ORDER BY submission_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HumKadam Admin Panel</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --maroon: #5a1220; --maroon-light: #7b1c2e; --maroon-dark: #3d0c18;
      --gold: #c9973a; --gold-dark: #a67c2e;
      --beige: #f5ede0; --beige-dark: #ede0cc;
      --white: #fff;
      --text-dark: #1a1a2e; --text-mid: #555; --text-light: #888;
      --green: #2e7d32; --green-light: #e8f5e9;
      --blue: #1565c0; --blue-light: #e3f2fd;
      --orange: #e65100; --orange-light: #fff3e0;
      --red: #c62828; --red-light: #ffebee;
      --shadow: 0 4px 20px rgba(0,0,0,0.08);
      --shadow-lg: 0 8px 40px rgba(0,0,0,0.14);
      --radius: 10px; --radius-lg: 16px;
    }
    body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0e8dc; color: var(--text-dark); min-height: 100vh; }

    /* TOPBAR */
    .topbar {
      background: linear-gradient(135deg, var(--maroon-dark), var(--maroon-light));
      color: #fff; padding: 0 32px;
      display: flex; align-items: center; justify-content: space-between;
      height: 64px; position: sticky; top: 0; z-index: 100;
      box-shadow: 0 2px 12px rgba(0,0,0,0.2);
    }
    .topbar-brand { display: flex; align-items: center; gap: 12px; font-size: 1.25rem; font-weight: 700; }
    .topbar-brand i { color: var(--gold); font-size: 1.4rem; }
    .topbar-actions { display: flex; align-items: center; gap: 16px; }
    .topbar-user { font-size: 0.9rem; opacity: 0.85; }
    .topbar-user strong { color: var(--gold); }
    .btn-logout {
      background: rgba(255,255,255,0.12); color: #fff; border: 1px solid rgba(255,255,255,0.25);
      padding: 7px 16px; border-radius: 6px; cursor: pointer; font-size: 0.88rem;
      text-decoration: none; display: flex; align-items: center; gap: 6px; transition: background 0.2s;
    }
    .btn-logout:hover { background: rgba(255,255,255,0.22); }

    /* LAYOUT */
    .page { max-width: 1400px; margin: 0 auto; padding: 32px 24px; }

    /* FLASH */
    .flash { padding: 14px 20px; border-radius: var(--radius); margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 10px; }
    .flash.success { background: var(--green-light); color: var(--green); border-left: 4px solid var(--green); }
    .flash.error   { background: var(--red-light); color: var(--red); border-left: 4px solid var(--red); }

    /* STATS */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 18px; margin-bottom: 28px; }
    .stat-card {
      background: var(--white); border-radius: var(--radius-lg); padding: 24px 20px;
      box-shadow: var(--shadow); display: flex; align-items: center; gap: 16px;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
    .stat-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
    .stat-icon.total   { background: #fce4ec; color: var(--maroon); }
    .stat-icon.new-c   { background: var(--blue-light); color: var(--blue); }
    .stat-icon.read-c  { background: var(--green-light); color: var(--green); }
    .stat-icon.resp-c  { background: var(--orange-light); color: var(--orange); }
    .stat-num { font-size: 1.9rem; font-weight: 800; color: var(--maroon); line-height: 1; }
    .stat-lbl { font-size: 0.78rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.06em; margin-top: 3px; }

    /* PANEL */
    .panel { background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow); padding: 26px; margin-bottom: 24px; }
    .panel-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
    .panel-title { font-size: 1.15rem; font-weight: 700; color: var(--maroon); display: flex; align-items: center; gap: 8px; }

    /* TOOLBAR */
    .toolbar { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 18px; }
    .search-wrap { position: relative; flex: 1; min-width: 200px; }
    .search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-light); }
    .search-input {
      width: 100%; padding: 10px 12px 10px 36px; border: 2px solid var(--beige-dark);
      border-radius: 8px; font-size: 0.93rem; outline: none; transition: border-color 0.2s;
    }
    .search-input:focus { border-color: var(--gold); }
    .filter-tabs { display: flex; gap: 4px; background: var(--beige); border-radius: 8px; padding: 4px; flex-wrap: wrap; }
    .tab-btn {
      padding: 7px 13px; border: none; background: none; border-radius: 6px;
      cursor: pointer; font-size: 0.85rem; font-weight: 600; color: var(--text-mid);
      transition: all 0.15s; display: flex; align-items: center; gap: 5px;
    }
    .tab-btn.active { background: var(--maroon); color: #fff; }
    .tab-btn:hover:not(.active) { background: var(--beige-dark); }
    .cnt { background: var(--gold); color: #fff; border-radius: 10px; padding: 1px 7px; font-size: 0.72rem; }
    .tab-btn.active .cnt { background: rgba(255,255,255,0.28); }

    /* BUTTONS */
    .action-group { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn {
      display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
      border-radius: 7px; font-size: 0.86rem; font-weight: 600; cursor: pointer;
      border: none; text-decoration: none; transition: all 0.15s;
    }
    .btn-export    { background: var(--green); color: #fff; }
    .btn-export:hover { filter: brightness(0.88); }
    .btn-bulk-del  { background: var(--red-light); color: var(--red); border: 1px solid #e57373; }
    .btn-bulk-del:hover { background: var(--red); color: #fff; }

    /* TABLE */
    .table-wrap { overflow-x: auto; border-radius: var(--radius); border: 1px solid var(--beige-dark); }
    table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    thead th {
      background: var(--maroon); color: #fff; padding: 12px 14px; text-align: left;
      font-size: 0.77rem; text-transform: uppercase; letter-spacing: 0.06em; white-space: nowrap;
    }
    tbody tr { border-bottom: 1px solid var(--beige); transition: background 0.1s; }
    tbody tr:hover { background: #fdf6f0; }
    tbody tr.status-new { border-left: 3px solid var(--blue); }
    tbody td { padding: 12px 14px; vertical-align: middle; }
    .td-name  { font-weight: 600; color: var(--maroon-dark); }
    .td-email a { color: var(--blue); text-decoration: none; font-size: 0.86rem; }
    .td-phone a { color: var(--text-dark); text-decoration: none; font-size: 0.86rem; }
    .td-service { font-size: 0.82rem; }
    .td-date  { font-size: 0.8rem; color: var(--text-light); white-space: nowrap; }

    /* STATUS BADGE */
    .badge-status { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .badge-new       { background: var(--blue-light); color: var(--blue); }
    .badge-read      { background: var(--green-light); color: var(--green); }
    .badge-responded { background: var(--orange-light); color: var(--orange); }

    /* ROW ACTIONS */
    .row-actions { display: flex; gap: 5px; align-items: center; }
    .act-btn {
      width: 30px; height: 30px; border-radius: 6px; border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center; font-size: 0.8rem;
      transition: all 0.15s; flex-shrink: 0;
    }
    .act-view      { background: var(--gold); color: #fff; }
    .act-view:hover { background: var(--gold-dark); }
    .act-read      { background: var(--green-light); color: var(--green); border: 1px solid #81c784; }
    .act-read:hover { background: var(--green); color: #fff; }
    .act-responded { background: var(--orange-light); color: var(--orange); border: 1px solid #ffb74d; }
    .act-responded:hover { background: var(--orange); color: #fff; }
    .act-del       { background: var(--red-light); color: var(--red); border: 1px solid #e57373; }
    .act-del:hover { background: var(--red); color: #fff; }

    /* EMPTY STATE */
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-light); }
    .empty-state i { font-size: 2.8rem; margin-bottom: 14px; opacity: 0.3; display: block; }

    /* MODAL */
    .modal-backdrop {
      position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 1000;
      display: flex; align-items: center; justify-content: center; padding: 20px;
      animation: fadeIn 0.15s ease;
    }
    @keyframes fadeIn { from { opacity:0 } to { opacity:1 } }
    .modal {
      background: var(--white); border-radius: var(--radius-lg); max-width: 560px; width: 100%;
      box-shadow: var(--shadow-lg); overflow: hidden; animation: slideUp 0.2s ease;
    }
    @keyframes slideUp { from { transform:translateY(20px);opacity:0 } to { transform:translateY(0);opacity:1 } }
    .modal-header {
      background: linear-gradient(135deg, var(--maroon-dark), var(--maroon-light));
      color: #fff; padding: 18px 22px; display: flex; align-items: center; justify-content: space-between;
    }
    .modal-header h3 { font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .modal-close { background: rgba(255,255,255,0.15); border: none; color: #fff; width: 30px; height: 30px; border-radius: 6px; cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center; }
    .modal-close:hover { background: rgba(255,255,255,0.28); }
    .modal-body { padding: 22px; max-height: 70vh; overflow-y: auto; }
    .detail-row { display: flex; gap: 12px; margin-bottom: 14px; align-items: flex-start; }
    .detail-icon { width: 30px; height: 30px; border-radius: 6px; background: var(--beige); display: flex; align-items: center; justify-content: center; color: var(--maroon); font-size: 0.8rem; flex-shrink: 0; margin-top: 2px; }
    .detail-label { font-size: 0.72rem; text-transform: uppercase; color: var(--text-light); letter-spacing: 0.05em; margin-bottom: 2px; }
    .detail-value { font-size: 0.93rem; }
    .detail-value a { color: var(--blue); text-decoration: none; }
    .msg-box { background: var(--beige); border-radius: 8px; padding: 14px; line-height: 1.75; font-size: 0.9rem; white-space: pre-wrap; word-break: break-word; margin-top: 8px; }
    .modal-footer { padding: 14px 22px; border-top: 1px solid var(--beige-dark); display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap; }
    .btn-sm { padding: 7px 14px; font-size: 0.82rem; }
    .btn-mark-read-m  { background: var(--green); color: #fff; }
    .btn-mark-read-m:hover { filter: brightness(0.88); }
    .btn-mark-resp-m  { background: var(--orange); color: #fff; }
    .btn-mark-resp-m:hover { filter: brightness(0.88); }
    .btn-del-m  { background: var(--red); color: #fff; }
    .btn-del-m:hover { filter: brightness(0.88); }
    .btn-close-m { background: var(--beige-dark); color: var(--text-mid); }
    .btn-close-m:hover { filter: brightness(0.93); }

    /* TOOLTIP */
    [title] { cursor: pointer; }

    @media (max-width: 680px) {
      .page { padding: 14px 10px; }
      .topbar { padding: 0 14px; }
      .panel { padding: 14px; }
      .stats-grid { grid-template-columns: 1fr 1fr; }
      .topbar-user { display: none; }
    }
  </style>
</head>
<body>

<div class="topbar">
  <div class="topbar-brand">
    <i class="fas fa-crown"></i>
    <span>HumKadam Admin</span>
  </div>
  <div class="topbar-actions">
    <span class="topbar-user">Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></strong></span>
    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</div>

<div class="page">

  <?php if ($flash): ?>
    <div class="flash <?php echo $flash['type']; ?>">
      <i class="fas fa-<?php echo $flash['type']==='success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
      <?php echo $flash['text']; ?>
    </div>
  <?php endif; ?>

  <!-- STATS -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon total"><i class="fas fa-users"></i></div>
      <div><div class="stat-num"><?php echo $total; ?></div><div class="stat-lbl">Total Contacts</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon new-c"><i class="fas fa-envelope"></i></div>
      <div><div class="stat-num"><?php echo $new_c; ?></div><div class="stat-lbl">New</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon read-c"><i class="fas fa-envelope-open"></i></div>
      <div><div class="stat-num"><?php echo $read_c; ?></div><div class="stat-lbl">Read</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon resp-c"><i class="fas fa-reply"></i></div>
      <div><div class="stat-num"><?php echo $resp_c; ?></div><div class="stat-lbl">Responded</div></div>
    </div>
  </div>

  <!-- CONTACTS TABLE -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title"><i class="fas fa-address-book"></i> Contact Submissions</div>
      <div class="action-group">
        <button class="btn btn-bulk-del" id="bulkDelBtn" onclick="bulkDelete()" style="display:none">
          <i class="fas fa-trash"></i> Delete Selected (<span id="selCount">0</span>)
        </button>
        <a href="export-contacts.php" class="btn btn-export">
          <i class="fas fa-file-csv"></i> Export CSV
        </a>
      </div>
    </div>

    <div class="toolbar">
      <div class="search-wrap">
        <i class="fas fa-search"></i>
        <input type="text" class="search-input" id="searchInput" placeholder="Search name, email, phone, service…" oninput="applyFilters()">
      </div>
      <div class="filter-tabs">
        <button class="tab-btn active" onclick="setTab(this,'all')">
          <i class="fas fa-list"></i> All <span class="cnt"><?php echo $total; ?></span>
        </button>
        <button class="tab-btn" onclick="setTab(this,'new')">
          <i class="fas fa-circle" style="font-size:.55rem"></i> New <span class="cnt"><?php echo $new_c; ?></span>
        </button>
        <button class="tab-btn" onclick="setTab(this,'read')">
          <i class="fas fa-check"></i> Read <span class="cnt"><?php echo $read_c; ?></span>
        </button>
        <button class="tab-btn" onclick="setTab(this,'responded')">
          <i class="fas fa-reply"></i> Responded <span class="cnt"><?php echo $resp_c; ?></span>
        </button>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th style="width:36px"><input type="checkbox" id="checkAll" onchange="toggleAll(this)" style="cursor:pointer;accent-color:#fff;width:15px;height:15px"></th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Service</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <?php while ($c = $contacts_result->fetch_assoc()): ?>
          <tr class="contact-row status-<?php echo $c['status']; ?>"
              data-status="<?php echo $c['status']; ?>"
              data-id="<?php echo (int)$c['id']; ?>"
              data-name="<?php echo htmlspecialchars(strtolower($c['name'])); ?>"
              data-email="<?php echo htmlspecialchars(strtolower($c['email'])); ?>"
              data-phone="<?php echo htmlspecialchars($c['phone']); ?>"
              data-service="<?php echo htmlspecialchars(strtolower($c['service_type'] ?? '')); ?>"
              data-message="<?php echo htmlspecialchars($c['message']); ?>"
              data-date="<?php echo date('M j, Y g:i A', strtotime($c['submission_date'])); ?>">
            <td><input type="checkbox" class="cb-row" onchange="updateBulk()"></td>
            <td class="td-name"><?php echo htmlspecialchars($c['name']); ?></td>
            <td class="td-email"><a href="mailto:<?php echo htmlspecialchars($c['email']); ?>"><?php echo htmlspecialchars($c['email']); ?></a></td>
            <td class="td-phone"><a href="tel:<?php echo htmlspecialchars($c['phone']); ?>"><?php echo htmlspecialchars($c['phone']); ?></a></td>
            <td class="td-service"><?php echo htmlspecialchars(ucfirst($c['service_type'] ?? '—')); ?></td>
            <td class="td-date"><?php echo date('M j, Y', strtotime($c['submission_date'])); ?></td>
            <td><span class="badge-status badge-<?php echo $c['status']; ?>"><?php echo ucfirst($c['status']); ?></span></td>
            <td>
              <div class="row-actions">
                <button class="act-btn act-view" onclick="openModal(<?php echo (int)$c['id']; ?>)" title="View full message"><i class="fas fa-eye"></i></button>
                <?php if ($c['status'] === 'new'): ?>
                  <button class="act-btn act-read" onclick="changeStatus(<?php echo (int)$c['id']; ?>,'read')" title="Mark as read"><i class="fas fa-envelope-open"></i></button>
                <?php endif; ?>
                <?php if ($c['status'] !== 'responded'): ?>
                  <button class="act-btn act-responded" onclick="changeStatus(<?php echo (int)$c['id']; ?>,'responded')" title="Mark as responded"><i class="fas fa-reply"></i></button>
                <?php endif; ?>
                <button class="act-btn act-del" onclick="deleteOne(<?php echo (int)$c['id']; ?>)" title="Delete"><i class="fas fa-trash"></i></button>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <div class="empty-state" id="emptyState" style="display:none">
        <i class="fas fa-inbox"></i>
        <p>No contacts match your filter.</p>
      </div>
    </div>
  </div>

</div>

<!-- MESSAGE MODAL -->
<div class="modal-backdrop" id="modalBackdrop" style="display:none" onclick="if(event.target===this)closeModal()">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-envelope-open-text"></i> Contact Details</h3>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body" id="modalBody"></div>
    <div class="modal-footer" id="modalFooter"></div>
  </div>
</div>

<script>
let currentFilter = 'all';

function setTab(btn, filter) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  currentFilter = filter;
  applyFilters();
}

function applyFilters() {
  const q = document.getElementById('searchInput').value.toLowerCase().trim();
  const rows = document.querySelectorAll('.contact-row');
  let visible = 0;
  rows.forEach(row => {
    const statusOk = currentFilter === 'all' || row.dataset.status === currentFilter;
    const searchOk = !q || [row.dataset.name, row.dataset.email, row.dataset.phone, row.dataset.service].some(v => v.includes(q));
    const show = statusOk && searchOk;
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  document.getElementById('emptyState').style.display = visible === 0 ? 'block' : 'none';
}

function toggleAll(master) {
  document.querySelectorAll('.cb-row').forEach(cb => {
    if (cb.closest('tr').style.display !== 'none') cb.checked = master.checked;
  });
  updateBulk();
}

function updateBulk() {
  const n = document.querySelectorAll('.cb-row:checked').length;
  document.getElementById('selCount').textContent = n;
  document.getElementById('bulkDelBtn').style.display = n > 0 ? '' : 'none';
}

function bulkDelete() {
  const ids = [...document.querySelectorAll('.cb-row:checked')].map(cb => cb.closest('tr').dataset.id);
  if (!ids.length || !confirm(`Permanently delete ${ids.length} contact(s)?`)) return;
  Promise.all(ids.map(id => fetch(`delete-contact.php?id=${id}&ajax=1`)))
    .then(() => location.href = `index.php?message=${ids.length}+contact(s)+deleted`);
}

function changeStatus(id, status) {
  fetch(`mark-read.php?id=${id}&status=${status}&ajax=1`)
    .then(() => location.href = `index.php?message=Marked+as+${status}`);
}

function deleteOne(id) {
  if (!confirm('Permanently delete this contact?')) return;
  location.href = `delete-contact.php?id=${id}`;
}

function openModal(id) {
  const row = document.querySelector(`.contact-row[data-id="${id}"]`);
  if (!row) return;

  const name    = row.querySelector('.td-name').textContent.trim();
  const email   = row.querySelector('.td-email a').textContent.trim();
  const phone   = row.querySelector('.td-phone a').textContent.trim();
  const service = row.querySelector('.td-service').textContent.trim();
  const date    = row.dataset.date;
  const message = row.dataset.message;
  const status  = row.dataset.status;

  document.getElementById('modalBody').innerHTML = `
    <div class="detail-row">
      <div class="detail-icon"><i class="fas fa-user"></i></div>
      <div><div class="detail-label">Full Name</div><div class="detail-value">${esc(name)}</div></div>
    </div>
    <div class="detail-row">
      <div class="detail-icon"><i class="fas fa-envelope"></i></div>
      <div><div class="detail-label">Email</div><div class="detail-value"><a href="mailto:${esc(email)}">${esc(email)}</a></div></div>
    </div>
    <div class="detail-row">
      <div class="detail-icon"><i class="fas fa-phone"></i></div>
      <div><div class="detail-label">Phone</div><div class="detail-value"><a href="tel:${esc(phone)}">${esc(phone)}</a></div></div>
    </div>
    <div class="detail-row">
      <div class="detail-icon"><i class="fas fa-tag"></i></div>
      <div><div class="detail-label">Service Requested</div><div class="detail-value">${esc(service)}</div></div>
    </div>
    <div class="detail-row">
      <div class="detail-icon"><i class="fas fa-calendar-alt"></i></div>
      <div><div class="detail-label">Submitted</div><div class="detail-value">${esc(date)}</div></div>
    </div>
    <div>
      <div class="detail-label" style="margin-bottom:6px">Message</div>
      <div class="msg-box">${esc(message || '(no message provided)')}</div>
    </div>
  `;

  let btns = '';
  if (status === 'new')
    btns += `<button class="btn btn-sm btn-mark-read-m" onclick="changeStatus(${id},'read')"><i class="fas fa-envelope-open"></i> Mark Read</button>`;
  if (status !== 'responded')
    btns += `<button class="btn btn-sm btn-mark-resp-m" onclick="changeStatus(${id},'responded')"><i class="fas fa-reply"></i> Mark Responded</button>`;
  btns += `<button class="btn btn-sm btn-del-m" onclick="closeModal();deleteOne(${id})"><i class="fas fa-trash"></i> Delete</button>`;
  btns += `<button class="btn btn-sm btn-close-m" onclick="closeModal()">Close</button>`;
  document.getElementById('modalFooter').innerHTML = btns;

  document.getElementById('modalBackdrop').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}

function closeModal() {
  document.getElementById('modalBackdrop').style.display = 'none';
  document.body.style.overflow = '';
}

function esc(str) {
  const d = document.createElement('div');
  d.textContent = String(str);
  return d.innerHTML;
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>

<?php $conn->close(); ?>
</body>
</html>