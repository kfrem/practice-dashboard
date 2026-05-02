





let clients = [], curLinkId = '', curAmlId = '', curNotesId = '', curTab = 'all';

async function api(action, body = {}) {
  const r = await fetch('api.php?action=' + action, {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin', body: JSON.stringify(body)
  });
  return r.json();
}

function toast(msg, err = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.borderLeftColor = err ? '#f87171' : 'var(--gold)';
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}

async function doLogin() {
  const d = await api('login', { password: document.getElementById('pwdInput').value });
  if (d.success) {
    document.getElementById('loginScreen').style.display = 'none';
    document.getElementById('app').style.display = 'block';
    loadClients();
  } else {
    document.getElementById('loginErr').style.display = 'block';
    document.getElementById('pwdInput').value = '';
  }
}

async function doLogout() { await api('logout'); location.reload(); }

window.addEventListener('load', async () => {
  const d = await api('check');
  if (d.auth) {
    document.getElementById('loginScreen').style.display = 'none';
    document.getElementById('app').style.display = 'block';
    loadClients();
    // Auto-refresh every 60 seconds
    setInterval(loadClients, 60000);
  }
});

async function loadClients() {
  const d = await api('clients');
  if (d.success) { clients = d.clients; renderAll(); }
}

function isOverdue(c) {
  if (c.status === 'signed' || !c.deadline_at) return false;
  return new Date(c.deadline_at) < new Date();
}

// ── Monthly stats ──────────────────────────────────────
function renderMonthlyStats() {
  const now = new Date();
  const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
  const thisMonth = clients.filter(c => new Date(c.created_at) >= monthStart);
  const signedMonth = clients.filter(c => c.signed_at && new Date(c.signed_at) >= monthStart);
  const outstanding = clients.filter(c => c.status !== 'signed').length;

  // Average time to sign (in hours)
  const times = clients
    .filter(c => c.signed_at && c.sent_at)
    .map(c => (new Date(c.signed_at) - new Date(c.sent_at)) / 3600000);
  const avgHrs = times.length ? Math.round(times.reduce((a, b) => a + b, 0) / times.length) : null;
  const avgLabel = avgHrs === null ? 'No data' : avgHrs < 24 ? avgHrs + 'h' : Math.round(avgHrs / 24) + 'd';

  document.getElementById('monthlyStats').innerHTML = `
    <div class="ms"><div class="ms-n">${thisMonth.length}</div><div class="ms-l">Added this month</div></div>
    <div class="ms"><div class="ms-n">${signedMonth.length}</div><div class="ms-l">Signed this month</div></div>
    <div class="ms"><div class="ms-n">${outstanding}</div><div class="ms-l">Outstanding (unsigned)</div></div>
    <div class="ms"><div class="ms-n">${avgLabel}</div><div class="ms-l">Avg. time to sign</div></div>
  `;
}

function renderAll() {
  const overdue = clients.filter(c => isOverdue(c));
  // Show client count badge (only meaningful for provisioned firm instances with a limit)
  const badge = document.getElementById('clientCountBadge');
  if (badge) {
    const active = clients.filter(c => c.status !== 'onboarding').length;
    const limit = typeof CLIENT_LIMIT !== 'undefined' ? CLIENT_LIMIT : 0;
    if (limit > 0) {
      badge.textContent = active + '/' + limit + ' clients';
      badge.style.display = 'inline';
      badge.style.color = active >= limit ? '#c0392b' : (active >= limit * 0.8 ? '#b45309' : '#64748b');
    }
  }
  document.getElementById('stTotal').textContent = clients.length;
  document.getElementById('stSent').textContent = clients.filter(c => c.status === 'sent').length;
  document.getElementById('stSigned').textContent = clients.filter(c => c.status === 'signed').length;
  document.getElementById('stAml').textContent = clients.filter(c => c.aml_status === 'complete').length;
  const bar = document.getElementById('overdueBar');
  if (overdue.length > 0) {
    bar.classList.add('show');
    document.getElementById('overdueCount').textContent = overdue.length;
    document.getElementById('tabOverdue').textContent = '⚠️ Overdue (' + overdue.length + ')';
  } else {
    bar.classList.remove('show');
    document.getElementById('tabOverdue').textContent = '⚠️ Overdue';
  }
  renderMonthlyStats();
  renderTable();
  renderAml();
  // Update deadline tab badge
  const allDl = computeAllDeadlines();
  const today = new Date(); today.setHours(0, 0, 0, 0);
  const overdueCount = allDl.filter(d => d.status !== 'filed' && d.status !== 'paid' && d.dueDate < today).length;
  const dlTab = document.getElementById('tabDeadlines');
  if (dlTab) dlTab.textContent = overdueCount > 0 ? `📅 Deadlines (${overdueCount})` : '📅 Deadlines';
}

function switchTab(tab, el) {
  curTab = tab;
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  if (el) el.classList.add('active');
  const titles = { all: 'All Clients', pending: 'Awaiting Signature', signed: 'Signed Clients', overdue: '⚠️ Overdue — Action Required', mtd: '📊 MTD Tracker', archive: '📁 Signed Document Archive' };
  // Hide all views
  ['viewClients', 'viewAml', 'viewMtd', 'viewDeadlines', 'viewArchive', 'viewKanban'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });
  if (tab === 'aml') {
    document.getElementById('viewAml').style.display = 'block';
  } else if (tab === 'mtd') {
    document.getElementById('viewMtd').style.display = 'block';
    renderMtd();
  } else if (tab === 'deadlines') {
    document.getElementById('viewDeadlines').style.display = 'block';
    renderDeadlines('all');
  } else if (tab === 'archive') {
    document.getElementById('viewArchive').style.display = 'block';
    renderArchive();
  } else if (tab === 'kanban') {
    document.getElementById('viewKanban').style.display = 'block';
    renderKanban();
    document.getElementById('viewArchive').style.display = 'block';
    renderArchive();
  } else {
    document.getElementById('viewClients').style.display = 'block';
    document.getElementById('tabTitle').textContent = titles[tab] || 'Clients';
    renderTable();
  }
}

