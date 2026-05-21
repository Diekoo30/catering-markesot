/* ═══════════════════════════════════════
   SHARED DATA
═══════════════════════════════════════ */
const MENUS = window.APP_MENUS || [];
// 3 Kriteria AHP Final: Rasa | Nutrisi | Jenis Hidangan
const CRITERIA=[
  {id:'rasa',          name:'Rasa',           icon:'😋',desc:'Gurih Pedas vs Gurih Segar'},
  {id:'nutrisi',       name:'Nutrisi',         icon:'🥩',desc:'Protein Daging vs Protein Telur'},
  {id:'jenis_hidangan',name:'Jenis Hidangan',  icon:'🍲',desc:'Berkuah Hangat vs Kering/Goreng'},
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

window.addEventListener('DOMContentLoaded', () => {
  if(localStorage.getItem('mk_auto_open') === '1') {
     localStorage.removeItem('mk_auto_open');
     setTimeout(openOrder, 500); 
  }
});

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
      <div style="font-size: 3rem; margin-bottom: 1rem;">🔒</div>
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
    <div style="font-size:0.75rem; color:#b88a00; margin-top:4px; padding:6px 10px; background:#fffbe6; border-radius:8px; border:1px solid #ffe58f;">⏰ Minimal pemesanan <strong>${leadMins} menit</strong> sebelum waktu pesanan dibutuhkan.</div>
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
// pairAns: 3 pilihan VS (kiri/kanan/sama)
let pairAns={rasa_vs_nutrisi:null,rasa_vs_jenis:null,nutrisi_vs_jenis:null};
let dssScreen=0;
let dssApiResult=null;
const TOTAL_Q=3;

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
  // Screen: 0=Intro | 1-3=Pertanyaan VS | 4=Loading | 5+=Hasil
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
  const labels=['Yuk Mulai!','Bandingkan Kriteria','Bandingkan Kriteria','Bandingkan Kriteria','Menghitung via AI...','Hasilnya ada!'];
  document.getElementById('dssPLabel').textContent=labels[dssScreen]||'';
  let dots='';
  for(let i=0;i<TOTAL_Q;i++){const done=i<answered,active=i===answered;dots+=`<div class="dss-dot ${done?'done':active?'active':''}"></div>`;}
  document.getElementById('dssPDots').innerHTML=dots;
}

// Payload 3 pilihan → backend bangun matriks 3×3
function buildPayload(){
  return {
    rasa_vs_nutrisi:  pairAns.rasa_vs_nutrisi  || 'sama',
    rasa_vs_jenis:    pairAns.rasa_vs_jenis    || 'sama',
    nutrisi_vs_jenis: pairAns.nutrisi_vs_jenis || 'sama',
  };
}

// ─── Kirim ke Backend API & Dapatkan Hasil Ranking ──────────
async function fetchDSSResult(){
  const payload = buildPayload();

  // ── DEBUG: Tampilkan payload yang dikirim di console browser ──
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

    // ── DEBUG: Tampilkan response dari backend ──
    console.log('🟢 [AHP] Response status:', resp.status);
    console.log('🟢 [AHP] Weights:', json.weights);
    console.log('🟢 [AHP] CR:', json.consistency?.cr);
    console.log('🟢 [AHP] Ranking #1:', json.ranked?.[0]?.nama_menu, json.ranked?.[0]?.match_percentage + '%');

    return json;
  } catch(e){
    console.error('🔴 [AHP] Fetch error:', e);
    return { success:false, error:'Gagal terhubung ke server. Periksa koneksi Anda.' };
  }
}

