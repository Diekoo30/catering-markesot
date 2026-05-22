/* ═══════════════════════════════════════
   SHARED DATA
═══════════════════════════════════════ */
const MENUS = window.APP_MENUS || [];
// 3 Kriteria AHP Final: Rasa | Nutrisi | Jenis Hidangan
const CRITERIA=[
  {id:'rasa',          name:'Rasa',           icon:'😋',desc:'Kekuatan rasa masakan (bumbu pekat & pedas gurih vs ringan)'},
  {id:'nutrisi',       name:'Nutrisi',         icon:'🥩',desc:'Kelengkapan gizi (kandungan protein & serat sehat)'},
  {id:'jenis_hidangan',name:'Jenis Hidangan',  icon:'🍲',desc:'Tipe penyajian makanan (berkuah segar vs kering/goreng)'},
];
// 3 pasang VS: C(3,2) = 3
const PAIRS=[
  {key:'rasa_vs_nutrisi',  i:0,j:1},  // Rasa    vs Nutrisi
  {key:'rasa_vs_jenis',    i:0,j:2},  // Rasa    vs Jenis Hidangan
  {key:'nutrisi_vs_jenis', i:1,j:2},  // Nutrisi vs Jenis Hidangan
];
const fmt=n=>'Rp.'+new Intl.NumberFormat('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2}).format(n);

/* ═══════════════════════════════════════
   ORDER SYSTEM
═══════════════════════════════════════ */
const DP_PCT = window.DP_PCT || 50;
let qty={},oStep=1,payMethod=null,uploaded=null;
MENUS.forEach(m=>qty[m.id]=0);
const storedQty = localStorage.getItem('mk_cart_qty');
if (storedQty) {
  try { Object.assign(qty, JSON.parse(storedQty)); } catch(e){}
}

function initAutoOpen() {
  if(localStorage.getItem('mk_auto_open') === '1') {
     localStorage.removeItem('mk_auto_open');
     setTimeout(openOrder, 500); 
  }
}
if (document.readyState === 'loading') {
  window.addEventListener('DOMContentLoaded', initAutoOpen);
} else {
  initAutoOpen();
}

const oTotal=()=>MENUS.reduce((s,m)=>s+m.price*(qty[m.id]||0),0);
const oDp=()=>Math.round(oTotal()*DP_PCT/100);

function openOrder(){
  oStep=1;payMethod=null;uploaded=null;
  cName = window.USER_NAME || '';
  cPhone = window.USER_PHONE || '';
  cAddress = window.USER_ADDRESS || '';
  cEmail = window.USER_EMAIL || '';
  document.getElementById('orderOverlay').classList.add('open');
  document.body.style.overflow='hidden';
  renderOrder();
}
function closeOrder(){
  document.getElementById('orderOverlay').classList.remove('open');
  document.body.style.overflow='';
}

function renderOrder(){
  updateOrderSteps();
  const b=document.getElementById('orderBody');
  if(oStep===1) {
    b.innerHTML=oS1();
  } else if(oStep===2) {
    b.innerHTML=oS2();
    if (document.getElementById('custDate') && window.flatpickr) {
        flatpickr('#custDate', {
            enableTime: true,
            time_24hr: true,
            altInput: true,
            altFormat: "d/m/Y H:i",
            dateFormat: "Y-m-d\\TH:i",
            locale: "id",
            minDate: document.getElementById('custDate').getAttribute('min'),
            maxDate: document.getElementById('custDate').getAttribute('max'),
            onChange: function(selectedDates, dateStr) {
                cDate = dateStr;
                checkData();
            }
        });
    }
    if (payMethod === 'bank') {
      if (bankInfoCache) {
        setTimeout(updateBankDOM, 10);
      } else {
        fetch('/bank-info')
          .then(r => r.json())
          .then(res => {
            bankInfoCache = res;
            updateBankDOM();
          });
      }
    }
  } else {
    b.innerHTML=oS3();
  }
  setTimeout(animW,80);
}

function updateOrderSteps(){
  const labels=['Menu','Bayar','Selesai'];
  let h='';
  labels.forEach((l,i)=>{
    const n=i+1,cls=n<oStep?'done':n===oStep?'active':'';
    h+=`<div class="step-pill ${cls}"><div class="step-dot">${n<oStep?'✓':n}</div><span>${l}</span></div>`;
    if(i<2)h+=`<div class="step-line ${n<oStep?'done':''}"></div>`;
  });
  document.getElementById('orderStepsRow').innerHTML=h;
  const titles={1:'Pilih Menu',2:'Pembayaran',3:'Pesanan Diterima!'};
  document.getElementById('orderTitle').textContent=titles[oStep];
}

let itemNotes = {};
const storedNotes = localStorage.getItem('mk_cart_notes');
if (storedNotes) { try { Object.assign(itemNotes, JSON.parse(storedNotes)); } catch(e){} }

function oS1(){
  const ordered = MENUS.filter(m => qty[m.id] > 0);
  const t = oTotal(), has = t > 0;

  let h = '';

  if (!has) {
    h += `<div style="text-align:center;padding:2.5rem 1rem;">
      <div style="margin-bottom:0.8rem;color:var(--maroon);"><svg style="width:64px;height:64px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg></div>
      <div style="font-weight:700;font-size:1.1rem;color:#333;margin-bottom:0.4rem;">Keranjang Masih Kosong</div>
      <div style="font-size:0.85rem;color:#888;line-height:1.5;margin-bottom:1.5rem;">Pilih menu pada halaman utama terlebih dahulu, lalu kembali ke sini untuk melanjutkan pesanan.</div>
      <button class="btn-primary" onclick="closeOrder(); setTimeout(() => document.getElementById('menu')?.scrollIntoView({behavior:'smooth'}), 100);" style="width:100%;">Lihat Menu Kami</button>
    </div>`;
  } else {
    // Group ordered items by category
    const cats = {};
    ordered.forEach(m => {
      const c = m.category_name || (m.cat === 'drink' ? 'Minuman' : 'Makanan');
      if(!cats[c]) cats[c] = [];
      cats[c].push(m);
    });

    for (const [catName, items] of Object.entries(cats)) {
      h += `<div class="menu-cat-label"><span style="font-weight:700;">${catName}</span></div>`;
      items.forEach(m => h += mRow(m));
    }

    h += `<div class="order-box"><div style="font-weight:700;font-size:0.9rem;margin-bottom:0.6rem;color:#333;">Ringkasan Pesanan</div>`;
    ordered.forEach(m => {
      const note = itemNotes[m.id] || '';
      h += `<div style="border-bottom:1px solid #f0f0f0;padding:0.6rem 0;">
        <div class="orow"><span>${m.name} ×${qty[m.id]}</span><span>${fmt(m.price*qty[m.id])}</span></div>
        <input type="text" placeholder="Catatan: pedas, tanpa sayur, dll." value="${note}" 
          oninput="itemNotes[${m.id}]=this.value;localStorage.setItem('mk_cart_notes',JSON.stringify(itemNotes))" 
          style="width:100%;border:1px solid #e8e8e8;border-radius:8px;padding:0.4rem 0.6rem;font-size:0.78rem;margin-top:0.4rem;color:#555;outline:none;box-sizing:border-box;"
        >
      </div>`;
    });
    h += `<div class="orow orow-total"><span>Total</span><span>${fmt(t)}</span></div></div>`;
  }

  if (has) {
    h += `<button class="btn-primary" onclick="oGoStep(2)">Lanjut ke Pembayaran</button>`;
    h += `<button class="btn-ghost" onclick="closeOrder()" style="margin-top:0.5rem;">Tambah Menu Lagi</button>`;
  }
  return h;
}

function mRow(m){
  const q=qty[m.id]||0;
  const imgStyle = m.image 
    ? `background-image:url('${m.image}');background-size:cover;background-position:center;` 
    : `background:linear-gradient(135deg,#f5e4be,#e8c97a);display:flex;align-items:center;justify-content:center;font-size:1.5rem;`;
  return`<div class="menu-row">
    <div style="width:44px;height:44px;border-radius:10px;overflow:hidden;flex-shrink:0;${imgStyle}">${m.image?'':m.emoji}</div>
    <div class="menu-info"><div class="menu-row-name">${m.name}</div><div class="menu-row-price">${fmt(m.price)}</div></div>
    <div class="qty-wrap"><button class="qty-btn" onclick="chgQty(${m.id},-1)" ${q===0?'disabled':''}>−</button><div class="qty-val">${q}</div><button class="qty-btn" onclick="chgQty(${m.id},1)">+</button></div>
  </div>`;
}

function chgQty(id,d){
  qty[id]=Math.max(0,(qty[id]||0)+d);
  localStorage.setItem('mk_cart_qty', JSON.stringify(qty));
  renderOrder();
  if (typeof renderLandingSteppers === 'function') renderLandingSteppers();
}
function oGoStep(n){oStep=n;if(n===2){payMethod=null;uploaded=null;}renderOrder();}

function addLandingItem(id) {
    chgQty(id, 1);
}

function openMenuDetail(id) {
  const m = MENUS.find(x => x.id == id);
  if(!m) return;
  
  if (m.image) {
    document.getElementById('mdImg').src = m.image;
    document.getElementById('mdImg').style.display = 'block';
    document.getElementById('mdEmoji').style.display = 'none';
  } else {
    document.getElementById('mdImg').style.display = 'none';
    document.getElementById('mdEmoji').innerText = m.emoji || '🍽️';
    document.getElementById('mdEmoji').style.display = 'flex';
  }

  document.getElementById('mdCat').innerText = m.category_name || (m.cat === 'drink' ? 'Minuman' : 'Makanan');
  document.getElementById('mdName').innerText = m.name;
  document.getElementById('mdPrice').innerText = fmt(m.price);
  document.getElementById('mdDesc').innerText = m.desc || '-';
  
  document.getElementById('mdRasa').innerText = (m.rasa||0) + '/5';
  document.getElementById('mdHarga').innerText = (m.harga||0) + '/5';
  document.getElementById('mdSehat').innerText = (m.sehat||0) + '/5';
  document.getElementById('mdKenyang').innerText = (m.kenyang||0) + '/5';
  
  const tagsWrap = document.getElementById('mdTags');
  if(tagsWrap) tagsWrap.innerHTML = '';
  /*
  if(m.tags && Array.isArray(m.tags)) {
    m.tags.forEach(t => {
      tagsWrap.innerHTML += `<span style="background:#e0e7ff; color:#4338ca; font-size:0.75rem; font-weight:700; padding:0.2rem 0.6rem; border-radius:12px;">#${t}</span>`;
    });
  } else if (typeof m.tags === 'string') {
    tagsWrap.innerHTML += `<span style="background:#e0e7ff; color:#4338ca; font-size:0.75rem; font-weight:700; padding:0.2rem 0.6rem; border-radius:12px;">#${m.tags}</span>`;
  }
  */

  const actWrap = document.getElementById('mdActionWrap');
  if(qty[id] > 0) {
    actWrap.innerHTML = `
      <div style="display:flex; justify-content:space-between; align-items:center; background:#f9f9f9; border:1px solid #eee; padding:0.6rem 1rem; border-radius:14px;">
        <span style="font-weight:700; color:var(--text); font-size:0.95rem;">Pesanan: <span style="color:var(--maroon);">${qty[id]} porsi</span></span>
        <button class="btn-primary" style="margin:0; width:auto; padding:0.5rem 1.2rem; font-size:0.85rem;" onclick="document.getElementById('menuDetailModal').classList.remove('open'); openOrder();">Lihat Keranjang</button>
      </div>
    `;
  } else {
    actWrap.innerHTML = `<button class="btn-primary" style="margin:0; width:100%;" onclick="addLandingItem(${id}); document.getElementById('menuDetailModal').classList.remove('open');">Tambahkan ke Pesanan</button>`;
  }

  document.getElementById('menuDetailModal').classList.add('open');
}

function renderLandingSteppers() {
    if (!window.APP_MENUS) return;
    
    window.APP_MENUS.forEach(m => {
        const q = qty[m.id] || 0;
        const stepperWrap = document.getElementById(`stepper-${m.id}`);
        if (!stepperWrap) return;
        
        const btnInit = stepperWrap.querySelector('.add-btn-init');
        const stepper = stepperWrap.querySelector('.stepper-controls');
        const disp = stepperWrap.querySelector('.qty-display');
        
        if (btnInit && stepper && disp) {
            if (q > 0) {
                btnInit.style.display = 'none';
                stepper.style.display = 'flex';
                disp.innerText = q;
            } else {
                btnInit.style.display = 'block';
                stepper.style.display = 'none';
            }
        }
    });

    const uniqueItemsCount = Object.keys(qty).filter(k => qty[k] > 0).length;
    let badge = document.getElementById('cart-badge');
    if (!badge) {
        const fab = document.querySelector('.fab-order');
        if (fab) {
            badge = document.createElement('div');
            badge.id = 'cart-badge';
            badge.className = 'cart-badge';
            fab.appendChild(badge);
        }
    }
    
    if (badge) {
        if (uniqueItemsCount > 0) {
            const prevCount = parseInt(badge.innerText || '0');
            badge.innerText = uniqueItemsCount;
            badge.style.display = 'flex';
            
            if (prevCount !== uniqueItemsCount) {
                badge.classList.remove('pop');
                void badge.offsetWidth; 
                badge.classList.add('pop');
            }
        } else {
            badge.style.display = 'none';
        }
    }
}

let cName='', cPhone='', cAddress='', cDate='', cEmail='', cPassword='',
    lastOrderNumber='', lastPayMethod='', lastTotal=0, lastDp=0, lastOrderRowsHTML='';
let bankPayFull = false;

function oS2(){
  if (!window.IS_LOGGED_IN) {
    return `<div style="text-align:center; padding: 2rem 1rem; margin-top: 1rem; border-radius: 12px;">
      <div style="background: rgba(128,0,0,0.06); width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.2rem auto; border: 1.5px solid rgba(128,0,0,0.08);">
        <svg style="width: 38px; height: 38px; color: var(--maroon);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
      </div>
      <h3 style="margin-bottom: 0.5rem; font-size: 1.3rem; color: #333;">Silakan Login Terlebih Dahulu</h3>
      <p style="color: var(--text-light); margin-bottom: 2rem; font-size: 0.95rem; line-height: 1.5;">Anda harus masuk ke akun Anda untuk menyelesaikan pesanan dan melanjutkan pembayaran.</p>
      
      <button type="button" onclick="localStorage.setItem('mk_auto_open','1'); window.location.href=window.GOOGLE_LOGIN_URL" style="width: 100%; background:white;border:1px solid #ddd;padding:0.9rem;border-radius:10px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:0.6rem;cursor:pointer;margin-bottom:1rem;box-shadow: 0 2px 4px rgba(0,0,0,0.03); font-size: 0.95rem;">
        <svg width="22" height="22" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/><path fill="none" d="M0 0h48v48H0z"/></svg>
        Login dengan Google
      </button>

      <button type="button" onclick="localStorage.setItem('mk_auto_open','1'); window.location.href=window.LOGIN_URL" class="btn-primary" style="width: 100%; display:flex; align-items:center; justify-content:center; gap: 0.5rem; padding: 0.9rem; border-radius: 10px; font-size: 0.95rem;">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        Login dengan Akun (Email)
      </button>
      
      <div style="font-size: 0.95rem; color: #666; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #eee;">
        Belum punya akun? <br>
        <a href="${window.LOGIN_URL}" onclick="localStorage.setItem('mk_auto_open','1')" style="color: var(--maroon); font-weight: 700; text-decoration: underline; display: inline-block; margin-top: 0.5rem; font-size: 1rem;">Daftar di sini</a>
      </div>
      
      <button class="btn-ghost" onclick="oGoStep(1)" style="margin-top: 1.5rem;">Kembali ke Menu</button>
    </div>`;
  }

  if(!cName && window.USER_NAME) cName = window.USER_NAME;
  if(!cPhone && window.USER_PHONE) cPhone = window.USER_PHONE;
  if(!cAddress && window.USER_ADDRESS) cAddress = window.USER_ADDRESS;

  const t=oTotal(), d=oDp();
  const leadMins = window.MIN_ORDER_LEAD_TIME || 30;
  const now = new Date();
  now.setMinutes(now.getMinutes() + leadMins);
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  const minDateTime = now.toISOString().slice(0,16);

  const maxDate = new Date();
  maxDate.setFullYear(maxDate.getFullYear() + 1);
  maxDate.setMinutes(maxDate.getMinutes() - maxDate.getTimezoneOffset());
  const maxDateTime = maxDate.toISOString().slice(0,16);

  let h = `<div class="dp-banner"><div class="dp-ico"><img src="/images/icons/info.png" class="icon-img" alt="" onerror="this.style.display='none'"></div><div class="dp-info"><h4>Kebijakan DP ${DP_PCT}%</h4><p>DP telah ditetapkan. Pelunasan saat pengambilan.</p></div><div class="dp-right"><div class="dp-num">${fmt(d)}</div><div class="dp-lbl">DP minimum</div></div></div>
  <div class="form-section">
    <div class="form-section-label">Data Pemesan</div>
    <input type="text" id="custName" placeholder="Nama Lengkap (min. 3 karakter)" value="${cName}" oninput="cName=this.value;checkData()" class="cust-input">
    <input type="tel" id="custPhone" placeholder="No. WhatsApp (contoh: 08123...)" value="${cPhone}" oninput="this.value=this.value.replace(/[^0-9]/g,'');cPhone=this.value;checkData()" class="cust-input" inputmode="numeric" pattern="[0-9]*">
    <textarea id="custAddress" placeholder="Alamat lengkap (untuk pengambilan / pengiriman)" oninput="cAddress=this.value;checkData()" class="cust-input cust-textarea" rows="2">${cAddress}</textarea>
    
    <label class="cust-label" style="margin-top: 1.2rem;">Waktu Pesanan Dibutuhkan (Tanggal & Waktu)</label>
    <input type="text" id="custDate" value="${cDate}" min="${minDateTime}" max="${maxDateTime}" placeholder="Pilih Tanggal & Jam" oninput="cDate=this.value;checkData()" class="cust-input">
    <div style="font-size:0.75rem; color:#b88a00; margin-top:4px; padding:6px 10px; background:#fffbe6; border-radius:8px; border:1px solid #ffe58f; display:flex; align-items:center; gap:6px;">
      <svg style="width:14px;height:14px;color:#b88a00;flex-shrink:0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
      <span>Minimal pemesanan <strong>${leadMins} menit</strong> sebelum waktu pesanan dibutuhkan.</span>
    </div>
  </div>
  <div class="form-section-label" style="margin-top:1.4rem;margin-bottom:.7rem;">Metode Pembayaran</div>
  <div class="pay-opts">
    <div class="pay-opt ${payMethod==='cash'?'sel':''}" onclick="selPay('cash')"><div class="pay-opt-icon"><img src="/images/icons/cash.png" class="icon-img" alt="Tunai" onerror="this.style.display='none'"></div><div class="pay-opt-name">Tunai</div><div class="pay-opt-hint">Bayar saat pengambilan</div></div>
    <div class="pay-opt ${payMethod==='bank'?'sel':''}" onclick="selPay('bank')"><div class="pay-opt-icon"><img src="/images/icons/bank.png" class="icon-img" alt="Transfer" onerror="this.style.display='none'"></div><div class="pay-opt-name">Transfer Bank</div><div class="pay-opt-hint">BRI / BNI / Mandiri</div></div>
  </div>`;

  if(payMethod==='cash'){
    h+=`<div class="pay-detail"><h4>Informasi Pembayaran Tunai</h4><div style="background:var(--green-bg);border:1px solid rgba(30,127,81,.25);border-radius:12px;padding:1.1rem 1.2rem;"><div style="font-weight:700;font-size:.92rem;color:var(--green);margin-bottom:.3rem;">Bayar Lunas saat Pengambilan</div><div style="font-size:.78rem;color:var(--text-light);">Tidak diperlukan DP. Pembayaran dilakukan langsung di tempat.</div><div style="border-top:1px solid rgba(30,127,81,.2);padding-top:.7rem;margin-top:.7rem;display:flex;justify-content:space-between;align-items:center;"><span style="font-size:.82rem;color:var(--text-light);">Total yang dibayar</span><span style="font-size:1.15rem;font-weight:800;color:var(--green);">${fmt(t)}</span></div></div></div>`;
  }
  if(payMethod==='bank'){
    const payAmt = bankPayFull ? t : d;
    h+=`<div class="pay-detail"><h4>Opsi Pembayaran Transfer</h4>
      <div style="display:flex;gap:0.6rem;margin-bottom:1.2rem;">
        <div onclick="bankPayFull=false;renderOrder()" style="flex:1;padding:0.8rem;border-radius:12px;border:2px solid ${!bankPayFull?'var(--maroon)':'#ddd'};background:${!bankPayFull?'#fdf2f2':'#fff'};cursor:pointer;text-align:center;transition:all 0.2s;">
          <div style="font-weight:700;font-size:0.9rem;color:${!bankPayFull?'var(--maroon)':'#555'};">Bayar DP</div>
          <div style="font-size:0.78rem;color:#888;margin-top:0.2rem;">DP ${DP_PCT}% = ${fmt(d)}</div>
          <div style="font-size:0.7rem;color:#aaa;margin-top:0.15rem;">Pelunasan saat ambil</div>
        </div>
        <div onclick="bankPayFull=true;renderOrder()" style="flex:1;padding:0.8rem;border-radius:12px;border:2px solid ${bankPayFull?'var(--maroon)':'#ddd'};background:${bankPayFull?'#fdf2f2':'#fff'};cursor:pointer;text-align:center;transition:all 0.2s;">
          <div style="font-weight:700;font-size:0.9rem;color:${bankPayFull?'var(--maroon)':'#555'};">Bayar Lunas</div>
          <div style="font-size:0.78rem;color:#888;margin-top:0.2rem;">Full ${fmt(t)}</div>
          <div style="font-size:0.7rem;color:#aaa;margin-top:0.15rem;">Tidak ada sisa bayar</div>
        </div>
      </div>
      <h4>Detail Rekening Bank</h4><div class="bank-line"><span class="bl-label">Bank</span><span class="bl-val" id="bankNameTxt">Loading...</span></div><div class="bank-line"><span class="bl-label">No. Rekening</span><span class="bl-val"><span id="bankAccTxt">Loading...</span> <button class="copy-btn" onclick="cp(document.getElementById('bankAccTxt').innerText)">Copy</button></span></div><div class="bank-line"><span class="bl-label">Atas Nama</span><span class="bl-val" id="bankHolderTxt">Loading...</span></div><div class="bank-line"><span class="bl-label">Nominal Transfer</span><span class="bl-val" style="color:var(--maroon);font-weight:800;">${fmt(payAmt)}</span></div></div>`;
    h+=`<span class="upload-label">Upload Bukti Transfer</span><div class="upload-zone"><input type="file" accept=".png, .jpg, .jpeg, .heic, .webp, image/png, image/jpeg, image/heic, image/webp" id="paymentFile" onchange="handleFile(event)"/><div class="upload-ico"><img src="/images/icons/upload.png" class="icon-img" alt="Upload" onerror="this.style.display='none'"></div><div class="upload-txt" id="uploadText">Klik atau seret foto bukti transfer</div><div class="upload-hint">JPG, PNG, HEIC — maks. 5MB</div><img class="preview-img ${uploaded?'show':''}" id="prevImg" ${uploaded?`src="${uploaded.previewExt}"`:''}/></div>`;
  }
  let summaryBox = '';
  if (payMethod==='cash') {
    summaryBox = `<div class="order-box" style="margin-top:1.4rem;"><div class="orow"><span>Total Pesanan</span><span>${fmt(t)}</span></div><div class="orow orow-total"><span>Bayar Lunas (Tunai)</span><span>${fmt(t)}</span></div></div>`;
  } else if (payMethod==='bank' && bankPayFull) {
    summaryBox = `<div class="order-box" style="margin-top:1.4rem;"><div class="orow"><span>Total Pesanan</span><span>${fmt(t)}</span></div><div class="orow orow-total"><span>Transfer Lunas</span><span>${fmt(t)}</span></div></div>`;
  } else if (payMethod==='bank') {
    summaryBox = `<div class="order-box" style="margin-top:1.4rem;"><div class="orow"><span>Total Pesanan</span><span>${fmt(t)}</span></div><div class="orow"><span>DP Transfer (${DP_PCT}%)</span><span style="color:var(--gold-light)">${fmt(d)}</span></div><div class="orow orow-total"><span>Sisa Pelunasan</span><span>${fmt(t-d)}</span></div></div>`;
  }
  h += summaryBox;
  h+=`<div id="validationMsg" style="color:#ef4444; font-size:0.85rem; margin-bottom:12px; text-align:left; font-weight:600; display:none; background:#fef2f2; padding:10px 15px; border-radius:8px; border:1px solid #fca5a5; line-height:1.5;"></div>
  <button class="btn-primary" id="pesanBtn" onclick="submitOrder()" disabled>Kirim Pesanan</button>
  <button class="btn-ghost" onclick="oGoStep(1)">Ubah Menu</button>`;
  return h;
}

let bankInfoCache = null;

function updateBankDOM() {
  if(!bankInfoCache) return;
  const bn = document.getElementById('bankNameTxt'),
        bnc = document.getElementById('bankAccTxt'),
        bhl = document.getElementById('bankHolderTxt');
  if(bn) bn.innerText = bankInfoCache.bank_name || '-';
  if(bnc) bnc.innerText = bankInfoCache.account_number || '-';
  if(bhl) bhl.innerText = bankInfoCache.account_name || '-';
}

function selPay(m){
  payMethod=m; renderOrder(); checkData();
}

function handleFile(e){
  const f=e.target.files[0];
  if(!f)return;
  if(f.size > 5 * 1024 * 1024) { alert("Maksimal 5MB!"); e.target.value=''; return; }
  uploaded=f;
  const r=new FileReader();
  r.onload=ev=>{
    uploaded.previewExt = ev.target.result;
    const img=document.getElementById('prevImg');
    if(img){img.src=ev.target.result;img.classList.add('show');}
    const txt=document.getElementById('uploadText');
    if(txt){txt.innerText=f.name;}
    checkData();
  };
  r.readAsDataURL(f);
}

function checkData(){
  const btn = document.getElementById('pesanBtn');
  const msg = document.getElementById('validationMsg');
  if(!btn) return;
  const validName    = cName.trim().length >= 3;
  const validPhone   = cPhone.trim().length >= 10;
  const validAddress = cAddress.trim().length >= 5;
  const validEmail   = window.IS_LOGGED_IN ? true : cEmail.trim().length >= 5;
  const validPass    = window.IS_LOGGED_IN ? true : cPassword.trim().length >= 4;
  let validDate      = cDate.trim().length > 0;
  let dateIsPast     = false;
  let isClosed       = false;
  let closedReason   = "";
  
  if (validDate) {
      const selDate = new Date(cDate);
      const leadMins = window.MIN_ORDER_LEAD_TIME || 30;
      const limit = new Date();
      limit.setMinutes(limit.getMinutes() + leadMins - 2); // 2 mins buffer
      if (selDate < limit) {
          validDate = false;
          dateIsPast = true;
      } else if (window.OP_HOURS) {
          const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
          const dayName = days[selDate.getDay()];
          const opStr = window.OP_HOURS[dayName];
          if (!opStr || opStr.match(/libur|tutup/i)) {
              validDate = false;
              isClosed = true;
              closedReason = `Toko libur pada hari ${dayName}. Silakan pilih hari lain.`;
          } else if (opStr.includes('-')) {
              const [start, end] = opStr.split('-').map(s => s.trim());
              const [startH, startM] = start.split(':').map(Number);
              const [endH, endM] = end.split(':').map(Number);
              
              const selH = selDate.getHours();
              const selM = selDate.getMinutes();
              const selTime = selH * 60 + selM;
              const startTime = startH * 60 + startM;
              const endTime = endH * 60 + endM;
              
              if (selTime < startTime || selTime > endTime) {
                  validDate = false;
                  isClosed = true;
                  closedReason = `Pesanan diluar jam operasional. Hari ${dayName} buka jam ${start} - ${end}.`;
              }
          }
      }
  }

  const validProof   = payMethod === 'bank' ? !!uploaded : true;

  let missing = [];
  if (!validName) missing.push("Nama (min. 3 huruf)");
  if (!validPhone) missing.push("Telepon/WA (min. 10 angka)");
  if (!validEmail) missing.push("Email valid");
  if (!validPass) missing.push("Password (min. 4 karakter)");
  if (!validAddress) missing.push("Alamat Pengiriman (min. 5 huruf)");
  if (!validDate) {
      const leadMins = window.MIN_ORDER_LEAD_TIME || 30;
      if (dateIsPast) missing.push(`Waktu pesanan tidak valid. Anda harus memesan minimal ${leadMins} menit dari sekarang`);
      else if (isClosed) missing.push(closedReason);
      else missing.push("Tanggal & Waktu Acara");
  }
  
  if (!payMethod) {
      missing.push("Pilih Metode Pembayaran");
  } else if (!validProof) {
      missing.push("Upload Foto Bukti Transfer");
  }

  if(missing.length === 0) {
    btn.disabled = false;
    if (msg) msg.style.display = 'none';
  } else {
    btn.disabled = true;
    if (msg) {
      msg.style.display = 'block';
      msg.innerHTML = "<span style='display:block;margin-bottom:4px;'>Belum lengkap:</span>• " + missing.join("<br/>• ");
    }
  }
}

function cp(txt){navigator.clipboard.writeText(txt).then(()=>{document.querySelectorAll('.copy-btn').forEach(b=>{if(b.textContent==='Copy'){b.textContent='✓';setTimeout(()=>b.textContent='Copy',1300);}});});}

function submitOrder(){
  const btn = document.getElementById('pesanBtn');
  btn.disabled = true;
  if(document.getElementById('pesanLoad')) document.getElementById('pesanLoad').style.display = 'inline';
  
  const fd = new FormData();
  fd.append('_token', window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content || '');
  fd.append('customer_name',    cName);
  fd.append('customer_phone',   cPhone);
  fd.append('customer_address', cAddress);
  if (!window.IS_LOGGED_IN) {
     fd.append('email', cEmail);
     fd.append('password', cPassword);
  }
  fd.append('event_date',       cDate);
  fd.append('payment_method',   payMethod);
  if(payMethod === 'bank') fd.append('bank_pay_full', bankPayFull ? '1' : '0');
  // Bukti hanya diperlukan untuk transfer bank
  if(payMethod === 'bank' && uploaded) fd.append('payment_proof', uploaded);
  
  let itemIdx = 0;
  MENUS.filter(m=>qty[m.id]>0).forEach(m => {
     fd.append(`items[${itemIdx}][menu_item_id]`, m.id);
     fd.append(`items[${itemIdx}][qty]`, qty[m.id]);
     fd.append(`items[${itemIdx}][notes]`, itemNotes[m.id] || '');
     itemIdx++;
  });

  fetch('/order', {
    method: 'POST',
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: fd
  })
  .then(r => r.json())
  .then(res => {
     if(res.order_number) {
       lastOrderNumber = res.order_number;
       lastPayMethod   = res.payment_method || payMethod;
       lastTotal       = res.total_amount   || oTotal();
       lastDp          = res.dp_amount      || oDp();
       lastOrderRowsHTML = MENUS.filter(m=>qty[m.id]>0).map(m=>`<div class="orow"><span>${m.name} ×${qty[m.id]}</span><span>${fmt(m.price*qty[m.id])}</span></div>`).join('');
       oStep = 3;
       renderOrder();
       localStorage.removeItem('mk_cart_qty');
       localStorage.removeItem('mk_cart_notes');
       MENUS.forEach(m=>qty[m.id]=0);
       if (typeof renderLandingSteppers === 'function') renderLandingSteppers();
     } else if(res.errors || res.error) {
       let errMsg = res.error || '';
       if (res.errors) {
           errMsg = Object.values(res.errors).flat().join('\n');
       }
       alert(errMsg);
       btn.disabled = false;
       if(document.getElementById('pesanLoad')) document.getElementById('pesanLoad').style.display = 'none';
     }
  })
  .catch(e => {
     alert("Terjadi kesalahan jaringan.");
     btn.disabled = false;
     if(document.getElementById('pesanLoad')) document.getElementById('pesanLoad').style.display = 'none';
  });
}

function oS3(){
  const t = lastTotal || oTotal();
  const d = lastDp   || oDp();
  const isCash = lastPayMethod === 'cash';
  let rows = lastOrderRowsHTML || MENUS.filter(m=>qty[m.id]>0).map(m=>`<div class="orow"><span>${m.name} ×${qty[m.id]}</span><span>${fmt(m.price*qty[m.id])}</span></div>`).join('');

  const paymentInfo = isCash
    ? `<div class="orow orow-total"><span>Total Pesanan</span><span>${fmt(t)}</span></div>
       <div class="orow" style="color:rgba(255,255,255,.85);font-size:.82rem;"><span>Pembayaran</span><span>Tunai saat pengambilan</span></div>`
    : `<div class="orow orow-total"><span>Total Pesanan</span><span>${fmt(t)}</span></div>
       <div class="orow" style="color:rgba(255,255,255,.72)"><span>DP ditransfer</span><span>${fmt(d)}</span></div>
       <div class="orow" style="color:rgba(255,255,255,.72)"><span>Sisa saat pengambilan</span><span>${fmt(t-d)}</span></div>`;

  const payNote = isCash
    ? `<div style="background:#eaf7f1;border:1px solid rgba(30,127,81,.25);border-radius:13px;padding:1rem;font-size:.84rem;color:var(--green);line-height:1.65;margin-bottom:1.2rem;text-align:center;"><strong>Bayar lunas (${fmt(t)})</strong> saat pengambilan pesanan.</div>`
    : `<div style="background:#fdf4ec;border-radius:13px;padding:1rem;font-size:.84rem;color:var(--text-light);line-height:1.65;margin-bottom:1.2rem;text-align:center;">DP <strong>${fmt(d)}</strong> sedang diverifikasi admin. Sisa <strong>${fmt(t-d)}</strong> dibayar saat pengambilan.</div>`;
  return `<div class="success-wrap">
    <div class="success-ico"><img src="/images/icons/success.png" class="icon-img" style="width:80px;height:80px;object-fit:contain;" alt="Sukses" onerror="this.parentElement.textContent='OK'"></div>
    <div class="success-ttl">Pesanan Terkirim!</div>
    <div class="success-sub">Order ID: <strong>${lastOrderNumber}</strong><br>Terima kasih! Tim Markesot akan segera memproses.</div>
    <div class="order-box" style="margin-bottom:1.2rem;">${rows}${paymentInfo}</div>
    ${payNote}
    <button class="btn-primary" onclick="window.location.reload()">Selesai</button>
  </div>`;
}

function resetOrder(){MENUS.forEach(m=>qty[m.id]=0);payMethod=null;uploaded=null;oStep=1;cName='';cPhone='';cAddress='';cDate='';closeOrder();}

/* ═══════════════════════════════════════
   DSS / AHP SYSTEM
═══════════════════════════════════════ */
let pairAns={rasa_vs_nutrisi:null,rasa_vs_jenis:null,nutrisi_vs_jenis:null};
let dssScreen=0;
let dssApiResult=null;
let dssCrossSellCat='all';
let dssCrossSellCrit='all';
let dssBestMenuId=null;
const TOTAL_Q=3;

// Helper to render premium inline SVG icons for criteria instead of generic emojis
function getCriterionSVG(id, size = 32) {
  if (id === 'rasa') {
    // Taste Smiley / Culinary Tongue representation
    return `<svg style="width:${size}px;height:${size}px;color:var(--maroon);display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z"></path>
      <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
      <line x1="9" y1="9" x2="9.01" y2="9"></line>
      <line x1="15" y1="9" x2="15.01" y2="9"></line>
    </svg>`;
  }
  if (id === 'nutrisi') {
    // Healthy Nutrition Shield representation
    return `<svg style="width:${size}px;height:${size}px;color:var(--maroon);display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
    </svg>`;
  }
  if (id === 'jenis_hidangan') {
    // Elegant Culinary Cloche / Bowl representation
    return `<svg style="width:${size}px;height:${size}px;color:var(--maroon);display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 12h18"></path>
      <path d="M12 22a9 9 0 0 0 9-9H3a9 9 0 0 0 9 9Z"></path>
      <path d="M12 8V2"></path>
      <path d="M9 5h6"></path>
    </svg>`;
  }
  return `<svg style="width:${size}px;height:${size}px;color:var(--maroon);display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle></svg>`;
}

// Menu Fallback SVG
function getMenuFallbackSVG(size = 40) {
  return `<svg style="width:${size}px;height:${size}px;color:var(--maroon);opacity:0.85;display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
    <path d="M3 12h18"></path>
    <path d="M12 22a9 9 0 0 0 9-9H3a9 9 0 0 0 9 9Z"></path>
    <path d="M12 8V2"></path>
    <path d="M9 5h6"></path>
  </svg>`;
}

// Side cross-selling drinks/snacks selection with criteria & category toggles
function getCrossSellItems(bestMenuId, category = 'all') {
  let items = MENUS.filter(m => m.id != bestMenuId && m.cat_enable_cross_sell === true && m.cat_enable_ahp !== true);
  
  // Filter by category
  if (category !== 'all') {
    items = items.filter(m => m.category_name === category);
  }
  
  return items;
}

function setCrossSellFilter(type, value) {
  if (type === 'cat') {
    dssCrossSellCat = value;
  } else if (type === 'crit') {
    dssCrossSellCrit = value;
  }
  const bodyEl = document.getElementById('dssBody');
  if (bodyEl) {
    bodyEl.innerHTML = dssResult();
    animW();
  }
}

function chgCrossSellQty(id, d) {
  qty[id] = Math.max(0, (qty[id] || 0) + d);
  localStorage.setItem('mk_cart_qty', JSON.stringify(qty));
  if (typeof renderLandingSteppers === 'function') renderLandingSteppers();
  renderCrossSellSteppers();
}

function setCrossSellCategory(catName) {
  dssCrossSellCat = catName;
  const buttons = document.querySelectorAll('.cross-sell-cat-btn');
  buttons.forEach(btn => {
    const isSelected = btn.getAttribute('data-cat') === catName;
    if (isSelected) {
      btn.style.background = 'var(--maroon)';
      btn.style.color = 'white';
      btn.style.borderColor = 'var(--maroon)';
      btn.style.boxShadow = '0 2px 6px rgba(107,28,42,0.15)';
    } else {
      btn.style.background = 'white';
      btn.style.color = 'var(--maroon)';
      btn.style.borderColor = 'var(--gold)';
      btn.style.boxShadow = 'none';
    }
  });
  renderCrossSellItems();
}

/* Toggle visibility of cross-sell steppers (same pattern as renderLandingSteppers) */
function renderCrossSellSteppers() {
  const container = document.getElementById('crossSellItemsList');
  if (!container) return;
  container.querySelectorAll('.cross-sell-item-row').forEach(row => {
    const itemId = row.getAttribute('data-item-id');
    if (!itemId) return;
    const q = qty[itemId] || 0;
    const btnInit = row.querySelector('.add-btn-init');
    const stepper = row.querySelector('.stepper-controls');
    const disp = row.querySelector('.qty-display');
    if (btnInit && stepper && disp) {
      if (q > 0) {
        btnInit.style.display = 'none';
        stepper.style.display = 'flex';
        disp.innerText = q;
      } else {
        btnInit.style.display = 'flex';
        stepper.style.display = 'none';
      }
    }
  });
}

function renderCrossSellItems() {
  const container = document.getElementById('crossSellItemsList');
  if (!container) return;
  const crossItems = getCrossSellItems(dssBestMenuId, dssCrossSellCat);
  if (crossItems.length === 0) {
    container.innerHTML = `<div style="font-size:0.72rem; color:#888; text-align:center; padding:0.5rem 0; width: 100%;">Tidak ada menu pendamping yang cocok dengan kategori ini.</div>`;
    return;
  }
  container.innerHTML = crossItems.map(item => {
    const inCart = qty[item.id] || 0;
    return `
    <div class="cross-sell-item-row" data-item-id="${item.id}" style="display: flex; align-items: center; gap: 0.75rem; background: white; border: 1px solid rgba(0,0,0,0.035); border-radius: 12px; padding: 0.6rem 0.7rem; box-shadow: 0 2px 6px rgba(0,0,0,0.015); transition: all 0.2s;">
      <div style="width: 40px; height: 40px; border-radius: 10px; overflow: hidden; flex-shrink: 0; background: ${item.image ? `url('${item.image}') center/cover` : 'linear-gradient(135deg,#f5e4be,#e8c97a)'}; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
        ${item.image ? '' : getMenuFallbackSVG(18)}
      </div>
      <div style="flex: 1; text-align: left;">
        <div style="font-weight: 750; font-size: 0.8rem; color: var(--text);">${item.name}</div>
        <div style="font-size: 0.74rem; font-weight: 700; color: var(--maroon); margin-top: 0.1rem;">Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</div>
      </div>
      <div class="landing-stepper" style="flex-shrink: 0; width: 90px;">
        <button class="add-btn-init" onclick="chgCrossSellQty(${item.id}, 1)" style="height: 30px; font-size: 0.75rem; display: ${inCart > 0 ? 'none' : 'flex'};">
          + Tambah
        </button>
        <div class="stepper-controls" style="display: ${inCart > 0 ? 'flex' : 'none'}; height: 30px;">
          <button class="st-minus" onclick="chgCrossSellQty(${item.id}, -1)">−</button>
          <span class="qty-display">${inCart}</span>
          <button class="st-plus" onclick="chgCrossSellQty(${item.id}, 1)">+</button>
        </div>
      </div>
    </div>
    `;
  }).join('');
}

function openDSS(){
  dssScreen=0;
  document.getElementById('dssOverlay').classList.add('open');
  document.body.style.overflow='hidden';
  renderDSS();
}
function closeDSS(){
  document.getElementById('dssOverlay').classList.remove('open');
  document.body.style.overflow='';
}

function renderDSS(){
  updateDSSProgress();
  const b=document.getElementById('dssBody');
  if(dssScreen===0)b.innerHTML=dssIntro();
  else if(dssScreen<=3)b.innerHTML=dssPair(dssScreen-1);
  else if(dssScreen===4){b.innerHTML=dssLoading();runDSSLoading();}
  else b.innerHTML=dssResult();
  b.scrollTop=0;
  setTimeout(animW,80);
}

function updateDSSProgress(){
  const answered=Object.values(pairAns).filter(v=>v!==null).length;
  const pct=Math.round((answered/TOTAL_Q)*100);
  document.getElementById('dssPFill').style.width=pct+'%';
  document.getElementById('dssPStep').textContent=answered+' dari '+TOTAL_Q;
  const labels=['Yuk Mulai!','Bandingkan Kriteria','Bandingkan Kriteria','Bandingkan Kriteria','Menyelaraskan Selera...','Saran Terbaik Chef'];
  document.getElementById('dssPLabel').textContent=labels[dssScreen]||'';
  let dots='';
  for(let i=0;i<TOTAL_Q;i++){const done=i<answered,active=i===answered;dots+=`<div class="dss-dot ${done?'done':active?'active':''}"></div>`;}
  document.getElementById('dssPDots').innerHTML=dots;
}

function buildPayload(){
  return {
    rasa_vs_nutrisi:  pairAns.rasa_vs_nutrisi  || 'sama',
    rasa_vs_jenis:    pairAns.rasa_vs_jenis    || 'sama',
    nutrisi_vs_jenis: pairAns.nutrisi_vs_jenis || 'sama',
  };
}

async function fetchDSSResult(){
  const payload = buildPayload();
  console.log('🔵 [AHP] pairAns saat kirim:', JSON.parse(JSON.stringify(pairAns)));
  console.log('🔵 [AHP] Payload JSON dikirim:', JSON.stringify(payload));

  try {
    const resp = await fetch('/api/recommendation',{
      method:'POST',
      headers:{
        'Content-Type':'application/json',
        'Accept':'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content||''
      },
      body: JSON.stringify(payload)
    });
    const json = await resp.json();
    return json;
  } catch(e){
    console.error('🔴 [AHP] Fetch error:', e);
    return { success:false, error:'Gagal terhubung ke server. Periksa koneksi Anda.' };
  }
}

function dssIntro(){
  return`<div style="text-align: center; margin: 1.25rem 0 1.8rem 0;">
    <div style="background: rgba(128,0,0,0.06); width: 76px; height: 76px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.9rem auto; box-shadow: 0 4px 10px rgba(128,0,0,0.06); border: 1px solid rgba(128,0,0,0.03);">
      <svg style="width: 42px; height: 42px; color: var(--maroon);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M2 20h20"></path>
        <path d="M20 16A8 8 0 0 0 4 16h16Z"></path>
        <path d="M12 8V5a2 2 0 0 1 2-2h0"></path>
      </svg>
    </div>
    <h3 style="font-weight: 800; font-size: 1.3rem; color: var(--text); margin: 0.8rem 0 0.25rem 0; letter-spacing: -0.02em;">Asisten Kuliner Markesot</h3>
    <p style="font-size: 0.82rem; color: var(--text-light); line-height: 1.5; max-width: 320px; margin: 0 auto;">Pencocok menu makan utama ideal yang dipersonalisasi khusus berdasarkan preferensi rasa, nutrisi, dan cara penyajian Anda.</p>
  </div>
  <div style="background: white; border: 1px solid rgba(0,0,0,0.04); border-radius: 16px; padding: 1.2rem; box-shadow: var(--shadow-sm); margin-bottom: 1.4rem;">
    <div style="font-weight: 700; font-size: 0.85rem; color: var(--text); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 8px; justify-content: center;">
      <svg style="width: 15px; height: 15px; color: var(--gold-light);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
      Sistem Penyelarasan Kriteria
    </div>
    <div style="display: flex; gap: 0.85rem; align-items: center; background: var(--gold-pale); border-radius: 12px; padding: 0.9rem 1rem; border: 1px solid rgba(201,168,76,0.15); text-align: left;">
      <div style="flex-shrink: 0; color: var(--maroon);">
        <svg style="width: 30px; height: 30px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="20" x2="18" y2="10"></line>
          <line x1="12" y1="20" x2="12" y2="4"></line>
          <line x1="6" y1="20" x2="6" y2="14"></line>
        </svg>
      </div>
      <div style="flex: 1;">
        <div style="font-weight: 800; font-size: 0.82rem; color: var(--maroon-dark); margin-bottom: 0.15rem;">Penyelarasan dalam 3 Soal</div>
        <div style="font-size: 0.72rem; color: var(--text-light); line-height: 1.45;">Cukup jawab <strong>3 pertanyaan cepat</strong> untuk menyelaraskan tingkat kepentingan Rasa, Gizi, dan Jenis Penyajian terbaik menurut Anda.</div>
      </div>
    </div>
  </div>
  <button class="btn-primary" onclick="dssGo(1)" style="font-weight: 700; font-size: 0.9rem; letter-spacing: -0.01em; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;">
    <span>Mulai Pencocokan Selera</span>
    <svg style="width:16px;height:16px;color:white;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
  </button>`;
}

function dssPair(idx){
  const pair=PAIRS[idx];
  const A=CRITERIA[pair.i],B=CRITERIA[pair.j];
  const currentVal=pairAns[pair.key];

  const selKiri  = currentVal==='kiri';
  const selKanan = currentVal==='kanan';
  const selSama  = currentVal==='sama';

  return`<div class="pair-counter" style="font-weight:700; font-size:0.72rem; color:var(--text-light); margin-bottom:0.8rem; letter-spacing:0.05em; text-transform:uppercase; text-align:center;">
    Pertanyaan ${idx+1} dari 3
    <div class="pair-dots">${[0,1,2].map(k=>`<div class="pdot ${k<idx?'done':k===idx?'active':''}"></div>`).join('')}</div>
  </div>
  <div style="background:white;border-radius:18px;padding:1.4rem;box-shadow:var(--shadow-sm);border:1px solid rgba(0,0,0,0.03);">
    <div style="font-weight: 800; font-size: 1rem; color: var(--text); margin-bottom: 0.25rem; letter-spacing: -0.02em;">Mana yang lebih penting bagi Anda?</div>
    <div style="font-size: 0.76rem; color: var(--text-light); margin-bottom: 1.25rem;">Pilihlah salah satu kriteria kuliner yang lebih Anda prioritaskan hari ini.</div>
    <div class="versus-wrap" style="display: flex; align-items: stretch; justify-content: space-between; position: relative; gap: 0.5rem; margin-bottom: 1rem;">
      <div class="versus-side ${selKiri?'sel':''}" onclick="dssSelWinner('${pair.key}','kiri')" style="flex: 1; border: 1.5px solid ${selKiri?'var(--maroon)':'#eaeaea'}; background: ${selKiri?'rgba(128,0,0,0.015)':'white'}; border-radius: 14px; padding: 1.1rem 0.5rem; cursor: pointer; transition: all 0.2s; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: ${selKiri?'0 4px 12px rgba(128,0,0,0.04)':'none'};">
        <div class="versus-icon" style="margin-bottom: 0.6rem; background: ${selKiri?'rgba(128,0,0,0.05)':'#fafafa'}; width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
          ${getCriterionSVG(A.id, 26)}
        </div>
        <div class="versus-name" style="font-weight: 800; font-size: 0.82rem; color: ${selKiri?'var(--maroon)':'var(--text)'}; margin-bottom: 0.2rem;">${A.name}</div>
        <div class="versus-desc" style="font-size: 0.68rem; color: var(--text-light); line-height: 1.35; padding: 0 4px;">${A.desc}</div>
        ${selKiri?'<div style="font-size: 0.68rem; font-weight: 700; color: var(--maroon); background: rgba(128,0,0,0.08); border-radius: 20px; padding: 0.15rem 0.55rem; margin-top: 0.6rem; display: inline-flex; align-items: center; gap: 4px;">✓ Dipilih</div>':''}
      </div>
      <div class="vs-divider" style="display: flex; align-items: center; justify-content: center; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 10; pointer-events: none;">
        <div class="vs-circle" style="width: 32px; height: 32px; border-radius: 50%; background: var(--maroon); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 800; border: 3px solid white; box-shadow: var(--shadow-sm);">VS</div>
      </div>
      <div class="versus-side ${selKanan?'sel':''}" onclick="dssSelWinner('${pair.key}','kanan')" style="flex: 1; border: 1.5px solid ${selKanan?'var(--maroon)':'#eaeaea'}; background: ${selKanan?'rgba(128,0,0,0.015)':'white'}; border-radius: 14px; padding: 1.1rem 0.5rem; cursor: pointer; transition: all 0.2s; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: ${selKanan?'0 4px 12px rgba(128,0,0,0.04)':'none'};">
        <div class="versus-icon" style="margin-bottom: 0.6rem; background: ${selKanan?'rgba(128,0,0,0.05)':'#fafafa'}; width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
          ${getCriterionSVG(B.id, 26)}
        </div>
        <div class="versus-name" style="font-weight: 800; font-size: 0.82rem; color: ${selKanan?'var(--maroon)':'var(--text)'}; margin-bottom: 0.2rem;">${B.name}</div>
        <div class="versus-desc" style="font-size: 0.68rem; color: var(--text-light); line-height: 1.35; padding: 0 4px;">${B.desc}</div>
        ${selKanan?'<div style="font-size: 0.68rem; font-weight: 700; color: var(--maroon); background: rgba(128,0,0,0.08); border-radius: 20px; padding: 0.15rem 0.55rem; margin-top: 0.6rem; display: inline-flex; align-items: center; gap: 4px;">✓ Dipilih</div>':''}
      </div>
    </div>
    <div class="equal-btn ${selSama?'sel':''}" onclick="dssSelWinner('${pair.key}','sama')" style="display: flex; align-items: center; justify-content: center; gap: 8px; border: 1.5px solid ${selSama?'var(--maroon)':'#eaeaea'}; background: ${selSama?'rgba(128,0,0,0.015)':'white'}; border-radius: 12px; padding: 0.7rem; font-size: 0.8rem; font-weight: 700; color: ${selSama?'var(--maroon)':'#555'}; cursor: pointer; text-align: center; margin-bottom: 1.25rem; transition: all 0.2s;">
      <svg style="width:15px;height:15px;color:currentColor;display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="9" x2="19" y2="9"></line><line x1="5" y1="15" x2="19" y2="15"></line></svg>
      Kedua kriteria sama pentingnya
    </div>
    <button class="btn-primary" onclick="dssNextPair(${idx})" ${currentVal===null?'disabled':''} style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;">
      ${idx<2?'<span>Pertanyaan Berikutnya</span> <svg style="width:14px;height:14px;color:currentColor;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>':'<span>Lihat Hasil Penyelarasan</span> <svg style="width:14px;height:14px;color:currentColor;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'}
    </button>
  </div>`;
}

function dssSelWinner(pairKey, pilihan){
  console.log('🟡 [AHP] User pilih:', pairKey, '=', pilihan);
  if (pairAns[pairKey] === pilihan) {
    pairAns[pairKey] = null;
    console.log('🟡 [AHP] Undo pilihan:', pairKey);
  } else {
    pairAns[pairKey] = pilihan;
  }
  const idx=PAIRS.findIndex(p=>p.key===pairKey);
  document.getElementById('dssBody').innerHTML=dssPair(idx);
  updateDSSProgress();setTimeout(animW,50);
}
function dssNextPair(idx){
  const key=PAIRS[idx].key;
  if(!pairAns[key])return;
  dssScreen = idx < 2 ? idx+2 : 4;
  renderDSS();
}

function dssLoading(){
  return`<div style="text-align:center;padding:2.5rem 1rem;">
    <div style="margin: 0 auto 1.5rem auto; display: flex; align-items: center; justify-content: center; position: relative; width: 60px; height: 60px;">
      <svg style="transform: rotate(-90deg); width: 60px; height: 60px; position: absolute;">
        <circle cx="30" cy="30" r="25" stroke="rgba(128,0,0,0.06)" stroke-width="4" fill="transparent" />
        <circle id="dssLoaderCircle" cx="30" cy="30" r="25" stroke="var(--maroon)" stroke-width="4" fill="transparent" 
                stroke-dasharray="157" stroke-dashoffset="157" style="stroke-dashoffset: 157px; transition: stroke-dashoffset 0.6s cubic-bezier(0.4, 0, 0.2, 1); stroke-linecap: round;" />
      </svg>
      <div style="position: absolute; display: flex; align-items: center; justify-content: center;">
        <svg style="width: 22px; height: 22px; color: var(--maroon);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 12h18"></path>
          <path d="M12 22a9 9 0 0 0 9-9H3a9 9 0 0 0 9 9Z"></path>
          <path d="M12 8V2"></path>
          <path d="M9 5h6"></path>
        </svg>
      </div>
    </div>
    <div style="font-weight:800;font-size:1.05rem;color:var(--text);margin-bottom:.4rem;letter-spacing:-0.02em;">Memproses Selera Anda...</div>
    <div style="font-size:.78rem;color:var(--text-light);margin-bottom:1.8rem;line-height:1.45;">Asisten kuliner sedang memadukan data hidangan utama terbaik.</div>
    <div id="loadSteps" style="display:flex; flex-direction:column; gap:0.75rem; text-align:left; max-width:290px; margin:0 auto; background:rgba(0,0,0,0.012); padding:1.2rem; border-radius:16px; border:1px solid rgba(0,0,0,0.035);">
      <div class="load-step" style="font-size:0.76rem; display:flex; align-items:center; gap:0.6rem; transition:all 0.4s ease;">
        <svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        <span>Menyelaraskan perbandingan kriteria...</span>
      </div>
      <div class="load-step" style="font-size:0.76rem; display:flex; align-items:center; gap:0.6rem; transition:all 0.4s ease;">
        <svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
        <span>Menghitung bobot prioritas selera...</span>
      </div>
      <div class="load-step" style="font-size:0.76rem; display:flex; align-items:center; gap:0.6rem; transition:all 0.4s ease;">
        <svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
        <span>Mencocokkan skor gizi & rasa...</span>
      </div>
      <div class="load-step" style="font-size:0.76rem; display:flex; align-items:center; gap:0.6rem; transition:all 0.4s ease;">
        <svg style="width:14px;height:14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
        <span>Menyajikan saran menu teratas...</span>
      </div>
    </div>
  </div>`;
}

async function runDSSLoading(){
  const steps = document.querySelectorAll('.load-step');
  const circle = document.getElementById('dssLoaderCircle');
  const delays = [400, 1000, 1600, 2200];
  const offsets = [117.75, 78.5, 39.25, 0];
  
  const apiPromise = fetchDSSResult();
  
  delays.forEach((d, i) => {
    setTimeout(() => {
      if (steps[i]) {
        steps[i].classList.add('done');
        steps[i].style.transform = 'translateX(4px)';
        const txt = steps[i].querySelector('span').textContent;
        steps[i].innerHTML = `<svg style="width:13px;height:13px;color:var(--green);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> <span style="font-size:0.75rem;">${txt}</span> <span style="margin-left:auto; color:var(--green); font-weight:700;">✓</span>`;
      }
      if (circle) {
        circle.style.strokeDashoffset = offsets[i] + 'px';
        circle.setAttribute('stroke-dashoffset', offsets[i]);
      }
    }, d);
  });
  
  const [result] = await Promise.all([
    apiPromise,
    new Promise(resolve => setTimeout(resolve, 2600))
  ]);
  
  dssApiResult = result;
  dssScreen = 5;
  renderDSS();
}

const generateNarrative = (best, weights) => {
  const weightPairs = [
    { name: 'Rasa', weight: weights.rasa || 0, desc: 'Anda sangat mengutamakan kelezatan cita rasa' },
    { name: 'Nutrisi', weight: weights.nutrisi || 0, desc: 'Anda sangat peduli dengan kandungan gizi & protein' },
    { name: 'Jenis Hidangan', weight: weights.jenis_hidangan || 0, desc: 'Anda mencari tipe penyajian hidangan yang pas' }
  ];
  
  weightPairs.sort((a, b) => b.weight - a.weight);
  const primary = weightPairs[0];
  const secondary = weightPairs[1];

  let tasteText = "";
  const rSkor = Math.round(best.skor_rasa || 0);
  if (rSkor === 1) tasteText = "memiliki profil rasa ringan & lembut";
  else if (rSkor === 2) tasteText = "menawarkan rasa gurih & segar alami";
  else if (rSkor === 3) tasteText = "menyuguhkan bumbu pekat atau pedas gurih yang mantap";
  else tasteText = "memiliki keseimbangan rasa yang pas";

  let nutriText = "";
  const nSkor = Math.round(best.skor_nutrisi || 0);
  if (nSkor === 1) nutriText = "sangat cocok untuk santapan santai berkarbohidrat";
  else if (nSkor === 2) nutriText = "dilengkapi protein nabati & telur seimbang";
  else if (nSkor === 3) nutriText = "Kaya gizi dan protein hewani padat yang memuaskan";
  else nutriText = "kandungan nutrisinya proporsional";

  let typeText = "";
  const tSkor = Math.round(best.skor_jenis_hidangan || 0);
  if (tSkor === 1) typeText = "disajikan kering atau digoreng renyah";
  else if (tSkor === 2) typeText = "merupakan kombinasi hidangan campuran yang pas";
  else if (tSkor === 3) typeText = "disajikan hangat dan berkuah/cairan yang menyegarkan";
  else typeText = "disajikan dengan penyajian yang menarik";

  let intro = `Karena ${primary.desc}`;
  if (secondary && secondary.weight > 0.25) {
    intro += ` serta ${secondary.desc.toLowerCase()}`;
  }

  return `<div style="font-size:0.76rem; color:#666; line-height:1.6; margin-bottom:1rem; padding-bottom:0.8rem; border-bottom:1px dashed rgba(201,168,76,0.35); text-align:left;">
    <strong>Saran Penyajian Chef:</strong> ${intro}, maka <strong>${best.nama_menu}</strong> adalah pilihan paling ideal untuk Anda! Menu ini ${tasteText}, ${nutriText}, serta ${typeText}.
  </div>`;
};

function dssResult(){
  const res = dssApiResult;
  const medals=['#1 Andalan', '#2 Peringkat', '#3 Peringkat', '#4 Cadangan', '#5 Cadangan'];
  const fills=['rf1','rf2','rf3','rfn','rfn'];

  if(!res || !res.success){
    const errMsg = res?.error || 'Terjadi kesalahan saat menghubungi server.';
    return`<div style="text-align:center;padding:2rem 1rem;">
      <svg style="width:48px;height:48px;color:var(--maroon);margin:0 auto 1rem auto;display:block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
      <div style="font-weight:700;font-size:1.1rem;color:var(--maroon);margin-bottom:.8rem;">Jawaban Kurang Konsisten</div>
      <div style="font-size:.84rem;color:var(--text-light);line-height:1.6;margin-bottom:1.5rem;background:#fef2f2;border-radius:12px;padding:1rem;">${errMsg}</div>
      <div style="font-size:.78rem;color:#888;background:#f9f9f9;border-radius:10px;padding:.8rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:center;gap:6px;">
        <svg style="width:14px;height:14px;color:var(--gold-dark);display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A5 5 0 0 0 8 8c0 1 .3 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"></path><path d="M9 18h6"></path><path d="M10 22h4"></path></svg>
        <span><strong>Tips:</strong> Coba lebih konsisten saat membandingkan kriteria.</span>
      </div>
      <button class="btn-primary" onclick="resetDSS()" style="display:flex;align-items:center;justify-content:center;gap:6px;margin:0 auto;">
        <svg style="width:14px;height:14px;color:white;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path></svg>
        Coba Lagi
      </button>
    </div>`;
  }

  const ranked = res.ranked;
  const weights = res.weights;
  const consistency = res.consistency;
  const best = ranked[0];

  const skorDesc = {
    skor_rasa:           {1:'Rasa Ringan / Lembut', 2:'Gurih Segar / Sedang', 3:'Rasa Kuat / Gurih Pedas'},
    skor_nutrisi:        {1:'Karbohidrat / Gizi Ringan', 2:'Protein Nabati & Telur', 3:'Protein Hewani Padat'},
    skor_jenis_hidangan: {1:'Kering / Gorengan', 2:'Campuran / Basah', 3:'Berkuah / Hangat'},
  };
  const wArr=[weights.rasa||0, weights.nutrisi||0, weights.jenis_hidangan||0];

  const trophySVG = `<svg style="width: 42px; height: 42px; color: var(--gold-light); display: inline-block; margin-bottom: 0.5rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
    <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
    <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
    <path d="M4 22h16"></path>
    <path d="M10 14.66V17c0 .55-.45 1-1 1H4v2h16v-2h-5c-.55 0-1-.45-1-1v-2.34"></path>
    <path d="M12 2a6 6 0 0 1 6 6v3.5c0 3.3-2.7 6-6 6s-6-2.7-6-6V8a6 6 0 0 1 6-6Z"></path>
  </svg>`;

  const bestImageHTML = best.image 
    ? `<div style="width: 110px; height: 110px; border-radius: 50%; overflow: hidden; border: 3px solid var(--gold); margin: 0.6rem auto 1rem auto; box-shadow: 0 6px 16px rgba(0,0,0,0.25); background: url('${best.image}') center/cover no-repeat;"></div>`
    : `<div style="width: 84px; height: 84px; border-radius: 50%; background: linear-gradient(135deg,#f5e4be,#e8c97a); display: flex; align-items: center; justify-content: center; margin: 0.6rem auto 1rem auto; box-shadow: 0 6px 16px rgba(0,0,0,0.15); border: 2px solid var(--gold);">${getMenuFallbackSVG(40)}</div>`;

  const medalsMarkup = (r) => {
    const labels = ['#1 Andalan', '#2 Peringkat', '#3 Peringkat', '#4 Cadangan', '#5 Cadangan'];
    const colors = [
      'background:var(--gold-pale);color:var(--gold-dark);border:1px solid rgba(201,168,76,0.3);',
      'background:#f5f5f5;color:#555;border:1px solid #ddd;',
      'background:#faf3eb;color:#b87333;border:1px solid rgba(184,115,51,0.25);',
      'background:#fafafa;color:#888;border:1px solid #eee;',
      'background:#fafafa;color:#888;border:1px solid #eee;'
    ];
    return `<span style="font-size:0.68rem;font-weight:800;padding:0.25rem 0.55rem;border-radius:20px;${colors[r]||colors[3]}">${labels[r]}</span>`;
  };

  dssBestMenuId = best.id;
  // Get cross selling items with currently active filters
  const activeCrossSellCats = [...new Set(MENUS.filter(m => m.cat_enable_cross_sell === true && m.cat_enable_ahp !== true).map(m => m.category_name))];
  if (dssCrossSellCat !== 'all' && !activeCrossSellCats.includes(dssCrossSellCat)) {
    dssCrossSellCat = 'all';
  }
  const crossItems = getCrossSellItems(best.id, dssCrossSellCat);
  let crossSellHTML = '';
  if (crossItems.length > 0 || dssCrossSellCat !== 'all') {
    crossSellHTML = `
    <div style="background: linear-gradient(135deg, #fffcf3, #fdf6df); border: 1px solid rgba(201,168,76,0.3); border-radius: 16px; padding: 1.1rem; margin-bottom: 1.25rem; box-shadow: 0 4px 15px rgba(201,168,76,0.05); text-align: left; animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: 360ms; opacity: 0;">
      <div style="font-weight: 800; font-size: 0.88rem; color: var(--maroon-dark); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 6px;">
        <svg style="width:16px;height:16px;color:var(--maroon);display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"></path><path d="M12 15v7"></path><path d="M8 22h8"></path><path d="m15 2-8 8"></path></svg>
        Rekomendasi Menu Pelengkap
      </div>
      <div style="font-size: 0.72rem; color: #7c6d48; margin-bottom: 0.85rem; line-height: 1.4;">
        Biar santapan makin sempurna, Chef menyarankan minuman segar atau camilan pelengkap ini:
      </div>

      <!-- Category Filter -->
      <div style="display:flex; align-items:center; gap:0.45rem; flex-wrap:wrap; margin-bottom: 1rem; border-bottom: 1px dashed rgba(201,168,76,0.18); padding-bottom: 0.8rem;">
        <span style="font-size:0.68rem; font-weight:800; color:var(--maroon-dark); text-transform:uppercase; letter-spacing:0.05em; width:65px;">Kategori:</span>
        <button class="cross-sell-cat-btn" data-cat="all" onclick="setCrossSellCategory('all')" style="border:1.5px solid ${dssCrossSellCat === 'all' ? 'var(--maroon)' : 'var(--gold)'}; border-radius:20px; padding:0.3rem 0.85rem; font-size:0.68rem; font-weight:800; cursor:pointer; transition:all 0.22s ease; ${dssCrossSellCat === 'all' ? 'background:var(--maroon); color:white; box-shadow: 0 2px 6px rgba(107,28,42,0.15);' : 'background:white; color:var(--maroon);'}" onmouseover="if(this.getAttribute('data-cat')!=='${dssCrossSellCat}') { this.style.borderColor='var(--maroon)'; this.style.background='var(--gold-pale)'; }" onmouseout="if(this.getAttribute('data-cat')!=='${dssCrossSellCat}') { this.style.borderColor='var(--gold)'; this.style.background='white'; }">Semua</button>
        ${activeCrossSellCats.map(catName => {
          const isSelected = dssCrossSellCat === catName;
          return `
            <button class="cross-sell-cat-btn" data-cat="${catName}" onclick="setCrossSellCategory('${catName}')" style="border:1.5px solid ${isSelected ? 'var(--maroon)' : 'var(--gold)'}; border-radius:20px; padding:0.3rem 0.85rem; font-size:0.68rem; font-weight:800; cursor:pointer; transition:all 0.22s ease; ${isSelected ? 'background:var(--maroon); color:white; box-shadow: 0 2px 6px rgba(107,28,42,0.15);' : 'background:white; color:var(--maroon);'}" onmouseover="if(this.getAttribute('data-cat')!=='${dssCrossSellCat}') { this.style.borderColor='var(--maroon)'; this.style.background='var(--gold-pale)'; }" onmouseout="if(this.getAttribute('data-cat')!=='${dssCrossSellCat}') { this.style.borderColor='var(--gold)'; this.style.background='white'; }">${catName}</button>
          `;
        }).join('')}
      </div>

      <div id="crossSellItemsList" style="display: flex; flex-direction: column; gap: 0.7rem;">
        <!-- Injected dynamically via renderCrossSellItems() -->
      </div>
    </div>
    `;
    setTimeout(() => renderCrossSellItems(), 20);
  }

  return`<div class="winner-banner" style="background: linear-gradient(135deg, var(--maroon), var(--maroon-dark)); border-radius: 18px; padding: 1.5rem 1rem; color: white; text-align: center; margin-bottom: 1.25rem; box-shadow: 0 6px 20px rgba(128,0,0,0.12); border: 1px solid rgba(255,255,255,0.06); animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0;">
    ${trophySVG}
    <div class="w-sub" style="font-size: 0.7rem; color: rgba(255,255,255,0.72); font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 0.35rem;">Saran Utama Hidangan Anda</div>
    ${bestImageHTML}
    <div class="w-name" style="font-weight: 900; font-size: 1.3rem; letter-spacing: -0.02em; margin-bottom: 0.25rem;">${best.nama_menu}</div>
    <div class="w-pct" style="font-size: 0.78rem; font-weight: 600; color: var(--gold-light); background: rgba(255,255,255,0.08); padding: 0.25rem 0.7rem; border-radius: 20px; display: inline-block; margin-bottom: 0.8rem;">Tingkat Kecocokan: ${best.match_percentage}%</div>
    <div class="w-tags" style="display: flex; gap: 0.5rem; justify-content: center; font-size: 0.74rem;"><span class="w-tag" style="background: rgba(255,255,255,0.12); padding: 0.2rem 0.6rem; border-radius: 6px; font-weight: 700;">${best.harga_format}</span><span class="w-tag" style="background: rgba(255,255,255,0.12); padding: 0.2rem 0.6rem; border-radius: 6px; font-weight: 700;">Skor: ${best.final_score.toFixed(3)}</span></div>
  </div>

  <div style="background: var(--gold-pale); border: 1px solid rgba(201,168,76,0.22); border-radius: 16px; padding: 1.1rem; margin-bottom: 1.25rem; text-align: left; animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: 150ms; opacity: 0;">
    <div style="font-weight: 800; font-size: 0.82rem; color: var(--maroon-dark); margin-bottom: 0.6rem; display: flex; align-items: center; gap: 6px;">
      <svg style="width:15px;height:15px;color:var(--maroon);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
      Kenapa ${best.nama_menu} Paling Sesuai?
    </div>
    ${generateNarrative(best, weights)}
    ${CRITERIA.map((c,i)=>{
      const sMap=['skor_rasa','skor_nutrisi','skor_jenis_hidangan'];
      const sk = Math.round(best[sMap[i]]||0);
      const desc = (skorDesc[sMap[i]]||{})[sk] || '-';
      return `<div style="display:flex;align-items:center;gap:.6rem;padding:.45rem 0;border-bottom:1px solid rgba(201,168,76,0.12); animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: ${200 + (i * 100)}ms; opacity: 0;">
        <div style="width:30px;height:30px;border-radius:50%;background:white;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          ${getCriterionSVG(c.id, 16)}
        </div>
        <div style="flex:1;font-size:.76rem;color:var(--text-mid);line-height:1.3;"><strong>${c.name}</strong><br><span style="color:#888;font-size:0.7rem;">${desc}</span></div>
        <span style="font-size:.7rem;font-weight:800;color:var(--maroon);background:rgba(128,0,0,.06);padding:.15rem .5rem;border-radius:6px;">Bobot ${Math.round(wArr[i]*100)}%</span>
      </div>`;
    }).join('')}
    <div style="margin-top:.7rem;font-size:.66rem;color:#b88a00;text-align:right;display:flex;align-items:center;justify-content:end;gap:4px;">
      <span>Penyelarasan Konsisten</span>
      <svg style="width:12px;height:12px;color:#22c55e;display:inline-block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
    </div>
  </div>

  <div style="background:white;border-radius:16px;padding:1.1rem;box-shadow:var(--shadow-sm);border:1px solid rgba(0,0,0,0.03);margin-bottom:1.25rem; animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: 450ms; opacity: 0;">
    <div style="font-weight: 800; font-size: 0.82rem; color: var(--text); margin-bottom: 0.8rem; text-align: left; display: flex; align-items: center; gap: 6px;">
      <svg style="width:15px;height:15px;color:var(--maroon);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
      Peringkat Menu Hidangan Utama
    </div>
    <div class="rank-list">
      ${ranked.map((m,r)=>{
        const rankImageStyle = m.image 
          ? `background: url('${m.image}') center/cover no-repeat;` 
          : `background: linear-gradient(135deg,#f5e4be,#e8c97a);`;
        const rankImageContent = m.image ? '' : getMenuFallbackSVG(20);
        return `<div class="rank-row" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0; border-bottom: ${r < ranked.length-1 ? '1px solid #f9f9f9' : 'none'}; animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: ${500 + (r * 120)}ms; opacity: 0;">
          <div class="rank-image" style="width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0; border: 1.5px solid #eaeaea; box-shadow: 0 2px 6px rgba(0,0,0,0.05); ${rankImageStyle} display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">${rankImageContent}</div>
          <div class="rank-info" style="flex: 1; text-align: left;">
            <div class="rank-name" style="font-weight: 700; font-size: 0.8rem; color: var(--text);">${m.nama_menu}</div>
            <div style="font-size:.68rem;color:#aaa;margin-bottom:.15rem;">${m.harga_format}</div>
            <div class="rank-bar-track" style="height: 5px; background: #f0f0f0; border-radius: 10px; overflow: hidden; margin-top: 0.15rem; width: 100%;"><div class="${fills[r]||'rfn'} rank-bar-fill" data-w="${m.match_percentage}%" style="width:0%; height:100%; border-radius:10px; transition: width 0.8s ease;"></div></div>
            <div class="rank-pct-label" style="font-size: 0.66rem; color: #888; margin-top: 0.2rem; font-weight: 600;">Kecocokan: ${m.match_percentage}%</div>
          </div>
          <div class="rank-medal" style="flex-shrink: 0; display: flex; align-items: center; justify-content: center;">${medalsMarkup(r)}</div>
        </div>`;
      }).join('')}
    </div>
  </div>

  <button class="btn-order-now" onclick="closeDSSOpenOrder(${best.id})" style="display: flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 0.5rem;">
    <svg style="width:16px;height:16px;color:white;display:inline-block;vertical-align:middle;margin-right:6px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
    Pesan ${best.nama_menu} Sekarang!
  </button>
  <button class="btn-restart-dss" onclick="resetDSS()" style="background: transparent; color: #888; border: 1.5px solid #eaeaea; border-radius: 14px; padding: 0.7rem; font-size: 0.82rem; font-weight: 700; cursor: pointer; transition: all 0.2s; width: 100%; margin-top: 0.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: center; gap: 6px;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
    <svg style="width:14px;height:14px;color:currentColor;display:inline-block;vertical-align:middle;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path></svg>
    Reset Preferensi Kriteria
  </button>

  ${crossSellHTML}`;
}

function closeDSSOpenOrder(bestId){
  const bid = parseInt(bestId);
  if (!qty[bid] || qty[bid] < 1) {
    chgQty(bid, 1);
  }
  closeDSS();
  setTimeout(()=>{openOrder();},300);
}

function resetDSS(){
  pairAns={rasa_vs_nutrisi:null,rasa_vs_jenis:null,nutrisi_vs_jenis:null};
  dssScreen=0;
  dssApiResult=null;
  dssCrossSellCat='all';
  dssCrossSellCrit='all';
  renderDSS();
}
function dssGo(s){dssScreen=s;renderDSS();}


/* ═══════════════════════════════════════
   SHARED UTILS
═══════════════════════════════════════ */
function handleOverlayClick(e,id){if(e.target===document.getElementById(id)){if(id==='orderOverlay')closeOrder();else closeDSS();}}

function animW(){
  document.querySelectorAll('[data-w]').forEach(el=>{
    setTimeout(()=>{el.style.width=el.dataset.w;},100);
  });
}

/* scroll reveal */
const srObs=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting)e.target.classList.add('vis');});
},{threshold:.12});
document.querySelectorAll('.sr').forEach(el=>srObs.observe(el));

function initLandingSteppers() {
  if (typeof renderLandingSteppers === 'function') {
      renderLandingSteppers();
  }
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initLandingSteppers);
} else {
  initLandingSteppers();
}