function renderTable() {
  const q = (document.getElementById('searchBox').value || '').toLowerCase();
  let list = clients.filter(c =>
    c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q) || (c.company || '').toLowerCase().includes(q)
  );
  if (curTab === 'pending') list = list.filter(c => c.status !== 'signed');
  if (curTab === 'signed') list = list.filter(c => c.status === 'signed');
  if (curTab === 'overdue') list = list.filter(c => isOverdue(c));

  const tbody = document.getElementById('clientTbody');
  if (!list.length) {
    const icon = curTab === 'overdue' ? '✅' : '📋';
    const msg = curTab === 'overdue' ? 'No overdue clients — everything is on track' : 'No clients found. Click "+ Add New Client" to get started.';
    tbody.innerHTML = `<tr><td colspan="7"><div class="empty"><div class="empty-icon">${icon}</div><div class="empty-lbl">${msg}</div></div></td></tr>`;
    return;
  }
  tbody.innerHTML = list.map(c => `<tr ${isOverdue(c) ? 'style="background:#fff8f8;"' : ''}>
    <td>
      <div class="c-name">${e(c.name)}
        <button class="notes-pill ${c.notes ? 'has-notes' : ''}" onclick="openNotes('${c.id}')" title="Internal notes">${c.notes ? '📝 Notes' : '+ Note'}</button>
      </div>
      <div class="c-info">${e(c.email)}${c.company ? ' · ' + e(c.company) : ''}${c.fee ? ' · <strong>' + e(c.fee) + '</strong>' : ''}</div>
    </td>
    <td style="font-size:12px">${e(c.type)}</td>
    <td style="font-size:12px">${renderServices(c.service)}</td>
    <td>${sigBadge(c)}</td>
    <td>${deadlineBadge(c)}</td>
    <td>${amlBadge(c.aml_status)}</td>
    <td style="white-space:nowrap">${actions(c)}</td>
  </tr>`).join('');
}

function renderAml() {
  const tbody = document.getElementById('amlTbody');
  if (!clients.length) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty"><div class="empty-icon">🔐</div><div class="empty-lbl">No clients yet.</div></div></td></tr>';
    return;
  }
  tbody.innerHTML = clients.map(c => `<tr>
    <td><div class="c-name">${e(c.name)}</div><div class="c-info">${e(c.company || '')}</div></td>
    <td style="font-size:12px">${e(c.aml_id_type) || '—'}</td>
    <td style="font-family:monospace;font-size:11px">${e(c.aml_id_ref) || '—'}</td>
    <td><span class="badge ${c.aml_risk === 'High' ? 'b-pending' : c.aml_risk === 'Medium' ? 'b-sent' : 'b-signed'}">${e(c.aml_risk) || '—'}</span></td>
    <td style="font-size:12px">${c.aml_verified_date ? new Date(c.aml_verified_date).toLocaleDateString('en-GB') : '—'}</td>
    <td>${amlBadge(c.aml_status)}</td>
    <td><button class="btn btn-outline" onclick="openAml('${c.id}')">${c.aml_status === 'complete' ? 'Edit' : 'Complete'}</button></td>
  </tr>`).join('');
}

function sigBadge(c) {
  if (c.status === 'signed') return `<span class="badge b-signed">✓ Signed<br><span style="font-weight:400;font-size:10px">${new Date(c.signed_at).toLocaleDateString('en-GB')}</span></span>`;
  if (c.status === 'sent') return `<span class="badge b-sent">Link Sent<br><span style="font-weight:400;font-size:10px">${new Date(c.sent_at).toLocaleDateString('en-GB')}</span></span>`;
  return '<span class="badge b-pending">Not Sent</span>';
}
function deadlineBadge(c) {
  if (c.status === 'signed' || !c.deadline_at) return '<span style="color:var(--muted);font-size:11px;">—</span>';
  const dl = new Date(c.deadline_at), diff = dl - new Date();
  if (diff < 0) { const h = Math.round(Math.abs(diff) / 3600000); return `<span class="badge b-overdue">Overdue<br><span style="font-weight:400;font-size:10px">${h > 48 ? Math.round(h / 24) + 'd ago' : h + 'h ago'}</span></span>`; }
  if (diff < 6 * 3600000) return `<span class="badge b-due-soon">Due soon<br><span style="font-weight:400;font-size:10px">${dl.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}</span></span>`;
  return `<span style="font-size:11px;color:var(--muted)">${dl.toLocaleDateString('en-GB')}<br>${dl.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' })}</span>`;
}
function amlBadge(s) {
  return s === 'complete' ? '<span class="badge b-aml-ok">✓ Complete</span>' : '<span class="badge b-aml-p">Pending</span>';
}
function actions(c) {
  let a = '';
  if (c.status === 'signed') {
    a += `<a class="btn btn-navy" href="api.php?action=download_pdf&id=${c.id}" target="_blank" style="margin-right:4px">⬇ PDF</a>`;
  } else {
    a += `<button class="btn btn-gold" onclick="openLink('${c.id}')" style="margin-right:4px">Send Link</button>`;
    if (c.status === 'sent') {
      const n = parseInt(c.reminders_sent || 0);
      const label = n > 0 ? `🔔 Reminder ${n + 1}` : '🔔 Remind';
      a += `<button class="btn ${isOverdue(c) ? 'btn-red' : 'btn-outline'}" onclick="sendReminder('${c.id}')" style="margin-right:4px">${label}</button>`;
      // WhatsApp button if phone exists
      if (c.phone) {
        const phone = c.phone.replace(/\D/g, '');
        const waPhone = phone.startsWith('0') ? '44' + phone.slice(1) : phone;
        const waMsg = encodeURIComponent(`Dear ${c.name}, this is a reminder from The Practice. Could you please sign your engagement letter at your earliest convenience? Thank you.`);
        a += `<a class="btn btn-outline" href="https://wa.me/${waPhone}?text=${waMsg}" target="_blank" style="margin-right:4px" title="WhatsApp reminder">💬</a>`;
      }
    }
  }
  a += `<button class="btn btn-outline" onclick="openAml('${c.id}')" style="margin-right:4px">AML</button>`;
  a += `<button class="btn btn-outline" onclick="openDeadlines('${c.id}')" style="margin-right:4px" title="Deadline tracker">📅</button>`;
  // DPA button
  if (c.dpa_status === 'signed') {
    a += `<span class="badge" style="background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;margin-right:4px;font-size:10px">✓ DPA</span>`;
  } else if (c.dpa_status === 'sent') {
    a += `<span class="badge" style="background:#fef9c3;color:#92400e;border:1px solid #fde68a;margin-right:4px;font-size:10px">DPA Sent</span>`;
  } else {
    a += `<button class="btn btn-outline" onclick="sendDpa('${c.id}')" style="margin-right:4px;font-size:11px" title="Send GDPR Data Processing Agreement">DPA</button>`;
  }
  // Activate onboarding
  if (c.status === 'onboarded') {
    a += `<button class="btn btn-gold" onclick="activateOnboard('${c.id}')" style="margin-right:4px;font-size:11px">✓ Activate</button>`;
  }
  a += `<button class="btn btn-red" onclick="delClient('${c.id}')">✕</button>`;
  return a;
}
function e(s) { return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }

function openM(id) { document.getElementById(id).classList.add('open'); }
function closeM(id) { document.getElementById(id).classList.remove('open'); }

// ── Add Client ──────────────────────────────────
let customSvcs = [];