// DSS Screens
function dssIntro(){
  return`<div class="chat-bubble"><div class="chat-avatar">👨‍🍳</div><div class="chat-text">Halo! Saya <strong>Chef Markesot</strong> 🍽️<br><br>Yuk cari menu paling cocok buat kamu sekarang dengan metode AHP!<br><br>Cukup <strong>3 pertanyaan singkat</strong> — super cepat! ⚡</div></div>
  <div style="background:white;border-radius:16px;padding:1.4rem;box-shadow:var(--shadow-sm);margin-bottom:1rem;">
    <div style="font-weight:700;font-size:.95rem;color:var(--text);margin-bottom:.9rem;">📋 Cara kerjanya:</div>
    <div style="display:flex;gap:.8rem;align-items:center;background:var(--gold-pale);border-radius:10px;padding:.8rem .9rem;">
      <div style="font-size:1.5rem">⚖️</div>
      <div style="flex:1"><div style="font-weight:700;font-size:.85rem;color:var(--maroon-dark)">3 pertanyaan — Bandingkan 3 Kriteria</div><div style="font-size:.75rem;color:var(--text-light)">Rasa 😋 vs Nutrisi 🥩 vs Porsi 🍽️ — pilih yang lebih penting!</div></div>
      <div style="background:var(--maroon);color:white;border-radius:6px;padding:.2rem .55rem;font-size:.7rem;font-weight:700;flex-shrink:0">3 soal</div>
    </div>
  </div>
  <button class="btn-primary" onclick="dssGo(1)">Mulai Sekarang 🚀</button>`;
}


// dssPair — Render 1 pertanyaan VS
// idx: 0-3 (4 pasang pertanyaan)
function dssPair(idx){
  const pair=PAIRS[idx];
  const A=CRITERIA[pair.i],B=CRITERIA[pair.j];
  const currentVal=pairAns[pair.key]; // null | 'kiri' | 'kanan' | 'sama'

  const selKiri  = currentVal==='kiri';
  const selKanan = currentVal==='kanan';
  const selSama  = currentVal==='sama';

  return`<div class="pair-counter">Pertanyaan ${idx+1} dari 3<div class="pair-dots">${[0,1,2].map(k=>`<div class="pdot ${k<idx?'done':k===idx?'active':''}"></div>`).join('')}</div></div>
  <div style="background:white;border-radius:16px;padding:1.4rem;box-shadow:var(--shadow-sm);">
    <div style="font-weight:700;font-size:1rem;color:var(--text);margin-bottom:.3rem;">Mana yang lebih penting buatmu?</div>
    <div style="font-size:.82rem;color:var(--text-light);margin-bottom:1.1rem;">Tap salah satu yang lebih kamu prioritaskan saat memilih makan.</div>
    <div class="versus-wrap">
      <div class="versus-side ${selKiri?'sel':''}" onclick="dssSelWinner('${pair.key}','kiri')">
        <div class="versus-icon">${A.icon}</div>
        <div class="versus-name">${A.name}</div>
        <div class="versus-desc">${A.desc}</div>
        ${selKiri?'<div style="font-size:1.2rem">✅</div>':''}
      </div>
      <div class="vs-divider"><div class="vs-circle">VS</div></div>
      <div class="versus-side ${selKanan?'sel':''}" onclick="dssSelWinner('${pair.key}','kanan')">
        <div class="versus-icon">${B.icon}</div>
        <div class="versus-name">${B.name}</div>
        <div class="versus-desc">${B.desc}</div>
        ${selKanan?'<div style="font-size:1.2rem">✅</div>':''}
      </div>
    </div>
    <div class="equal-btn ${selSama?'sel':''}" onclick="dssSelWinner('${pair.key}','sama')">😌 Dua-duanya sama pentingnya</div>
    <button class="btn-primary" onclick="dssNextPair(${idx})" ${currentVal===null?'disabled':''}>
      ${idx<2?'Pertanyaan Berikutnya →':'Lihat Rekomendasiku! 🎉'}
    </button>
  </div>`;
}

// User memilih kiri/kanan/sama untuk pasang tertentu
function dssSelWinner(pairKey, pilihan){
  console.log('🟡 [AHP] User pilih:', pairKey, '=', pilihan);
  pairAns[pairKey]=pilihan;
  console.log('🟡 [AHP] pairAns sekarang:', JSON.parse(JSON.stringify(pairAns)));
  // Re-render pertanyaan yang sedang tampil
  const idx=PAIRS.findIndex(p=>p.key===pairKey);
  document.getElementById('dssBody').innerHTML=dssPair(idx);
  updateDSSProgress();setTimeout(animW,50);
}
function dssNextPair(idx){
  const key=PAIRS[idx].key;
  if(!pairAns[key])return;
  // VS 1-3 → screen 1-3; setelah pertanyaan ke-3 → screen 4 (loading)
  dssScreen = idx < 2 ? idx+2 : 4;
  renderDSS();
}

