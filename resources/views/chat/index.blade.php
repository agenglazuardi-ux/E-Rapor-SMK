@extends('layouts.app')
@section('title', 'Pesan')
@section('eyebrow', 'Admin & Guru')
@section('content')
<style>
    .chat-box { display: grid; grid-template-columns: 320px 1fr; height: calc(100vh - 220px); min-height: 480px; background: var(--card); border: 1px solid var(--line); border-radius: 18px; overflow: hidden; }
    .chat-side { border-right: 1px solid var(--line); display: flex; flex-direction: column; min-height: 0; }
    .chat-cari { padding: 14px; border-bottom: 1px solid var(--line); }
    .chat-daftar { overflow-y: auto; flex: 1; }
    .chat-item { display: flex; gap: 12px; align-items: center; padding: 12px 14px; cursor: pointer; border-bottom: 1px solid #f1ede2; }
    .chat-item:hover { background: #faf7ef; } .chat-item.aktif { background: #eef5f0; }
    .chat-avatar { width: 42px; height: 42px; border-radius: 50%; background: var(--forest); color: #fff; display: grid; place-items: center; font-weight: 700; font-size: .9rem; flex-shrink: 0; }
    .chat-item .info { min-width: 0; flex: 1; }
    .chat-item .nama { font-weight: 700; font-size: .92rem; display: flex; gap: 6px; align-items: center; }
    .chat-item .cuplikan { font-size: .8rem; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .chat-item .meta { text-align: right; font-size: .7rem; color: var(--muted); display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }
    .chat-item .belum { background: var(--amber); color: var(--forest); font-weight: 700; border-radius: 99px; min-width: 20px; padding: 0 6px; font-size: .72rem; text-align: center; }
    .chat-main { display: flex; flex-direction: column; min-height: 0; min-width: 0; background: #fbf9f3; }
    .chat-head { display: flex; align-items: center; gap: 12px; padding: 12px 18px; background: var(--card); border-bottom: 1px solid var(--line); }
    .chat-head .nama { font-weight: 700; }
    .chat-pesan { flex: 1; overflow-y: auto; padding: 18px; display: flex; flex-direction: column; gap: 6px; }
    .chat-tgl { align-self: center; font-size: .72rem; color: var(--muted); background: #efeadc; padding: 2px 12px; border-radius: 99px; margin: 10px 0 4px; }
    .gelembung { max-width: min(75%, 520px); padding: 8px 12px; border-radius: 14px; font-size: .9rem; line-height: 1.4; word-wrap: break-word; overflow-wrap: anywhere; white-space: pre-wrap; }
    .gelembung .waktu { display: block; text-align: right; font-size: .66rem; margin-top: 2px; opacity: .7; white-space: nowrap; }
    .gelembung.saya { align-self: flex-end; background: var(--forest); color: #fff; border-bottom-right-radius: 4px; }
    .gelembung.lawan { align-self: flex-start; background: #fff; border: 1px solid var(--line); border-bottom-left-radius: 4px; }
    .gelembung .baca { color: #7fe0b0; font-weight: 700; }
    .chat-form { display: flex; gap: 10px; align-items: flex-end; padding: 12px 16px; background: var(--card); border-top: 1px solid var(--line); }
    .chat-form textarea { resize: none; max-height: 120px; }
    .chat-kosong { margin: auto; text-align: center; color: var(--muted); padding: 24px; }
    .chat-galat { color: #b42318; font-size: .8rem; padding: 0 18px 8px; background: var(--card); }
    .chat-kembali { display: none; }
    @media (max-width: 767px) {
        .chat-box { grid-template-columns: 1fr; height: calc(100vh - 190px); }
        .chat-box.buka .chat-side, .chat-box:not(.buka) .chat-main { display: none; }
        .chat-kembali { display: inline-block; }
        .gelembung { max-width: 85%; }
    }
</style>

<div class="chat-box" id="chatBox" data-testid="chat-box">
    <div class="chat-side">
        <div class="chat-cari"><input type="search" id="chatCari" class="form-control" placeholder="Cari nama guru / admin…" autocomplete="off" data-testid="chat-cari"></div>
        <div class="chat-daftar" id="chatDaftar" data-testid="chat-daftar"></div>
        <div class="chat-kosong d-none" id="chatKosong">Tidak ada admin/guru lain yang ditemukan.</div>
    </div>
    <div class="chat-main">
        <div class="chat-kosong" id="chatPlaceholder"><i class="bi bi-chat-dots fs-1 d-block mb-2"></i>Pilih rekan di sebelah kiri untuk memulai percakapan.</div>
        <div class="d-none flex-column h-100" id="chatPanel" style="display:flex;min-height:0;flex:1">
            <div class="chat-head">
                <button type="button" class="btn btn-sm btn-light chat-kembali" id="chatKembali" aria-label="Kembali ke daftar"><i class="bi bi-arrow-left"></i></button>
                <div class="chat-avatar" id="chatAvatar"></div>
                <div><div class="nama" id="chatNama" data-testid="chat-nama-lawan"></div><span class="role-badge" id="chatRole"></span></div>
            </div>
            <div class="chat-pesan" id="chatPesan" data-testid="chat-pesan"></div>
            <div class="chat-galat d-none" id="chatGalat" data-testid="chat-galat"></div>
            <form class="chat-form" id="chatForm" autocomplete="off">
                <textarea id="chatInput" class="form-control" rows="1" maxlength="2000" placeholder="Tulis pesan… (Enter kirim, Shift+Enter baris baru)" data-testid="chat-input"></textarea>
                <button type="submit" class="btn btn-primary text-nowrap" id="chatKirim" data-testid="chat-kirim"><i class="bi bi-send me-1"></i>Kirim</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var URL_KONTAK = @json(route('chat.kontak'));
    var URL_CHAT = @json(url('/chat'));
    var CSRF = @json(csrf_token());
    var DENGAN_AWAL = {{ (int) $dengan }};
    window.__chatAktif = true;

    var el = {
        box: document.getElementById('chatBox'), cari: document.getElementById('chatCari'), daftar: document.getElementById('chatDaftar'),
        kosong: document.getElementById('chatKosong'), placeholder: document.getElementById('chatPlaceholder'), panel: document.getElementById('chatPanel'),
        nama: document.getElementById('chatNama'), role: document.getElementById('chatRole'), avatar: document.getElementById('chatAvatar'),
        pesan: document.getElementById('chatPesan'), galat: document.getElementById('chatGalat'), form: document.getElementById('chatForm'),
        input: document.getElementById('chatInput'), kirim: document.getElementById('chatKirim'), kembali: document.getElementById('chatKembali')
    };
    var kontak = [], aktif = null, pesan = [], adaLama = false, terbacaSampai = 0, sedangKirim = false;

    function ambil(url, opsi) {
        opsi = opsi || {};
        opsi.credentials = 'same-origin';
        opsi.headers = Object.assign({ 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, opsi.headers || {});
        return fetch(url, opsi).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (d) {
                if (!r.ok) {
                    var pesanGalat = d.message || (r.status === 419 ? 'Sesi habis, muat ulang halaman.' : 'Terjadi kesalahan (' + r.status + ').');
                    if (d.errors) { pesanGalat = Object.keys(d.errors).map(function (k) { return d.errors[k][0]; }).join(' '); }
                    var e = new Error(pesanGalat); e.status = r.status; throw e;
                }
                return d;
            });
        });
    }
    function tampilGalat(e) { el.galat.textContent = e.message || 'Terjadi kesalahan.'; el.galat.classList.remove('d-none'); setTimeout(function () { el.galat.classList.add('d-none'); }, 5000); }
    function inisial(n) { var p = n.trim().split(/\s+/); return ((p[0] || '')[0] || '').toUpperCase() + ((p[1] || '')[0] || '').toUpperCase(); }
    function labelRole(r) { return r === 'admin' ? 'Admin' : 'Guru'; }
    function hariIni(d) { return d.toDateString() === new Date().toDateString(); }
    function jam(d) { return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }); }
    function labelTanggal(d) {
        var kemarin = new Date(); kemarin.setDate(kemarin.getDate() - 1);
        if (hariIni(d)) return 'Hari ini';
        if (d.toDateString() === kemarin.toDateString()) return 'Kemarin';
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    }
    function buat(tag, kelas, teks) { var n = document.createElement(tag); if (kelas) n.className = kelas; if (teks !== undefined) n.textContent = teks; return n; }

    function perbaruiBadge(n) {
        var b = document.getElementById('chat-badge');
        if (!b) return;
        b.textContent = n > 99 ? '99+' : n;
        b.classList.toggle('d-none', !n);
    }

    function renderDaftar() {
        var q = el.cari.value.trim().toLowerCase();
        el.daftar.textContent = '';
        var tampil = kontak.filter(function (k) { return !q || k.nama.toLowerCase().indexOf(q) !== -1; });
        el.kosong.classList.toggle('d-none', tampil.length > 0);
        tampil.forEach(function (k) {
            var item = buat('div', 'chat-item' + (aktif && aktif.id === k.id ? ' aktif' : ''));
            item.setAttribute('data-testid', 'chat-kontak-' + k.id);
            item.addEventListener('click', function () { bukaChat(k.id); });
            item.appendChild(buat('div', 'chat-avatar', inisial(k.nama)));
            var info = buat('div', 'info');
            var nama = buat('div', 'nama'); nama.appendChild(buat('span', null, k.nama)); nama.appendChild(buat('span', 'role-badge role-' + k.role, labelRole(k.role)));
            info.appendChild(nama);
            info.appendChild(buat('div', 'cuplikan', k.terakhir ? (k.dari_saya ? 'Anda: ' : '') + k.terakhir : 'Belum ada pesan'));
            item.appendChild(info);
            var meta = buat('div', 'meta');
            if (k.waktu) { var d = new Date(k.waktu); meta.appendChild(buat('span', null, hariIni(d) ? jam(d) : d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }))); }
            if (k.belum > 0) { var b = buat('span', 'belum', k.belum > 99 ? '99+' : k.belum); b.setAttribute('data-testid', 'chat-belum-' + k.id); meta.appendChild(b); }
            item.appendChild(meta);
            el.daftar.appendChild(item);
        });
    }

    // mode: 'bawah' = selalu ke pesan terbawah, 'otomatis' = ke bawah jika sudah dekat bawah, 'diam' = jangan geser.
    function renderPesan(mode) {
        var dekatBawah = el.pesan.scrollHeight - el.pesan.scrollTop - el.pesan.clientHeight < 80;
        el.pesan.textContent = '';
        if (adaLama) {
            var tombol = buat('button', 'btn btn-sm btn-light align-self-center', 'Muat pesan sebelumnya');
            tombol.type = 'button'; tombol.setAttribute('data-testid', 'chat-muat-lama');
            tombol.addEventListener('click', muatLama);
            el.pesan.appendChild(tombol);
        }
        if (!pesan.length) { el.pesan.appendChild(buat('div', 'chat-kosong', 'Belum ada pesan. Tulis pesan pertama Anda di bawah.')); }
        var tglTerakhir = '';
        pesan.forEach(function (m) {
            var d = new Date(m.dibuat);
            if (d.toDateString() !== tglTerakhir) { el.pesan.appendChild(buat('div', 'chat-tgl', labelTanggal(d))); tglTerakhir = d.toDateString(); }
            var g = buat('div', 'gelembung ' + (m.dari_saya ? 'saya' : 'lawan'), m.isi);
            g.setAttribute('data-testid', 'chat-pesan-' + m.id);
            var w = buat('span', 'waktu', jam(d) + ' ');
            if (m.dari_saya) { w.appendChild(buat('span', m.id <= terbacaSampai ? 'baca' : '', m.id <= terbacaSampai ? '✓✓' : '✓')); }
            g.appendChild(w);
            el.pesan.appendChild(g);
        });
        if (mode === 'bawah' || (mode === 'otomatis' && dekatBawah)) { el.pesan.scrollTop = el.pesan.scrollHeight; }
    }

    function muatKontak() {
        return ambil(URL_KONTAK).then(function (d) {
            kontak = d.kontak;
            if (aktif && !document.hidden) { kontak.forEach(function (k) { if (k.id === aktif.id) { k.belum = 0; } }); }
            renderDaftar();
            perbaruiBadge(d.total_belum);
        }).catch(function () {});
    }

    function bukaChat(id) {
        var k = kontak.filter(function (x) { return x.id === id; })[0];
        if (!k) { return; }
        aktif = k; pesan = []; adaLama = false; terbacaSampai = 0;
        el.placeholder.classList.add('d-none'); el.panel.classList.remove('d-none'); el.box.classList.add('buka');
        el.nama.textContent = k.nama; el.avatar.textContent = inisial(k.nama);
        el.role.textContent = labelRole(k.role); el.role.className = 'role-badge role-' + k.role;
        try { history.replaceState(null, '', URL_CHAT + '?dengan=' + k.id); } catch (e) {}
        renderDaftar(); renderPesan('bawah');
        ambil(URL_CHAT + '/' + k.id + '/pesan').then(function (d) {
            if (!aktif || aktif.id !== k.id) { return; }
            pesan = d.pesan; adaLama = d.ada_lama; terbacaSampai = d.terbaca_sampai;
            renderPesan('bawah'); muatKontak();
        }).catch(tampilGalat);
        el.input.focus();
    }

    function muatLama() {
        if (!aktif || !pesan.length) { return; }
        var id = aktif.id, sisa = el.pesan.scrollHeight - el.pesan.scrollTop;
        ambil(URL_CHAT + '/' + id + '/pesan?sebelum=' + pesan[0].id).then(function (d) {
            if (!aktif || aktif.id !== id) { return; }
            pesan = d.pesan.concat(pesan); adaLama = d.ada_lama;
            renderPesan('diam'); el.pesan.scrollTop = el.pesan.scrollHeight - sisa;
        }).catch(tampilGalat);
    }

    function pollPesan() {
        if (!aktif || document.hidden) { return; }
        var id = aktif.id, terakhir = pesan.length ? pesan[pesan.length - 1].id : 0;
        ambil(URL_CHAT + '/' + id + '/pesan?setelah=' + terakhir).then(function (d) {
            if (!aktif || aktif.id !== id) { return; }
            var baru = false;
            d.pesan.forEach(function (m) { if (!pesan.some(function (x) { return x.id === m.id; })) { pesan.push(m); baru = true; } });
            var tickBerubah = d.terbaca_sampai !== terbacaSampai;
            terbacaSampai = d.terbaca_sampai;
            if (baru || tickBerubah) { renderPesan('otomatis'); }
            if (baru) { muatKontak(); }
        }).catch(function () {});
    }

    function autoTinggi() { el.input.style.height = 'auto'; el.input.style.height = Math.min(el.input.scrollHeight, 120) + 'px'; }

    function kirimPesan() {
        if (!aktif || sedangKirim) { return; }
        var isi = el.input.value.trim();
        if (!isi) { return; }
        var id = aktif.id;
        sedangKirim = true; el.kirim.disabled = true;
        ambil(URL_CHAT + '/' + id + '/pesan', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }, body: JSON.stringify({ isi: isi }) })
            .then(function (d) {
                el.input.value = ''; autoTinggi();
                if (aktif && aktif.id === id && !pesan.some(function (x) { return x.id === d.pesan.id; })) { pesan.push(d.pesan); renderPesan('bawah'); }
                muatKontak();
            })
            .catch(tampilGalat)
            .then(function () { sedangKirim = false; el.kirim.disabled = false; el.input.focus(); });
    }

    el.form.addEventListener('submit', function (e) { e.preventDefault(); kirimPesan(); });
    el.input.addEventListener('input', autoTinggi);
    el.input.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); kirimPesan(); } });
    el.cari.addEventListener('input', renderDaftar);
    el.kembali.addEventListener('click', function () {
        el.box.classList.remove('buka'); aktif = null; renderDaftar();
        try { history.replaceState(null, '', URL_CHAT); } catch (e) {}
    });
    document.addEventListener('visibilitychange', function () { if (!document.hidden) { pollPesan(); muatKontak(); } });

    setInterval(pollPesan, 3000);
    setInterval(function () { if (!document.hidden) { muatKontak(); } }, 8000);
    muatKontak().then(function () { if (DENGAN_AWAL) { bukaChat(DENGAN_AWAL); } });
})();
</script>
@endpush