function addCustomSvc() {
  const inp = document.getElementById('addCustomSvc');
  const val = inp.value.trim();
  if (!val) return;
  if (!customSvcs.includes(val)) { customSvcs.push(val); renderSvcTags(); }
  inp.value = '';
}
function renderSvcTags() {
  document.getElementById('addSvcTags').innerHTML = customSvcs.map((s, i) =>
    `<span class="svc-tag">${e(s)}<button class="svc-tag-x" onclick="customSvcs.splice(${i},1);renderSvcTags()">×</button></span>`
  ).join('');
}
function getSelectedServices() {
  const checked = [...document.querySelectorAll('#addModal input[name="svc"]:checked')].map(el => el.value);
  return [...checked, ...customSvcs];
}
function renderServices(svc) {
  if (!svc) return '—';
  const parts = svc.split('\n').filter(s => s.trim());
  if (!parts.length) return '—';
  if (parts.length === 1) return e(parts[0]);
  return e(parts[0]) + `<span style="color:var(--muted);font-size:11px"> +${parts.length - 1} more</span>`;
}

function openAddClient() {
  ['addName', 'addEmail', 'addCompany', 'addCompanyNumber', 'addAddress', 'addPhone'].forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  const chRes = document.getElementById('chResults'); if (chRes) chRes.style.display = 'none';
  document.querySelectorAll('#addModal input[name="svc"]').forEach(cb => cb.checked = false);
  customSvcs = [];
  renderSvcTags();
  document.getElementById('addCustomSvc').value = '';
  document.getElementById('addAlert').innerHTML = '';
  openM('addModal');
}
async function addClient() {
  const name = document.getElementById('addName').value.trim();
  const email = document.getElementById('addEmail').value.trim();
  if (!name || !email) { document.getElementById('addAlert').innerHTML = '<div class="alert a-err">Name and email are required.</div>'; return; }
  const svcs = getSelectedServices();
  if (!svcs.length) { document.getElementById('addAlert').innerHTML = '<div class="alert a-err">Please select at least one service.</div>'; return; }
  const d = await api('add_client', {
    name, email,
    company: document.getElementById('addCompany').value.trim(),
    company_number: document.getElementById('addCompanyNumber').value.trim(),
    address: document.getElementById('addAddress').value.trim(),
    type: document.getElementById('addType').value,
    service: svcs.join('\n'),
    phone: document.getElementById('addPhone').value.trim()
  });
  if (d.success) { clients.unshift(d.client); renderAll(); closeM('addModal'); toast('Client added'); }
  else document.getElementById('addAlert').innerHTML = `<div class="alert a-err">${d.error}</div>`;
}

// ── Send Link with letter editor ─────────────────
function buildDefaultLetter(c) {
  const today = new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
  const feeLine = c.fee ? `\n\nFEES\nOur agreed fee for the above services is: ${c.fee}\n` : '\n\nFEES\nOur fees will be agreed and confirmed separately in writing.\n';
  const svcParts = (c.service || '').split('\n').filter(s => s.trim());
  const serviceBullets = svcParts.length ? svcParts.map(s => '• ' + s).join('\n') : '• Services to be confirmed';
  const svcSummary = svcParts.length ? svcParts.join(', ') : 'Accountancy Services';
  const clientBlock = [c.name, c.company || '', c.type].filter(Boolean).join('\n');
  return `CLIENT ENGAGEMENT LETTER
────────────────────────────────────────────────────────────────────

${FIRM_NAME}
${FIRM_ADDR}
Tel:     ${FIRM_PHONE}
Email:   ${FIRM_EMAIL}
Web:     ${FIRM_WEBSITE}
ICO Reg: ${FIRM_ICO}

                                                        ${today}

────────────────────────────────────────────────────────────────────

${clientBlock}
[Client Address — please complete before sending]

────────────────────────────────────────────────────────────────────

Dear ${c.name.split(' ')[0]},

Re: Letter of Engagement — ${svcSummary}

Thank you for choosing ${FIRM_NAME}. We are pleased to confirm our appointment as your accountants. This letter sets out the basis on which we will act for you.

SERVICES
We will provide the following services:
${serviceBullets}
${feeLine}
OUR RESPONSIBILITIES
We will carry out the agreed services with reasonable skill, care and diligence, maintain the confidentiality of your affairs and comply with UK GDPR, and communicate clearly on all matters affecting you.

YOUR RESPONSIBILITIES
You agree to provide us with complete, accurate and timely information, inform us promptly of any changes to your circumstances, and pay our fees within the agreed terms.

ANTI-MONEY LAUNDERING (AML)
We are required to comply with the Money Laundering Regulations 2017. We may need to verify your identity and you agree to provide all information we reasonably request.

MAKING TAX DIGITAL (MTD)
Where applicable, we will assist you in meeting your MTD obligations. You remain responsible for the accuracy of all information submitted to HMRC.

DATA PROTECTION
${FIRM_NAME} processes your personal data as Data Controller in accordance with UK GDPR and the Data Protection Act 2018. ICO Registration: ${FIRM_ICO}.

GOVERNING LAW
This engagement is governed by the laws of England and Wales.

Yours sincerely,


___________________________
${FIRM_NAME}
${FIRM_EMAIL} | ${FIRM_PHONE}
${FIRM_WEBSITE}`;
}

// ── Firm Signature ────────────────────────────────
let savedFirmSig = '', fsigCtx = null, fsigDrawing = false, fsigLx = 0, fsigLy = 0, fsigUploadData = '';