// ─── Pertanyaan Preferensi (Bagian 2 dari Kuesioner) ──────────
// Sinkron dengan 4 kriteria baru: Harga, Jenis Hidangan, Nutrisi, Rasa
const PREF_QS=[
  {cid:'harga',icon:'💰',q:'Gimana kondisi kantongmu hari ini?',hint:'Jujur aja, ini rahasia kita berdua! 😄',
   opts:[{v:1,i:'😅',l:'Lagi hemat',s:'Yang penting murah'},{v:2,i:'😊',l:'Biasa aja',s:'Budget normal'},{v:3,i:'🤑',l:'Ada rezeki!',s:'Nggak masalah harga'}]},
  {cid:'jenis_hidangan',icon:'🍲',q:'Pengen makan hidangan yang gimana?',hint:'Sesuaikan dengan cuaca dan kondisimu sekarang.',
   opts:[{v:1,i:'🍳',l:'Yang Kering/Goreng',s:'Nasi goreng, mie goreng'},{v:2,i:'🥣',l:'Yang Berkuah/Hangat',s:'Soto, rawon, kuah'}]},
  {cid:'nutrisi',icon:'🥩',q:'Lagi butuh protein jenis apa?',hint:'Pilih sesuai kebutuhan gizimu.',
   opts:[{v:1,i:'🥚',l:'Telur/Campuran',s:'Telur & bahan campuran'},{v:2,i:'🐔',l:'Protein Ayam',s:'Soto ayam, dll'},{v:3,i:'🥩',l:'Protein Sapi',s:'Rawon, daging sapi'}]},
  {cid:'rasa_dominan',icon:'🌶️',q:'Lagi mood rasa yang seperti apa?',hint:'Pilih yang paling bikin ngiler sekarang.',
   opts:[{v:1,i:'🥣',l:'Gurih Segar/Bening',s:'Soto, kuah bening'},{v:2,i:'🌶️',l:'Gurih Pedas',s:'Nasi goreng, mie pedas'}]},
];

function dssPref(idx){
  const Q=PREF_QS[idx],sel=prefAns[Q.cid];
  return`<div class="chat-bubble"><div class="chat-avatar">👨‍🍳</div><div class="chat-text">Hampir selesai! Pertanyaan ${idx+1} dari 4 — tentang kondisimu hari ini ya 😊</div></div>
  <div style="background:white;border-radius:16px;padding:1.4rem;box-shadow:var(--shadow-sm);">
    <div style="font-size:1.8rem;margin-bottom:.5rem;">${Q.icon}</div>
    <div style="font-weight:700;font-size:1rem;color:var(--text);margin-bottom:.3rem;">${Q.q}</div>
    <div style="font-size:.82rem;color:var(--text-light);margin-bottom:1.1rem;">${Q.hint}</div>
    <div class="choice-grid">
      ${Q.opts.map(o=>`<div class="choice-btn ${sel===o.v?'sel':''}" onclick="dssSelPref('${Q.cid}',${o.v},${idx})">
        <div class="choice-btn-icon">${o.i}</div>
        <div class="choice-btn-label">${o.l}</div>
        <div class="choice-btn-sub">${o.s}</div>
        <div class="choice-check">✓</div>
      </div>`).join('')}
    </div>
    <button class="btn-primary" onclick="dssNextPref(${idx})" ${sel===null||sel===undefined?'disabled':''}>
      ${idx<3?'Pertanyaan Berikutnya →':'Lihat Rekomendasiku! 🎉'}
    </button>
  </div>`;
}

function dssSelPref(cid,v,idx){
  prefAns[cid]=v;
  document.getElementById('dssBody').innerHTML=dssPref(idx);
  updateDSSProgress();setTimeout(animW,50);
}
// Navigasi preferensi: screen 5-8
// Setelah preferensi ke-4 (idx=3) → screen 9 (loading)
function dssNextPref(idx){dssScreen=idx<3?idx+6:9;renderDSS();}

function dssLoading(){
  return`<div style="text-align:center;padding:2rem 1rem;">
    <div class="dss-spinner"></div>
    <div style="font-weight:700;font-size:1rem;color:var(--text);margin-bottom:.4rem;">Sedang menganalisis pilihanmu...</div>
    <div style="font-size:.83rem;color:var(--text-light);margin-bottom:1.2rem;">Kami lagi menghitung menu terbaik buat kamu 🧠</div>
    <div id="loadSteps">
      <div class="load-step" style="animation-delay:.3s">⚖️ Membandingkan kriteria...</div>
      <div class="load-step" style="animation-delay:.9s">📊 Menghitung bobot prioritas...</div>
      <div class="load-step" style="animation-delay:1.5s">🍽️ Mencocokkan dengan menu...</div>
      <div class="load-step" style="animation-delay:2.1s">✅ Menyiapkan hasilnya...</div>
    </div>
  </div>`;
}

async function runDSSLoading(){
  const steps=document.querySelectorAll('.load-step');
  // Animasi loading steps: setiap 600ms satu step muncul
  [400,1000,1600,2200].forEach((d,i)=>setTimeout(()=>{if(steps[i])steps[i].classList.add('done');},d));
  
  // Kirim matriks ke backend API sambil animasi loading berjalan
  dssApiResult = await fetchDSSResult();
  
  // Pastikan minimal 2.9 detik agar UX terasa smooth
  setTimeout(()=>{dssScreen=5;renderDSS();},2900);
}