function switchFSigTab(tab) {
  ['Saved', 'Draw', 'Upload'].forEach(t => {
    document.getElementById('fsigTab' + t).classList.toggle('active', t.toLowerCase() === tab);
    document.getElementById('fsigPanel' + t).style.display = t.toLowerCase() === tab ? 'block' : 'none';
  });
  if (tab === 'draw') setTimeout(initFSigCanvas, 50);
}
function initFSigCanvas() {
  const canvas = document.getElementById('fsigCanvas');
  if (!canvas || fsigCtx) return;
  const r = canvas.parentElement.getBoundingClientRect();
  const dpr = window.devicePixelRatio || 1;
  canvas.width = r.width * dpr; canvas.height = 110 * dpr;
  canvas.style.width = r.width + 'px'; canvas.style.height = '110px';
  fsigCtx = canvas.getContext('2d');
  fsigCtx.scale(dpr, dpr);
  fsigCtx.strokeStyle = '#0f2238'; fsigCtx.lineWidth = 2.2; fsigCtx.lineCap = 'round'; fsigCtx.lineJoin = 'round';
  canvas.onmousedown = e => { fsigDrawing = true; const r = canvas.getBoundingClientRect(); fsigLx = e.clientX - r.left; fsigLy = e.clientY - r.top; };
  canvas.onmousemove = e => { if (!fsigDrawing) return; const r = canvas.getBoundingClientRect(); const x = e.clientX - r.left, y = e.clientY - r.top; fsigCtx.beginPath(); fsigCtx.moveTo(fsigLx, fsigLy); fsigCtx.lineTo(x, y); fsigCtx.stroke(); fsigLx = x; fsigLy = y; };
  canvas.onmouseup = canvas.onmouseleave = () => fsigDrawing = false;
}
function clearFSig() {
  if (fsigCtx) { const c = document.getElementById('fsigCanvas'); fsigCtx.clearRect(0, 0, c.width, c.height); }
}
function loadFSigFile(input) {
  const file = input.files[0]; if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    fsigUploadData = ev.target.result;
    const prev = document.getElementById('fsigUploadPreview');
    prev.src = fsigUploadData; prev.style.display = 'block';
  };
  reader.readAsDataURL(file);
}
async function saveFirmSig(source) {
  const sigData = source === 'draw'
    ? document.getElementById('fsigCanvas').toDataURL('image/png')
    : fsigUploadData;
  if (!sigData || sigData === 'data:,') { toast('Nothing to save — please draw or upload first'); return; }
  const d = await api('save_firm_sig', { sig: sigData });
  if (d.success) {
    savedFirmSig = sigData;
    document.getElementById('fsigPreview').src = sigData;
    document.getElementById('fsigPreview').style.display = 'block';
    document.getElementById('fsigNoSig').style.display = 'none';
    const okId = source === 'draw' ? 'drawSavedOk' : 'uploadSavedOk';
    const ok = document.getElementById(okId);
    ok.style.display = 'inline'; setTimeout(() => ok.style.display = 'none', 2500);
    switchFSigTab('saved');
    toast('Firm signature saved');
  } else { toast('Save failed — ' + (d.error || 'unknown error')); }
}

function openLink(id) {
  curLinkId = id;
  const c = clients.find(x => x.id === id);
  document.getElementById('linkEmail').textContent = c.email;
  document.getElementById('linkAlert').innerHTML = '';
  document.getElementById('linkFee').value = c.fee || '';
  document.getElementById('linkDeadline').value = '48';
  // Populate letter editor
  const letter = c.custom_letter && c.custom_letter.length > 100
    ? c.custom_letter
    : buildDefaultLetter(c);
  document.getElementById('letterEditor').value = letter;
  // Show step 1
  document.getElementById('linkStep1').style.display = 'block';
  document.getElementById('linkStep2').style.display = 'none';
  document.getElementById('linkFoot').style.display = 'flex';
  document.getElementById('sendBtn').disabled = false;
  document.getElementById('sendBtn').textContent = '📧 Save Letter & Send →';
  // Load firm signature
  switchFSigTab('saved');
  fsigCtx = null; // reset so canvas re-inits on next Draw tab open
  api('get_firm_sig', {}).then(d => {
    savedFirmSig = d.sig || '';
    const prev = document.getElementById('fsigPreview');
    const none = document.getElementById('fsigNoSig');
    if (savedFirmSig) { prev.src = savedFirmSig; prev.style.display = 'block'; none.style.display = 'none'; }
    else { prev.style.display = 'none'; none.style.display = 'block'; }
  });
  openM('linkModal');
}

async function sendLink() {
  document.getElementById('sendBtn').disabled = true;
  document.getElementById('sendBtn').textContent = 'Saving & Sending…';
  const deadlineHours = parseInt(document.getElementById('linkDeadline').value);
  const customLetter = document.getElementById('letterEditor').value.trim();
  const fee = document.getElementById('linkFee').value.trim();

  // Save letter first
  await api('update_letter', { id: curLinkId, custom_letter: customLetter, fee });

  // Then send link
  const d = await api('send_link', { id: curLinkId, deadline_hours: deadlineHours });
  if (d.success) {
    const idx = clients.findIndex(c => c.id === curLinkId);
    clients[idx].status = 'sent';
    clients[idx].sent_at = new Date().toISOString();
    clients[idx].deadline_at = d.deadline;
    clients[idx].deadline_hours = deadlineHours;
    clients[idx].custom_letter = customLetter;
    clients[idx].fee = fee;
    renderAll();
    // Show step 2
    document.getElementById('linkStep1').style.display = 'none';
    document.getElementById('linkStep2').style.display = 'block';
    document.getElementById('linkFoot').style.display = 'none';
    document.getElementById('linkText').textContent = d.link;
    const dl = new Date(d.deadline).toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' });
    document.getElementById('linkDeadlineConfirm').textContent = dl;
    document.getElementById('linkAlert').innerHTML = '';
  } else {
    document.getElementById('linkAlert').innerHTML = `<div class="alert a-err">${d.error || 'Email failed. Check your config.php email settings.'}</div>`;
    document.getElementById('sendBtn').disabled = false;
    document.getElementById('sendBtn').textContent = '📧 Save Letter & Send →';
  }
}

function copyLink() {
  navigator.clipboard.writeText(document.getElementById('linkText').textContent).then(() => {
    const el = document.getElementById('linkCopied');
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 2000);
  });
}

// ── Remind All Overdue ──────────────────────────
async function remindAll() {
  const overdue = clients.filter(c => isOverdue(c));
  if (!overdue.length) { toast('No overdue clients'); return; }
  if (!confirm(`Send reminders to all ${overdue.length} overdue client(s)?`)) return;
  toast('Sending reminders…');
  const d = await api('remind_all');
  if (d.success) {
    await loadClients();
    toast(`Sent ${d.sent} reminder(s)${d.failed ? ', ' + d.failed + ' failed' : ''}`, d.failed > 0);
  } else toast('Error sending reminders', true);
}

// ── Send individual reminder ─────────────────────
async function sendReminder(id) {
  const c = clients.find(x => x.id === id);
  const n = parseInt(c.reminders_sent || 0) + 1;
  if (!confirm(`Send Reminder ${n} to ${c.name} at ${c.email}?`)) return;
  const d = await api('send_reminder', { id });
  if (d.success) {
    const idx = clients.findIndex(x => x.id === id);
    clients[idx].reminders_sent = d.reminder_count;
    clients[idx].last_reminder_at = new Date().toISOString();
    renderAll();
    toast(`Reminder ${d.reminder_count} sent to ${c.name}`);
  } else toast(d.error || 'Could not send reminder', true);
}

// ── Notes ───────────────────────────────────────
function openNotes(id) {
  curNotesId = id;
  const c = clients.find(x => x.id === id);
  document.getElementById('notesClientName').textContent = c.name;
  document.getElementById('notesText').value = c.notes || '';
  document.getElementById('notesAlert').innerHTML = '';
  openM('notesModal');
}
async function saveNotes() {
  const notes = document.getElementById('notesText').value;
  const d = await api('save_notes', { id: curNotesId, notes });
  if (d.success) {
    const idx = clients.findIndex(c => c.id === curNotesId);
    clients[idx].notes = notes;
    renderAll();
    closeM('notesModal');
    toast('Notes saved');
  } else document.getElementById('notesAlert').innerHTML = `<div class="alert a-err">${d.error}</div>`;
}