function dssResult(){
  // Ambil hasil dari cache API yang sudah di-fetch di runDSSLoading()
  const res = dssApiResult;
  const medals=['🥇','🥈','🥉','4️⃣','5️⃣'];
  const fills=['rf1','rf2','rf3','rfn','rfn'];

  // ── Jika API gagal / tidak konsisten ──────────────────────
  if(!res || !res.success){
    const errMsg = res?.error || 'Terjadi kesalahan saat menghubungi server.';
    return`<div style="text-align:center;padding:2rem 1rem;">
      <div style="font-size:3rem;margin-bottom:1rem;">⚠️</div>
      <div style="font-weight:700;font-size:1.1rem;color:var(--maroon);margin-bottom:.8rem;">Jawaban Kurang Konsisten</div>
      <div style="font-size:.88rem;color:var(--text-light);line-height:1.6;margin-bottom:1.5rem;background:#fef2f2;border-radius:12px;padding:1rem;">${errMsg}</div>
      <div style="font-size:.78rem;color:#888;background:#f9f9f9;border-radius:10px;padding:.8rem;margin-bottom:1.5rem;">💡 <strong>Tips:</strong> Coba lebih konsisten saat membandingkan kriteria.<br>Misalnya, jika A > B dan B > C, maka A harus > C juga.</div>
      <button class="btn-primary" onclick="resetDSS()">🔄 Coba Lagi</button>
    </div>`;
  }

  // ── Ambil data dari response API ──────────────────────────
  const ranked = res.ranked;       // Array menu terurut
  const weights = res.weights;     // Bobot kriteria dari AHP
  const consistency = res.consistency;
  const best = ranked[0];

  // Cari emoji menu di data MENUS (menu dari backend landing page)
  const getEmoji = (namaMenu) => {
    const m = MENUS.find(x => x.name.toLowerCase() === namaMenu.toLowerCase());
    return m ? (m.emoji||'🍽️') : '🍽️';
  };

  // Deskripsi skor 3 kriteria
  const skorDesc = {
    skor_rasa:           {1:'Ringan',2:'Gurih Segar',3:'Gurih Pedas'},
    skor_nutrisi:        {1:'Rendah',2:'Protein Telur',3:'Protein Daging'},
    skor_jenis_hidangan: {1:'Kering/Goreng',2:'Campuran',3:'Berkuah/Hangat'},
  };
  const wArr=[weights.rasa||0, weights.nutrisi||0, weights.jenis_hidangan||0];

  return`<div class="winner-banner">
    <span class="w-crown">🏆</span>
    <div class="w-sub">Rekomendasi terbaik untukmu</div>
    <div class="w-name">${getEmoji(best.nama_menu)} ${best.nama_menu}</div>
    <div class="w-pct">Cocok ${best.match_percentage}% dengan preferensimu</div>
    <div class="w-tags"><span class="w-tag">${best.harga_format}</span><span class="w-tag">Skor: ${best.final_score.toFixed(3)}</span></div>
  </div>

  <div style="background:var(--gold-pale);border:1px solid rgba(201,168,76,.3);border-radius:14px;padding:1.2rem;margin-bottom:1rem;">
    <div style="font-weight:700;font-size:.88rem;color:var(--maroon-dark);margin-bottom:.7rem;">💡 Kenapa ${best.nama_menu}?</div>
    ${CRITERIA.map((c,i)=>{
      const sMap=['skor_rasa','skor_nutrisi','skor_jenis_hidangan'];
      const sk = best[sMap[i]]||0;
      const desc = (skorDesc[sMap[i]]||{})[sk] || '-';
      return `<div style="display:flex;align-items:center;gap:.7rem;padding:.4rem 0;border-bottom:1px solid rgba(201,168,76,.15);">
        <span style="font-size:1.2rem">${c.icon}</span>
        <div style="flex:1;font-size:.8rem;color:var(--text-mid)"><strong>${c.name}</strong><br><span style="color:#888">${desc}</span></div>
        <span style="font-size:.78rem;font-weight:700;color:var(--maroon);background:rgba(128,0,0,.08);padding:.15rem .5rem;border-radius:6px;">Bobot ${Math.round(wArr[i]*100)}%</span>
      </div>`;
    }).join('')}
    <div style="margin-top:.6rem;font-size:.72rem;color:#aaa;">CR = ${consistency.cr.toFixed(4)} ✅ Konsisten</div>
  </div>

  <div style="background:white;border-radius:14px;padding:1.2rem;box-shadow:var(--shadow-sm);margin-bottom:1rem;">
    <div style="font-weight:700;font-size:.88rem;color:var(--text);margin-bottom:.8rem;">📋 Peringkat Semua Menu</div>
    <div class="rank-list">
      ${ranked.map((m,r)=>`<div class="rank-row">
        <div class="rank-emoji">${getEmoji(m.nama_menu)}</div>
        <div class="rank-info">
          <div class="rank-name">${m.nama_menu}</div>
          <div style="font-size:.72rem;color:#aaa;margin-bottom:.25rem;">${m.harga_format}</div>
          <div class="rank-bar-track"><div class="${fills[r]||'rfn'} rank-bar-fill" data-w="${m.match_percentage}%" style="width:0%"></div></div>
          <div class="rank-pct-label">Kecocokan: ${m.match_percentage}%</div>
        </div>
        <div class="rank-medal">${medals[r]||'🍽️'}</div>
      </div>`).join('')}
    </div>
  </div>

  <button class="btn-order-now" onclick="closeDSSOpenOrder('${best.nama_menu}')">🛒 Pesan ${best.nama_menu} Sekarang!</button>
  <button class="btn-restart-dss" onclick="resetDSS()">🔄 Coba Lagi dengan Jawaban Lain</button>`;
}

function closeDSSOpenOrder(recName){
  closeDSS();
  setTimeout(()=>{openOrder();},300);
}

// Reset semua state DSS ke kondisi awal
function resetDSS(){
  pairAns={rasa_vs_nutrisi:null,rasa_vs_jenis:null,nutrisi_vs_jenis:null};
  dssScreen=0;
  dssApiResult=null;
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

document.addEventListener('DOMContentLoaded', () => {
    if (typeof renderLandingSteppers === 'function') {
        renderLandingSteppers();
    }
});