// ── AML ─────────────────────────────────────────
function openAml(id) {
  curAmlId = id;
  const c = clients.find(x => x.id === id);
  document.getElementById('amlName').textContent = c.name + (c.company ? ' — ' + c.company : '');
  document.getElementById('amlIdType').value = c.aml_id_type || 'UK Passport';
  document.getElementById('amlIdRef').value = c.aml_id_ref || '';
  document.getElementById('amlDate').value = c.aml_verified_date || new Date().toISOString().split('T')[0];
  document.getElementById('amlRisk').value = c.aml_risk || 'Low';
  document.getElementById('amlNotes').value = c.aml_notes || '';
  document.getElementById('amlAlert').innerHTML = '';
  openM('amlModal');
}
async function saveAml() {
  const d = await api('update_aml', {
    id: curAmlId,
    aml_id_type: document.getElementById('amlIdType').value,
    aml_id_ref: document.getElementById('amlIdRef').value.trim(),
    aml_verified_date: document.getElementById('amlDate').value,
    aml_risk: document.getElementById('amlRisk').value,
    aml_notes: document.getElementById('amlNotes').value.trim()
  });
  if (d.success) {
    const idx = clients.findIndex(c => c.id === curAmlId);
    Object.assign(clients[idx], {
      aml_status: 'complete',
      aml_id_type: document.getElementById('amlIdType').value,
      aml_id_ref: document.getElementById('amlIdRef').value,
      aml_verified_date: document.getElementById('amlDate').value,
      aml_risk: document.getElementById('amlRisk').value,
      aml_notes: document.getElementById('amlNotes').value
    });
    renderAll(); closeM('amlModal'); toast('AML record saved');
  } else document.getElementById('amlAlert').innerHTML = `<div class="alert a-err">${d.error}</div>`;
}

// ── Delete ──────────────────────────────────────
async function delClient(id) {
  if (!confirm('Delete this client? This cannot be undone.')) return;
  const d = await api('delete_client', { id });
  if (d.success) { clients = clients.filter(c => c.id !== id); renderAll(); toast('Client removed'); }
}

// ── MTD Tracker ─────────────────────────────────
let curMtdId = '';

function mtdStatusBadge(s, threshold) {
  if (!threshold) return '<span class="badge b-mtd-na">Not set</span>';
  const live = threshold === 'over50k';
  const soon27 = threshold === '30k-50k';
  const soon28 = threshold === '20k-30k';
  if (s === 'compliant') return '<span class="badge b-mtd-ok">✓ Compliant</span>';
  if (s === 'enrolled') return '<span class="badge b-mtd-ok">Enrolled</span>';
  if (s === 'exempt') return '<span class="badge b-mtd-na">Exempt</span>';
  if (s === 'in_progress') return '<span class="badge b-mtd-soon">In Progress</span>';
  if (live) return '<span class="badge b-mtd-live">⚠️ Action Required</span>';
  if (soon27 || soon28) return '<span class="badge b-mtd-soon">Coming Soon</span>';
  return '<span class="badge b-mtd-na">Not in scope</span>';
}

function mtdThresholdLabel(t) {
  const map = {
    'over50k': 'Over £50k — In scope NOW',
    '30k-50k': '£30k–£50k — April 2027',
    '20k-30k': '£20k–£30k — April 2028',
    'under20k': 'Under £20k — Not in scope'
  };
  return map[t] || '—';
}

function renderMtd() {
  const tbody = document.getElementById('mtdTbody');
  if (!clients.length) {
    tbody.innerHTML = '<tr><td colspan="8"><div class="empty"><div class="empty-icon">📊</div><div class="empty-lbl">No clients yet.</div></div></td></tr>';
    return;
  }
  tbody.innerHTML = clients.map(c => `<tr>
    <td><div class="c-name">${e(c.name)}</div><div class="c-info">${e(c.company || '')} · ${e(c.type)}</div></td>
    <td style="font-size:12px">${e(mtdThresholdLabel(c.mtd_threshold || ''))}</td>
    <td>${mtdStatusBadge(c.mtd_status || 'not_started', c.mtd_threshold || '')}</td>
    <td style="font-size:12px">${e(c.mtd_software || '—')}</td>
    <td style="font-size:12px">${c.mtd_enrol_date ? new Date(c.mtd_enrol_date).toLocaleDateString('en-GB') : '—'}</td>
    <td style="font-size:12px">${c.mtd_next_sub ? '<strong>' + new Date(c.mtd_next_sub).toLocaleDateString('en-GB') + '</strong>' : '—'}</td>
    <td style="font-size:11px;color:var(--muted);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${e(c.mtd_notes || '')}">${e(c.mtd_notes || '—')}</td>
    <td><button class="btn btn-outline" onclick="openMtd('${c.id}')">Update</button></td>
  </tr>`).join('');
}

const MTD_REM_IDS = ['mtdRem28', 'mtdRem14', 'mtdRem7', 'mtdRem3', 'mtdRem1', 'mtdRem0'];

function openMtd(id) {
  curMtdId = id;
  const c = clients.find(x => x.id === id);
  document.getElementById('mtdClientName').textContent = c.name;
  document.getElementById('mtdThreshold').value = c.mtd_threshold || '';
  document.getElementById('mtdStatus').value = c.mtd_status || 'not_started';
  document.getElementById('mtdSoftware').value = c.mtd_software || '';
  document.getElementById('mtdEnrolDate').value = c.mtd_enrol_date || '';
  document.getElementById('mtdNextSubmission').value = c.mtd_next_sub || '';
  document.getElementById('mtdNotes').value = c.mtd_notes || '';
  document.getElementById('mtdAlert').innerHTML = '';
  // Load reminder day checkboxes
  const savedDays = Array.isArray(c.mtd_reminder_days) ? c.mtd_reminder_days.map(String) : [];
  MTD_REM_IDS.forEach(rid => {
    const el = document.getElementById(rid);
    el.checked = savedDays.includes(el.value);
  });
  openM('mtdModal');
}

async function saveMtd() {
  const reminderDays = MTD_REM_IDS
    .map(rid => document.getElementById(rid))
    .filter(el => el.checked)
    .map(el => parseInt(el.value));
  const d = await api('save_mtd', {
    id: curMtdId,
    mtd_threshold: document.getElementById('mtdThreshold').value,
    mtd_status: document.getElementById('mtdStatus').value,
    mtd_software: document.getElementById('mtdSoftware').value,
    mtd_enrol_date: document.getElementById('mtdEnrolDate').value,
    mtd_next_sub: document.getElementById('mtdNextSubmission').value,
    mtd_notes: document.getElementById('mtdNotes').value.trim(),
    mtd_reminder_days: reminderDays
  });
  if (d.success) {
    const idx = clients.findIndex(c => c.id === curMtdId);
    Object.assign(clients[idx], {
      mtd_threshold: document.getElementById('mtdThreshold').value,
      mtd_status: document.getElementById('mtdStatus').value,
      mtd_software: document.getElementById('mtdSoftware').value,
      mtd_enrol_date: document.getElementById('mtdEnrolDate').value,
      mtd_next_sub: document.getElementById('mtdNextSubmission').value,
      mtd_notes: document.getElementById('mtdNotes').value,
      mtd_reminder_days: reminderDays
    });
    renderAll();
    closeM('mtdModal');
    toast('MTD record saved');
  } else {
    document.getElementById('mtdAlert').innerHTML = `<div class="alert a-err">${d.error}</div>`;
  }
}

// ── Deadline Tracker ─────────────────────────────
let curDlId = null;
let dlActiveFilter = 'all';

// Add months to a date, return new Date
function addMonths(date, months) {
  const d = new Date(date);
  d.setMonth(d.getMonth() + months);
  return d;
}
function addDays(date, days) {
  const d = new Date(date);
  d.setDate(d.getDate() + days);
  return d;
}
function nextDateForDay(month0, day) {
  // Returns the next occurrence of a given month (0-based) and day
  const now = new Date(); now.setHours(0, 0, 0, 0);
  let d = new Date(now.getFullYear(), month0, day);
  if (d <= now) d.setFullYear(d.getFullYear() + 1);
  return d;
}

// Get upcoming VAT due dates (next 4 quarters) for a given quarter group
function vatDueDates(group) {
  // quarter end months (0-based) per group
  const ends = { jan: [0, 3, 6, 9], feb: [1, 4, 7, 10], mar: [2, 5, 8, 11] };
  if (!ends[group]) return [];
  const today = new Date(); today.setHours(0, 0, 0, 0);
  const results = [];
  const months = ends[group];
  for (let y = today.getFullYear() - 1; y <= today.getFullYear() + 1; y++) {
    for (const m of months) {
      // Due = 1 month + 7 days after quarter end
      const quarterEnd = new Date(y, m + 1, 0); // last day of quarter-end month
      const due = addDays(addMonths(quarterEnd, 1), 7);
      if (due >= today) results.push(due);
    }
  }
  results.sort((a, b) => a - b);
  return results.slice(0, 4); // next 4 quarters
}

// Compute payroll RTI due dates (19th of each month, next 3)
function payrollDueDates() {
  const today = new Date(); today.setHours(0, 0, 0, 0);
  const results = [];
  for (let i = 0; i < 4; i++) {
    const d = new Date(today.getFullYear(), today.getMonth() + i, 19);
    if (d >= today) results.push(d);
    if (results.length >= 3) break;
  }
  return results;
}

// Return next 31 Jan / 31 Jul for SA
function saDueDates() {
  const today = new Date(); today.setHours(0, 0, 0, 0);
  const year = today.getFullYear();
  const dates = [];
  const candidates = [
    new Date(year, 0, 31),   // 31 Jan this year
    new Date(year, 6, 31),   // 31 Jul this year
    new Date(year + 1, 0, 31), // 31 Jan next year
    new Date(year + 1, 6, 31), // 31 Jul next year
  ];
  candidates.forEach(d => { if (d >= today) dates.push(d); });
  return dates.slice(0, 3);
}

// Compute all deadline entries for a single client
function computeClientDeadlines(c) {
  const deadlines = [];
  const statuses = c.dl_statuses || {};
  const today = new Date(); today.setHours(0, 0, 0, 0);

  function entry(type, label, dueDate, keyOverride) {
    const key = keyOverride || (type + ':' + dueDate.toISOString().slice(0, 10));
    const status = statuses[key] || 'pending';
    return { clientId: c.id, clientName: c.name, type, label, dueDate, key, status };
  }

  // Corporation Tax & Companies House (needs year end)
  if (c.dl_year_end) {
    const ye = new Date(c.dl_year_end);
    const ctReturn = addMonths(ye, 12);
    const ctPayment = addDays(addMonths(ye, 9), 1);
    const chAccounts = addMonths(ye, 9);
    deadlines.push(entry('ct_return', 'CT600 — Corporation Tax Return', ctReturn));
    deadlines.push(entry('ct_payment', 'Corporation Tax Payment', ctPayment));
    deadlines.push(entry('ch_accounts', 'Companies House Annual Accounts', chAccounts));
  }

  // Confirmation Statement
  if (c.dl_confirmation_due) {
    const cs = new Date(c.dl_confirmation_due);
    if (cs >= today) {
      deadlines.push(entry('confirmation', 'Confirmation Statement', cs, 'confirmation:' + c.dl_confirmation_due));
    }
    // Also show next year's if this one is close
    const csNext = new Date(cs); csNext.setFullYear(csNext.getFullYear() + 1);
    if (csNext >= today && csNext <= addMonths(today, 14)) {
      deadlines.push(entry('confirmation', 'Confirmation Statement', csNext, 'confirmation:' + csNext.toISOString().slice(0, 10)));
    }
  }

  // VAT
  if (c.dl_vat && c.dl_vat !== 'none') {
    vatDueDates(c.dl_vat).forEach(d => {
      deadlines.push(entry('vat', 'VAT Return & Payment', d));
    });
  }

  // Payroll RTI
  if (c.dl_payroll && c.dl_payroll !== 'none') {
    payrollDueDates().forEach(d => {
      deadlines.push(entry('payroll', 'Payroll RTI Submission', d));
    });
  }

  // Self Assessment
  if (c.dl_self_assessment) {
    saDueDates().forEach((d, i) => {
      const label = (d.getMonth() === 0) ? 'Self Assessment Return + Payment on Account' : 'SA — Second Payment on Account';
      deadlines.push(entry('sa', label, d));
    });
  }

  return deadlines;
}

// Compute all deadlines across all clients
function computeAllDeadlines() {
  let all = [];
  clients.forEach(c => { all = all.concat(computeClientDeadlines(c)); });
  all.sort((a, b) => a.dueDate - b.dueDate);
  return all;
}

function dlDaysLeft(dueDate) {
  const today = new Date(); today.setHours(0, 0, 0, 0);
  return Math.round((dueDate - today) / 86400000);
}

function dlRowClass(d) {
  if (d.status === 'filed' || d.status === 'paid') return 'dl-done';
  const days = dlDaysLeft(d.dueDate);
  if (days < 0) return 'dl-red';
  if (days <= 30) return 'dl-red';
  if (days <= 60) return 'dl-amber';
  return 'dl-green';
}

function dlTypeColour(type) {
  const map = {
    ct_return: '#dbeafe', ct_payment: '#fce7f3', ch_accounts: '#ede9fe',
    confirmation: '#d1fae5', vat: '#fef9c3', payroll: '#ffedd5', sa: '#e0f2fe'
  };
  return map[type] || '#f1f5f9';
}

let dlFilterState = 'all';

function dlFilter(f, el) {
  dlFilterState = f;
  document.querySelectorAll('.dl-filter').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  renderDeadlines(f);
}

function renderDeadlines(filter) {
  filter = filter || dlFilterState;
  const today = new Date(); today.setHours(0, 0, 0, 0);
  let all = computeAllDeadlines();

  // Update overdue badge
  const overdueCount = all.filter(d => d.status !== 'filed' && d.status !== 'paid' && d.dueDate < today).length;
  const oc = document.getElementById('dlCountOverdue');
  if (oc) { oc.textContent = overdueCount; oc.style.display = overdueCount > 0 ? 'inline' : 'none'; }

  // Apply filter
  if (filter === 'overdue') all = all.filter(d => d.status !== 'filed' && d.status !== 'paid' && d.dueDate < today);
  else if (filter === 'soon') all = all.filter(d => d.status !== 'filed' && d.status !== 'paid' && dlDaysLeft(d.dueDate) >= 0 && dlDaysLeft(d.dueDate) <= 30);
  else if (filter === 'upcoming') all = all.filter(d => d.status !== 'filed' && d.status !== 'paid' && dlDaysLeft(d.dueDate) > 30);
  else if (filter === 'done') all = all.filter(d => d.status === 'filed' || d.status === 'paid');

  const tbody = document.getElementById('dlTbody');
  if (!all.length) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="empty"><div class="empty-icon">📅</div><div class="empty-lbl">${filter === 'all' ? 'No deadlines set up yet. Click 📅 on any client row to configure.' : 'No deadlines match this filter.'}</div></div></td></tr>`;
    return;
  }

  tbody.innerHTML = all.map(d => {
    const days = dlDaysLeft(d.dueDate);
    const rc = dlRowClass(d);
    const done = d.status === 'filed' || d.status === 'paid';
    const daysLabel = done ? '—'
      : days < 0 ? `<strong style="color:#991b1b">${Math.abs(days)}d overdue</strong>`
        : days === 0 ? '<strong style="color:#991b1b">Today!</strong>'
          : days === 1 ? '<strong style="color:#c2410c">Tomorrow</strong>'
            : `${days} days`;

    const statusLabel = d.status === 'filed' ? '✓ Filed'
      : d.status === 'paid' ? '✓ Paid'
        : days < 0 ? '⚠️ Overdue' : 'Pending';

    const actionBtns = done
      ? `<button class="btn btn-outline" style="font-size:11px;padding:4px 10px" onclick="markDl('${d.clientId}','${d.key}','pending')">↩ Reopen</button>`
      : `<button class="btn btn-outline" style="font-size:11px;padding:4px 10px;margin-right:4px" onclick="markDl('${d.clientId}','${d.key}','filed')">✓ Filed</button>
         <button class="btn btn-outline" style="font-size:11px;padding:4px 10px" onclick="markDl('${d.clientId}','${d.key}','paid')">£ Paid</button>`;

    return `<tr class="${rc}">
      <td style="font-weight:600">${e(d.clientName)}</td>
      <td><span class="dl-type" style="background:${dlTypeColour(d.type)}">${e(d.label)}</span></td>
      <td>${d.dueDate.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })}</td>
      <td>${daysLabel}</td>
      <td><span style="font-size:12px;font-weight:600">${statusLabel}</span></td>
      <td>${actionBtns}</td>
    </tr>`;
  }).join('');
}

function openDeadlines(id) {
  curDlId = id;
  const c = clients.find(x => x.id === id);
  document.getElementById('dlClientName').textContent = c.name;
  document.getElementById('dlYearEnd').value = c.dl_year_end || '';
  document.getElementById('dlConfirmation').value = c.dl_confirmation_due || '';
  document.getElementById('dlVat').value = c.dl_vat || 'none';
  document.getElementById('dlPayroll').value = c.dl_payroll || 'none';
  document.getElementById('dlSa').checked = !!c.dl_self_assessment;
  document.getElementById('dlAlert').innerHTML = '';
  openM('dlModal');
}

async function saveDeadlines() {
  const payload = {
    id: curDlId,
    dl_year_end: document.getElementById('dlYearEnd').value,
    dl_confirmation_due: document.getElementById('dlConfirmation').value,
    dl_vat: document.getElementById('dlVat').value,
    dl_payroll: document.getElementById('dlPayroll').value,
    dl_self_assessment: document.getElementById('dlSa').checked
  };
  const d = await api('save_deadlines', payload);
  if (d.success) {
    const idx = clients.findIndex(c => c.id === curDlId);
    Object.assign(clients[idx], payload);
    renderAll();
    closeM('dlModal');
    toast('Deadline settings saved');
  } else {
    document.getElementById('dlAlert').innerHTML = `<div class="alert a-err">${d.error}</div>`;
  }
}

async function markDl(clientId, key, status) {
  const d = await api('mark_deadline', { id: clientId, key, status });
  if (d.success) {
    const idx = clients.findIndex(c => c.id === clientId);
    if (!clients[idx].dl_statuses) clients[idx].dl_statuses = {};
    clients[idx].dl_statuses[key] = status;
    renderDeadlines();
    // Update tab badge
    const allDl = computeAllDeadlines();
    const today = new Date(); today.setHours(0, 0, 0, 0);
    const oc = allDl.filter(x => x.status !== 'filed' && x.status !== 'paid' && x.dueDate < today).length;
    const dlTab = document.getElementById('tabDeadlines');
    if (dlTab) dlTab.textContent = oc > 0 ? `📅 Deadlines (${oc})` : '📅 Deadlines';
  }
}

// ── Companies House Lookup ───────────────────────
async function chLookup() {
  const q = document.getElementById('addCompany').value.trim();
  if (q.length < 2) { alert('Enter at least 2 characters to search.'); return; }
  const res = document.getElementById('chResults');
  res.style.display = 'block';
  res.innerHTML = '<div style="padding:12px;color:var(--muted);font-size:13px">Searching Companies House…</div>';
  const d = await api('ch_lookup', { query: q });
  if (!d.ok || !d.results.length) {
    res.innerHTML = '<div style="padding:12px;font-size:13px;color:var(--muted)">' + (d.error || 'No active companies found.') + '</div>';
    return;
  }
  res.innerHTML = d.results.map(r => `
    <div onclick="chSelect(${JSON.stringify(r).replace(/"/g, '&quot;')})"
         style="padding:10px 14px;cursor:pointer;border-bottom:1px solid #f0ede6;font-size:13px"
         onmouseover="this.style.background='#f8f7f4'" onmouseout="this.style.background=''">
      <strong>${e(r.title)}</strong>
      <span style="font-size:11px;color:var(--muted);margin-left:8px">${e(r.company_number)}</span>
      <div style="font-size:11px;color:var(--muted)">${e([r.address_line_1, r.locality, r.postal_code].filter(Boolean).join(', '))}</div>
    </div>
  `).join('');
}

function chSelect(r) {
  document.getElementById('addCompany').value = r.title;
  document.getElementById('addCompanyNumber').value = r.company_number;
  const addr = [r.address_line_1, r.address_line_2, r.locality, r.region, r.postal_code].filter(Boolean).join(', ');
  document.getElementById('addAddress').value = addr;
  // Map CH company type to our entity types
  const typeMap = { 'ltd': 'Ltd Company', 'llp': 'LLP', 'private-unlimited': 'Ltd Company', 'plc': 'Ltd Company' };
  const mapped = typeMap[(r.type || '').toLowerCase()] || '';
  if (mapped) document.getElementById('addType').value = mapped;
  document.getElementById('chResults').style.display = 'none';
}

// ── Onboarding ────────────────────────────────────
async function sendOnboard() {
  const name = document.getElementById('obName').value.trim();
  const email = document.getElementById('obEmail').value.trim();
  if (!name || !email) { document.getElementById('onboardAlert').innerHTML = '<div class="alert a-err">Name and email are required.</div>'; return; }
  document.getElementById('obSendBtn').disabled = true;
  document.getElementById('obSendBtn').textContent = 'Sending…';
  const d = await api('create_onboard', { name, email });
  document.getElementById('obSendBtn').disabled = false;
  document.getElementById('obSendBtn').textContent = 'Send Onboarding Link';
  if (d.success) {
    document.getElementById('onboardAlert').innerHTML = '';
    document.getElementById('obSentEmail').textContent = email;
    document.getElementById('obLinkCopy').value = d.link;
    document.getElementById('onboardLink').style.display = 'block';
    document.getElementById('obName').value = '';
    document.getElementById('obEmail').value = '';
    loadClients();
  } else {
    document.getElementById('onboardAlert').innerHTML = `<div class="alert a-err">${d.error}</div>`;
  }
}

async function activateOnboard(id) {
  if (!confirm('Activate this client? They will move from Onboarded to Pending and be ready for an engagement letter.')) return;
  const d = await api('activate_onboard', { id });
  if (d.success) { await loadClients(); toast('Client activated'); }
  else alert(d.error);
}

// ── DPA ───────────────────────────────────────────
async function sendDpa(id) {
  const c = clients.find(x => x.id === id);
  if (!confirm(`Send a GDPR Data Processing Agreement to ${c.name} (${c.email}) for e-signature?`)) return;
  const d = await api('send_dpa', { id });
  if (d.success) {
    const idx = clients.findIndex(x => x.id === id);
    clients[idx].dpa_status = 'sent';
    renderAll();
    toast('DPA sent to ' + c.email);
  } else {
    alert('Error: ' + d.error);
  }
}

// ── Document Archive ─────────────────────────────
function renderArchive() {
  const tbody = document.getElementById('archiveTbody');
  const signed = clients.filter(c => c.status === 'signed');
  if (!signed.length) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty"><div class="empty-icon">📁</div><div class="empty-lbl">No signed documents yet. Documents will appear here after clients sign.</div></div></td></tr>';
    return;
  }
  tbody.innerHTML = signed
    .sort((a, b) => new Date(b.signed_at) - new Date(a.signed_at))
    .map(c => `<tr>
    <td><div class="c-name">${e(c.name)}</div><div class="c-info">${e(c.email)}${c.company ? ' · ' + e(c.company) : ''}</div></td>
    <td style="font-size:12px">${renderServices(c.service)}</td>
    <td style="font-size:12px">${new Date(c.signed_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
    <td><span class="badge b-signed">${c.sig_method === 'draw' ? '✍️ Drawn' : 'Aa Typed'}</span></td>
    <td style="font-size:11px;color:var(--muted)">${e(c.signed_ip || '—')}</td>
    <td><div class="hash-short" title="${e(c.doc_hash || '')}">${c.doc_hash ? c.doc_hash.substring(0, 16) + '…' : '—'}</div></td>
    <td><a class="btn btn-navy" href="api.php?action=download_pdf&id=${c.id}" target="_blank">⬇ PDF</a></td>
  </tr>`).join('');
}
// ── Kanban Board ────────────────────────────────────
const KANBAN_STAGES = [
  { id: 'awaiting_info', name: 'Awaiting Info' },
  { id: 'in_progress', name: 'In Progress' },
  { id: 'awaiting_approval', name: 'Awaiting Approval' },
  { id: 'filed', name: 'Filed / Done' }
];

function renderKanban() {
  const board = document.getElementById('kanbanBoard');
  if (!board) return;
  clients.forEach(c => {
    if (!c.workflow_stage) c.workflow_stage = 'awaiting_info';
  });

  let html = '';
  KANBAN_STAGES.forEach(st => {
    const stageClients = clients.filter(c => c.workflow_stage === st.id);
    html += `
      <div class="kanban-col">
        <div class="kanban-col-head">
          <span>${st.name}</span> <span class="kanban-col-count">${stageClients.length}</span>
        </div>
        <div class="kanban-dropzone" ondragover="allowDrop(event)" ondrop="dropKanban(event, '${st.id}')">
          ${stageClients.map(c => `
            <div class="kanban-card" draggable="true" ondragstart="dragKanban(event, '${c.id}')">
              <h4>${e(c.name)}</h4>
              <p>${e(c.company || c.type)}</p>
              <div class="kanban-card-ft">
                <span class="k-tag">${c.service ? c.service.split('\\n')[0] : 'None'}</span>
                <span>${deadlineBadge(c)}</span>
              </div>
            </div>
          `).join('')}
        </div>
      </div>
    `;
  });
  board.innerHTML = html;
}

function dragKanban(e, id) { e.dataTransfer.setData('text/plain', id); }
function allowDrop(e) { e.preventDefault(); e.currentTarget.classList.add('drag-over'); }
async function dropKanban(e, stageId) {
  e.preventDefault();
  document.querySelectorAll('.kanban-dropzone').forEach(d => d.classList.remove('drag-over'));
  const id = e.dataTransfer.getData('text/plain');
  if (!id) return;
  const c = clients.find(x => x.id === id);
  if (c && c.workflow_stage !== stageId) {
    c.workflow_stage = stageId;
    renderKanban();
    const res = await api('update_workflow', { id, stage: stageId });
    if (!res.success) { toast('Failed to move client', true); loadClients(); }
  }
}