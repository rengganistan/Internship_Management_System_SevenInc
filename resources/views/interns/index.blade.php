@extends('layouts.dashboard')

@php
    use Illuminate\Support\Str;

    if (!isset($scope)) {
        $scope = 'all';
        $route = request()->route()?->getName();
        $mapRouteScope = [
            'admin.interns.active'    => 'active',
            'admin.interns.completed' => 'completed',
            'admin.interns.exited'    => 'exited',
            'admin.interns.pending'   => 'pending',
            'admin.interns.accepted'  => 'accepted',
            'admin.interns.rejected'  => 'rejected',
        ];
        if ($route && isset($mapRouteScope[$route])) {
            $scope = $mapRouteScope[$route];
        }
    }
@endphp

@section('content')

{{-- ===== MODALS ===== --}}
@push('modals')

{{-- Modal: App (Detail & Edit) --}}
<div id="appModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" data-modal-close></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div id="appModalDialog"
             class="w-full max-w-4xl rounded-[16px] bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-[#DCE7E1] px-5 py-4">
                <h3 id="appModalTitle" class="text-[15px] font-bold text-[#1B3A34]">Modal</h3>
                <button type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F4F8F6] text-[#4B5F5A] hover:bg-[#DCE7E1]"
                        data-modal-close>
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div id="appModalBody" class="max-h-[75vh] overflow-auto p-5"></div>
        </div>
    </div>
</div>

{{-- Modal: Konfirmasi Hapus --}}
<div id="confirmModal" class="fixed inset-0 z-[110] hidden">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" data-confirm-close></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-[16px] bg-white shadow-xl overflow-hidden">
            <div class="p-6 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-50">
                    <svg class="h-7 w-7 text-[#D32F2F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <h3 class="mb-2 text-[15px] font-bold text-[#1B3A34]">Hapus data pemagang?</h3>
                <p id="confirmBody" class="text-[12.5px] text-[#4B5F5A] leading-relaxed">
                    Data ini akan dihapus permanen dan tidak dapat dikembalikan.
                </p>
            </div>
            <div class="flex justify-center gap-3 border-t border-[#DCE7E1] px-5 py-4">
                <button class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#1B3A34] hover:bg-[#F4F8F6]" data-confirm-close>Batal</button>
                <button id="confirmYes" class="rounded-[9px] bg-[#D32F2F] px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>

@endpush

{{-- ===== TOAST ===== --}}
<div id="toastStack" class="fixed bottom-5 right-5 z-[200] flex flex-col gap-2"></div>

{{-- ===== KONTEN UTAMA ===== --}}
<div class="min-h-screen bg-[#F4F8F6] p-4 sm:p-6 lg:p-7">

    {{-- Header --}}
    <div class="mb-6">
        <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Data Pemagang</p>
        <h1 class="text-2xl font-extrabold tracking-tight text-[#1B3A34] sm:text-[28px]">
            {{ $title ?? 'Semua Pemagang' }}
        </h1>
        <p class="mt-1 text-sm text-[#4B5F5A]">
            Pantau daftar pemagang, status, dan tindakan admin dalam satu tampilan.
        </p>
    </div>

    {{-- Card utama --}}
    <div class="overflow-hidden rounded-[12px] border border-[#DCE7E1] bg-white shadow-sm">

        {{-- Toolbar: filter tab + search --}}
        <div class="flex flex-col gap-3 border-b border-[#DCE7E1] px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">

            {{-- Filter tabs --}}
            <div id="filterTabs" class="flex flex-wrap gap-1.5 rounded-[9px] border border-[#DCE7E1] bg-[#F4F8F6] p-1">
                @foreach([
                    'all'       => 'Semua',
                    'pending'   => 'Pending',
                    'accepted'  => 'Diterima',
                    'rejected'  => 'Ditolak',
                    'active'    => 'Aktif',
                    'completed' => 'Selesai',
                    'exited'    => 'Keluar',
                ] as $key => $label)
                <button type="button"
                    data-filter="{{ $key }}"
                    class="filter-tab rounded-[6px] px-3 py-1.5 text-[12.5px] font-semibold transition
                        {{ ($scope === $key || ($key === 'all' && $scope === 'all')) ? 'bg-white text-[#1F5F3F] shadow-sm' : 'text-[#4B5F5A] hover:text-[#1B3A34]' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- Search + Tambah --}}
            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2">
                    <svg class="h-4 w-4 text-[#4B5F5A]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input id="intern-search" type="search" placeholder="Cari nama / universitas..."
                        class="w-48 border-0 bg-transparent text-[13px] text-[#1B3A34] outline-none placeholder:text-[#4B5F5A] sm:w-56" />
                </label>
            </div>
        </div>

        {{-- Tabel --}}
        <div id="tableWrap" class="overflow-x-auto" data-base="{{ url('/admin/interns') }}">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead>
                    <tr>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white rounded-tl-none first:rounded-tl-lg">Nama Pendaftar</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Divisi</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Universitas</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Tgl Daftar</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Status</th>
                        <th class="bg-[#1B3A34] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white text-right rounded-tr-none last:rounded-tr-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody id="rows" class="divide-y divide-[#DCE7E1] bg-white">
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-sm text-[#4B5F5A]">
                            Memuat data pemagang...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pending bar (bulk status change) --}}
        <div id="pendingBar" class="hidden border-t border-[#DCE7E1] bg-amber-50 px-5 py-3">
            <div class="flex flex-wrap items-center justify-end gap-2">
                <span class="text-sm text-amber-800">
                    <strong id="pendingCount">0</strong> perubahan belum disimpan
                </span>
                <button id="discardAll"
                    class="rounded-[9px] border border-amber-300 px-3 py-1.5 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">
                    Batalkan
                </button>
                <button id="saveAll" disabled
                    class="rounded-[9px] bg-[#2D8659] px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-[#1F5F3F] disabled:cursor-not-allowed disabled:opacity-60">
                    Simpan
                </button>
            </div>
        </div>

        {{-- Pagination --}}
        <div id="pager" class="border-t border-[#DCE7E1] px-5 py-4"></div>
    </div>
</div>

<script>
window.rowData = window.rowData || new Map();

function debounce(fn, ms = 400) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

document.addEventListener('DOMContentLoaded', () => {

    const ADMIN_INTERNS_BASE = @json(url('/admin/interns'));
    const API_URL            = @json(route('admin.interns.api'));
    const SCOPE              = @json($scope ?? 'all');
    const csrf               = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const rowsEl       = document.getElementById('rows');
    const pagerEl      = document.getElementById('pager');
    const searchInput  = document.getElementById('intern-search');
    const pendingBar   = document.getElementById('pendingBar');
    const pendingCount = document.getElementById('pendingCount');
    const saveAllBtn   = document.getElementById('saveAll');
    const discardBtn   = document.getElementById('discardAll');
    const toastStack   = document.getElementById('toastStack');
    const pending      = new Map();

    // ── Status meta ────────────────────────────────────────────────────────────
    const STATUS_META = {
        waiting:   { label: 'Menunggu Review', cls: 'bg-amber-50 text-amber-700 border border-amber-200' },
        pending:   { label: 'Pending',         cls: 'bg-amber-100 text-amber-800 border border-amber-300' },
        accepted:  { label: 'Diterima',        cls: 'bg-[#E8F5E9] text-[#1F5F3F] border border-[#A5D6A7]' },
        active:    { label: 'Magang Aktif',    cls: 'bg-blue-50 text-blue-700 border border-blue-200' },
        completed: { label: 'Selesai',         cls: 'bg-slate-100 text-slate-700 border border-slate-300' },
        rejected:  { label: 'Ditolak',         cls: 'bg-red-50 text-red-700 border border-red-200' },
        exited:    { label: 'Keluar',          cls: 'bg-red-50 text-red-700 border border-red-200' },
    };

    // ── Toast ──────────────────────────────────────────────────────────────────
    function pushToast(message, type = 'success') {
        const isOk = type === 'success';
        const el = document.createElement('div');
        el.className = `flex items-center gap-2 rounded-[10px] border px-4 py-3 text-[13px] font-semibold shadow-lg
            ${isOk
                ? 'bg-[#1B3A34] border-[#2D8659] text-white'
                : 'bg-red-50 border-red-200 text-red-800'}`;
        el.innerHTML = `
            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full ${isOk ? 'bg-[#2D8659]' : 'bg-red-500'}">
                <svg class="h-3 w-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round">
                    ${isOk ? '<polyline points="20 6 9 17 4 12"/>' : '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>'}
                </svg>
            </div>
            <span>${message}</span>`;
        toastStack.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }

    // ── Pending bar ─────────────────────────────────────────────────────────────
    function updatePendingBar() {
        const n = pending.size;
        pendingCount.textContent = n;
        pendingBar.classList.toggle('hidden', n === 0);
        saveAllBtn.disabled = n === 0;
    }

    function markSelect(sel, active) {
        sel.classList.toggle('ring-2', active);
        sel.classList.toggle('ring-amber-400', active);
        sel.classList.toggle('bg-amber-50', active);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────────
    const fmtStr  = (s) => (s && String(s).trim() !== '' ? String(s) : '-');
    const fmtDate = (s) => {
        if (!s) return '-';
        const d = new Date(s);
        if (isNaN(d)) return String(s);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    function initials(name) {
        return (name || 'P').split(/\s+/).filter(Boolean).slice(0, 2).map(w => w[0]?.toUpperCase() || '').join('') || 'P';
    }

    // ── Filter tabs ─────────────────────────────────────────────────────────────
    let activeFilter = SCOPE || 'all';

    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            activeFilter = btn.dataset.filter;
            document.querySelectorAll('.filter-tab').forEach(b => {
                const isActive = b.dataset.filter === activeFilter;
                b.classList.toggle('bg-white', isActive);
                b.classList.toggle('text-[#1F5F3F]', isActive);
                b.classList.toggle('shadow-sm', isActive);
                b.classList.toggle('text-[#4B5F5A]', !isActive);
            });
            loadPage(1);
        });
    });

    // ── Search ──────────────────────────────────────────────────────────────────
    const doSearch = debounce(() => loadPage(1), 400);
    if (searchInput) searchInput.addEventListener('input', doSearch);

    // ── Build badge HTML ────────────────────────────────────────────────────────
    function badgeHtml(status, id) {
        const m = STATUS_META[status] || { label: status, cls: 'bg-gray-100 text-gray-700 border border-gray-300' };
        return `<span id="badge-${id}" class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${m.cls}">${m.label}</span>`;
    }

    // ── Build action cell ───────────────────────────────────────────────────────
    function buildActionCell(it) {
        return `
        <div class="flex items-center justify-end gap-1.5">
            <button type="button" title="Lihat Detail"
                class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#1F5F3F] js-detail"
                data-id="${it.id}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
            <button type="button" title="Edit Data"
                class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-[#DCE7E1] bg-white text-[#4B5F5A] transition hover:border-amber-400 hover:text-amber-600 js-edit"
                data-id="${it.id}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
            </button>
            <button type="button" title="Hapus"
                class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100 js-delete"
                data-id="${it.id}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
            </button>
        </div>`;
    }

    // ── Build status cell (badge + select) ──────────────────────────────────────
    function buildStatusCell(it) {
        const cur = it.internship_status || 'waiting';
        const statusOptions = Object.entries(STATUS_META).map(([val, m]) =>
            `<option value="${val}" ${val === cur ? 'selected' : ''}>${m.label}</option>`
        ).join('');

        return `
        <div class="flex flex-wrap items-center gap-2">
            ${badgeHtml(cur, it.id)}
            <select
                class="js-status-select rounded-[8px] border border-[#DCE7E1] bg-white px-2 py-1.5 text-[11px] font-semibold text-[#1B3A34] outline-none focus:border-[#2D8659]"
                data-url="${it.status_update_url || `${ADMIN_INTERNS_BASE}/${it.id}/status`}"
                data-id="${it.id}" data-current="${cur}">
                ${statusOptions}
            </select>
        </div>`;
    }

    // ── Apply badge setelah update ───────────────────────────────────────────────
    function applyBadge(badgeEl, newVal) {
        const m = STATUS_META[newVal] || { label: newVal, cls: 'bg-gray-100 text-gray-700 border border-gray-300' };
        badgeEl.className = `inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${m.cls}`;
        badgeEl.textContent = m.label;
    }

    // ── Render rows ──────────────────────────────────────────────────────────────
    function renderRows(payload) {
        const { data, meta } = payload;

        if (!data || data.length === 0) {
            rowsEl.innerHTML = `
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-sm text-[#4B5F5A]">
                        Belum ada data pemagang untuk ditampilkan.
                    </td>
                </tr>`;
            pagerEl.innerHTML = '';
            return;
        }

        rowsEl.innerHTML = data.map(it => {
            window.rowData.set(it.id, it);

            const status       = String(it.internship_status || 'waiting').toLowerCase();
            const ini          = initials(it.fullname);
            const institution  = fmtStr(it.institution_name || '-');
            const division     = fmtStr(it.internship_interest || '-');
            const createdAt    = fmtDate(it.created_at);
            const searchText   = `${it.fullname || ''} ${it.email || ''} ${institution} ${division}`.toLowerCase();

            return `
            <tr data-row-id="${it.id}" data-status="${status}"
                data-search-text="${searchText.replace(/"/g, '&quot;')}"
                class="transition hover:bg-[#F4F8F6]">

                {{-- Nama Pendaftar --}}
                <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E8F5E9] text-sm font-bold text-[#1F5F3F]">
                            ${ini}
                        </div>
                        <div>
                            <p class="font-semibold text-[#1B3A34]">${fmtStr(it.fullname || 'Tanpa nama')}</p>
                            <p class="mt-0.5 text-[11.5px] text-[#4B5F5A]">${fmtStr(it.email || '-')}</p>
                        </div>
                    </div>
                </td>

                {{-- Divisi --}}
                <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">${division}</td>

                {{-- Universitas --}}
                <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">${institution}</td>

                {{-- Tgl Daftar --}}
                <td class="px-5 py-4 text-[13px] text-[#4B5F5A]">${createdAt}</td>

                {{-- Status --}}
                <td class="px-5 py-4">${buildStatusCell(it)}</td>

                {{-- Aksi --}}
                <td class="px-5 py-4">${buildActionCell(it)}</td>
            </tr>`;
        }).join('');

        bindStatusListeners();
        bindRowActions();
        buildPager(meta);
    }

    // ── Bind status select ───────────────────────────────────────────────────────
    function bindStatusListeners() {
        document.querySelectorAll('.js-status-select').forEach(sel => {
            sel.onchange = function () {
                const url  = this.dataset.url;
                const id   = Number(this.dataset.id);
                const name = (window.rowData.get(id)?.fullname) || 'pemagang';
                const from = this.dataset.current;
                const to   = this.value;

                if (to === from) {
                    if (pending.has(id)) {
                        pending.delete(id);
                        markSelect(this, false);
                        updatePendingBar();
                    }
                    return;
                }

                pending.set(id, {
                    id, name, from, to, url,
                    select: this,
                    badge: document.getElementById(`badge-${id}`)
                });
                markSelect(this, true);
                updatePendingBar();
            };
        });
    }

    // ── Bind aksi baris (detail / edit / hapus) ──────────────────────────────────
    function bindRowActions() {
        // Detail
        document.querySelectorAll('.js-detail').forEach(btn => {
            btn.addEventListener('click', () => {
                const id   = Number(btn.dataset.id);
                const data = window.rowData.get(id);
                if (!data) return;
                openDetailModal(data);
            });
        });

        // Edit
        document.querySelectorAll('.js-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id   = Number(btn.dataset.id);
                const data = window.rowData.get(id);
                if (!data) return;
                openEditModal(data);
            });
        });

        // Hapus
        document.querySelectorAll('.js-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const id   = Number(btn.dataset.id);
                const data = window.rowData.get(id);
                openConfirmDelete(id, data?.fullname || 'pemagang ini');
            });
        });
    }

    // ── Modal helpers ─────────────────────────────────────────────────────────────
    const appModal    = document.getElementById('appModal');
    const confirmModal = document.getElementById('confirmModal');

    function openModal(modalEl)  { modalEl.classList.remove('hidden'); }
    function closeModal(modalEl) { modalEl.classList.add('hidden'); }

    appModal.querySelectorAll('[data-modal-close]').forEach(el =>
        el.addEventListener('click', () => closeModal(appModal))
    );
    confirmModal.querySelectorAll('[data-confirm-close]').forEach(el =>
        el.addEventListener('click', () => closeModal(confirmModal))
    );

    // ── Modal: Detail ────────────────────────────────────────────────────────────
    function openDetailModal(it) {
        document.getElementById('appModalTitle').textContent = 'Detail Pemagang';
        const ini    = initials(it.fullname);
        const status = String(it.internship_status || 'waiting').toLowerCase();
        const m      = STATUS_META[status] || { label: status, cls: 'bg-gray-100 text-gray-700' };

        const section = (title) =>
            `<p class="col-span-2 mt-2 text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659] border-b border-[#DCE7E1] pb-1">${title}</p>`;

        document.getElementById('appModalBody').innerHTML = `
        <div class="space-y-4">

            {{-- Identity header --}}
            <div class="flex items-center gap-4 rounded-[10px] bg-[#F4F8F6] px-4 py-3">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#E8F5E9] text-lg font-bold text-[#1F5F3F]">
                    ${ini}
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-[15px] font-bold text-[#1B3A34] truncate">${fmtStr(it.fullname)}</h4>
                    <p class="text-[12px] text-[#4B5F5A]">${fmtStr(it.institution_name || '-')}</p>
                </div>
                <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${m.cls}">${m.label}</span>
            </div>

            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-[13px]">

                ${section('Data Pribadi')}
                ${detailRow('Nama Lengkap', it.fullname)}
                ${detailRow('Tahun Lahir', it.born_date)}
                ${detailRow('NIM / NIS', it.student_id)}
                ${detailRow('Jenis Kelamin', it.gender)}
                ${detailRow('Email', it.email)}
                ${detailRow('No. HP / WA', it.phone_number)}
                ${detailRow('Kota Tinggal', it.current_city)}

                ${section('Data Akademik')}
                ${detailRow('Asal Sekolah / Kampus', it.institution_name)}
                ${detailRow('Program Studi', it.study_program)}
                ${detailRow('Fakultas', it.faculty)}

                ${section('Informasi Magang')}
                ${detailRow('Jenis Magang', it.internship_type)}
                ${detailRow('Sistem Kerja', it.internship_arrangement)}
                ${detailRow('Minat Program', it.internship_interest)}
                ${detailRow('Alasan Magang', it.internship_reason)}
                ${detailRow('Status Saat Ini', it.current_status)}
                ${detailRow('Bisa Bahasa Inggris', it.english_book_ability)}
                ${detailRow('No. WA Pembimbing', it.supervisor_contact)}
                ${detailRow('Tgl Mulai', it.start_date)}
                ${detailRow('Tgl Selesai', it.end_date)}

                ${section('Keahlian & Alat')}
                ${detailRow('Software Desain', it.design_software)}
                ${detailRow('Software Video', it.video_software)}
                ${detailRow('Bahasa Pemrograman', it.programming_languages)}
                ${detailRow('Materi Digital Marketing', it.digital_marketing_type)}
                ${detailRow('Punya Laptop', it.laptop_equipment)}
                ${detailRow('Alat yang Dimiliki', it.owned_tools)}

                ${section('Informasi Tambahan')}
                ${detailRow('Kegiatan Lain', it.current_activities)}
                ${detailRow('Butuh Info Kost', it.boarding_info)}
                ${detailRow('Status Keluarga', it.family_status)}
                ${detailRow('No. WA Wali / Ortu', it.parent_wa_contact)}
                ${detailRow('Instagram', it.social_media_instagram)}
                ${detailRow('Info Magang Dari', it.internship_info_sources)}
                ${detailRow('Tgl Daftar', fmtDate(it.created_at))}
            </div>

            {{-- Berkas --}}
            ${(it.cv_ktp_portofolio_pdf || it.portofolio_visual) ? `
            <div class="border-t border-[#DCE7E1] pt-4">
                <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.08em] text-[#4B5F5A]">Berkas Unggahan</p>
                <div class="flex flex-wrap gap-2">
                    ${it.cv_ktp_portofolio_pdf ? `
                    <a href="${it.cv_ktp_portofolio_pdf}" target="_blank"
                        class="inline-flex items-center gap-2 rounded-[8px] border border-[#DCE7E1] px-3 py-2 text-[13px] font-semibold text-[#2D8659] hover:bg-[#F4F8F6]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"/></svg>
                        CV / KTP / Portofolio (PDF)
                    </a>` : ''}
                    ${it.portofolio_visual ? `
                    <a href="${it.portofolio_visual}" target="_blank"
                        class="inline-flex items-center gap-2 rounded-[8px] border border-[#DCE7E1] px-3 py-2 text-[13px] font-semibold text-[#2D8659] hover:bg-[#F4F8F6]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m9 9 3 3-3 3"/><path d="m15 15-3-3 3-3"/></svg>
                        Portofolio Visual
                    </a>` : ''}
                </div>
            </div>` : ''}
        </div>`;
        openModal(appModal);
    }

    function detailRow(label, val) {
        return `<div>
            <p class="text-[10.5px] font-semibold uppercase tracking-wide text-[#4B5F5A] mb-0.5">${label}</p>
            <p class="text-[13px] font-medium text-[#1B3A34] break-words">${fmtStr(val)}</p>
        </div>`;
    }

    // ── Modal: Edit ──────────────────────────────────────────────────────────────
    function openEditModal(it) {
        document.getElementById('appModalTitle').textContent = 'Edit Data Pemagang';

        const statusOpts = Object.entries(STATUS_META).map(([val, m]) =>
            `<option value="${val}" ${val === it.internship_status ? 'selected' : ''}>${m.label}</option>`
        ).join('');

        const genderOpts = [
            ['male',   'Laki-laki'],
            ['female', 'Perempuan'],
        ].map(([v, l]) => `<option value="${v}" ${it.gender === v ? 'selected' : ''}>${l}</option>`).join('');

        const typeOpts = [
            ['mandiri',        'Magang Mandiri'],
            ['campus',         'Magang Kampus / Reguler'],
            ['pkl',            'PKL'],
            ['kampus-merdeka', 'Kampus Merdeka'],
        ].map(([v, l]) => `<option value="${v}" ${it.internship_type === v ? 'selected' : ''}>${l}</option>`).join('');

        const arrangementOpts = [
            ['onsite', 'WFO (Work From Office)'],
            ['hybrid', 'Hybrid'],
            ['remote', 'WFH (Work From Home)'],
        ].map(([v, l]) => `<option value="${v}" ${it.internship_arrangement === v ? 'selected' : ''}>${l}</option>`).join('');

        const interestOpts = [
            'project-manager','administration','hr','uiux','programmer',
            'photographer','videographer','graphic-designer','social-media-specialist',
            'content-writer','content-planner','marketing-and-sales','public-relation',
            'digital-marketing','tiktok-creator','welding','customer-service',
        ].map(v => {
            const labels = {
                'project-manager':'Project Manager','administration':'Administrasi','hr':'HR',
                'uiux':'UI/UX','programmer':'Programmer (Front End/Backend)','photographer':'Photographer',
                'videographer':'Videographer','graphic-designer':'Desainer Grafis',
                'social-media-specialist':'Social Media Specialist','content-writer':'Content Writer',
                'content-planner':'Content Planner','marketing-and-sales':'Marketing dan Sales',
                'public-relation':'Marcomm / Public Relation','digital-marketing':'Digital Marketing',
                'tiktok-creator':'Tiktok Creator','welding':'Las','customer-service':'Customer Service',
            };
            return `<option value="${v}" ${it.internship_interest === v ? 'selected' : ''}>${labels[v] || v}</option>`;
        }).join('');

        const section = (title) =>
            `<div class="col-span-2 mt-1 border-b border-[#DCE7E1] pb-1">
                <p class="text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">${title}</p>
            </div>`;

        document.getElementById('appModalBody').innerHTML = `
        <form id="editForm" class="space-y-1">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                ${section('Data Pribadi')}
                ${editField('Nama Lengkap', 'fullname', it.fullname)}
                ${editField('Tahun Lahir', 'born_date', it.born_date)}
                ${editField('NIM / NIS', 'student_id', it.student_id)}
                <div>
                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Jenis Kelamin</label>
                    <select name="gender" class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
                        <option value="">-- Pilih --</option>${genderOpts}
                    </select>
                </div>
                ${editField('Email', 'email', it.email, 'email')}
                ${editField('No. HP / WA', 'phone_number', it.phone_number)}
                ${editField('Kota Tinggal', 'current_city', it.current_city)}

                ${section('Data Akademik')}
                ${editField('Asal Sekolah / Kampus', 'institution_name', it.institution_name)}
                ${editField('Program Studi', 'study_program', it.study_program)}
                ${editField('Fakultas', 'faculty', it.faculty)}

                ${section('Informasi Magang')}
                <div>
                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Jenis Magang</label>
                    <select name="internship_type" class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
                        <option value="">-- Pilih --</option>${typeOpts}
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Sistem Kerja</label>
                    <select name="internship_arrangement" class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
                        <option value="">-- Pilih --</option>${arrangementOpts}
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Minat Program Magang</label>
                    <select name="internship_interest" class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
                        <option value="">-- Pilih --</option>${interestOpts}
                    </select>
                </div>
                ${editField('Tgl Mulai', 'start_date', it.start_date)}
                ${editField('Tgl Selesai', 'end_date', it.end_date)}
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Alasan Magang</label>
                    <textarea name="internship_reason" rows="2"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] resize-none">${fmtStr(it.internship_reason) === '-' ? '' : fmtStr(it.internship_reason)}</textarea>
                </div>
                ${editField('No. WA Pembimbing', 'supervisor_contact', it.supervisor_contact)}

                ${section('Keahlian & Alat')}
                ${editField('Software Desain', 'design_software', it.design_software)}
                ${editField('Software Video', 'video_software', it.video_software)}
                ${editField('Bahasa Pemrograman', 'programming_languages', it.programming_languages)}

                ${section('Informasi Tambahan')}
                ${editField('No. WA Wali / Ortu', 'parent_wa_contact', it.parent_wa_contact)}
                ${editField('Instagram', 'social_media_instagram', it.social_media_instagram)}
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Kegiatan Lain Selain Magang</label>
                    <textarea name="current_activities" rows="2"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] resize-none">${fmtStr(it.current_activities) === '-' ? '' : fmtStr(it.current_activities)}</textarea>
                </div>

                ${section('Status Pendaftaran')}
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">Status Magang</label>
                    <select name="internship_status"
                        class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659]">
                        ${statusOpts}
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-[#DCE7E1] pt-4 mt-4">
                <button type="button" onclick="document.getElementById('appModal').classList.add('hidden')"
                    class="rounded-[9px] border border-[#DCE7E1] bg-white px-4 py-2 text-sm font-semibold text-[#1B3A34] hover:bg-[#F4F8F6]">
                    Batal
                </button>
                <button type="submit"
                    class="rounded-[9px] bg-[#2D8659] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1F5F3F]">
                    Simpan Perubahan
                </button>
            </div>
        </form>`;

        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd  = new FormData(e.target);
            const url = `${ADMIN_INTERNS_BASE}/${it.id}`;
            fd.append('_method', 'PATCH');
            try {
                const res = await fetch(url, {
                    method: 'POST', body: fd,
                    headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                closeModal(appModal);
                pushToast('Data berhasil disimpan.');
                loadPage(window.__CURRENT_PAGE || 1);
            } catch (err) {
                pushToast('Gagal menyimpan: ' + err.message, 'error');
            }
        });

        openModal(appModal);
    }

    function editField(label, name, val, type = 'text') {
        const v = (fmtStr(val) === '-') ? '' : fmtStr(val);
        return `<div>
            <label class="mb-1 block text-[12px] font-semibold text-[#1B3A34]">${label}</label>
            <input type="${type}" name="${name}" value="${v}"
                class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
        </div>`;
    }

    // ── Modal: Konfirmasi Hapus ──────────────────────────────────────────────────
    let deleteId = null;

    function openConfirmDelete(id, name) {
        deleteId = id;
        document.getElementById('confirmBody').innerHTML =
            `Data <strong>${name}</strong> akan dihapus permanen dan tidak dapat dikembalikan.`;
        openModal(confirmModal);
    }

    document.getElementById('confirmYes').addEventListener('click', async () => {
        if (!deleteId) return;
        const url = `${ADMIN_INTERNS_BASE}/${deleteId}`;
        try {
            const fd = new FormData();
            fd.append('_method', 'DELETE');
            const res = await fetch(url, {
                method: 'POST', body: fd,
                headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            closeModal(confirmModal);
            pushToast('Data pemagang berhasil dihapus.');
            loadPage(window.__CURRENT_PAGE || 1);
        } catch (err) {
            pushToast('Gagal menghapus: ' + err.message, 'error');
        } finally {
            deleteId = null;
        }
    });

    // ── Pagination ───────────────────────────────────────────────────────────────
    function buildPager(meta) {
        const { current_page, last_page, total, per_page } = meta;
        const from = (current_page - 1) * per_page + 1;
        const to   = Math.min(current_page * per_page, total);
        const prev = current_page > 1 ? current_page - 1 : null;
        const next = current_page < last_page ? current_page + 1 : null;

        pagerEl.innerHTML = `
        <div class="flex items-center justify-between text-[12.5px] text-[#4B5F5A]">
            <span>Menampilkan <strong class="text-[#1B3A34]">${from}–${to}</strong> dari <strong class="text-[#1B3A34]">${total}</strong> pemagang</span>
            <div class="flex gap-1.5">
                <button ${!prev ? 'disabled' : ''} data-goto="${prev || ''}"
                    class="rounded-[7px] border border-[#DCE7E1] bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#2D8659] disabled:cursor-not-allowed disabled:opacity-40">
                    ← Sebelumnya
                </button>
                ${Array.from({ length: Math.min(5, last_page) }, (_, i) => {
                    let p;
                    if (last_page <= 5) {
                        p = i + 1;
                    } else if (current_page <= 3) {
                        p = i + 1;
                    } else if (current_page >= last_page - 2) {
                        p = last_page - 4 + i;
                    } else {
                        p = current_page - 2 + i;
                    }
                    return `<button data-goto="${p}"
                        class="h-8 w-8 rounded-[7px] border text-[12.5px] font-semibold transition
                            ${p === current_page
                                ? 'border-[#2D8659] bg-[#2D8659] text-white'
                                : 'border-[#DCE7E1] bg-white text-[#4B5F5A] hover:border-[#2D8659] hover:text-[#2D8659]'}">
                        ${p}
                    </button>`;
                }).join('')}
                <button ${!next ? 'disabled' : ''} data-goto="${next || ''}"
                    class="rounded-[7px] border border-[#DCE7E1] bg-white px-3 py-1.5 text-[12.5px] font-semibold text-[#4B5F5A] transition hover:border-[#2D8659] hover:text-[#2D8659] disabled:cursor-not-allowed disabled:opacity-40">
                    Berikutnya →
                </button>
            </div>
        </div>`;

        pagerEl.querySelectorAll('button[data-goto]').forEach(btn => {
            btn.addEventListener('click', () => {
                const p = Number(btn.dataset.goto);
                if (p) loadPage(p);
            });
        });
    }

    // ── Pending: Batalkan ────────────────────────────────────────────────────────
    discardBtn?.addEventListener('click', () => {
        if (pending.size === 0) return;
        for (const { select, from } of pending.values()) {
            select.value = from;
            markSelect(select, false);
        }
        pending.clear();
        updatePendingBar();
        pushToast('Semua perubahan dibatalkan.');
    });

    // ── Pending: Simpan semua ────────────────────────────────────────────────────
    async function runWithConcurrency(tasks, limit = 4) {
        const results = [];
        let i = 0;
        const workers = Array.from({ length: Math.min(limit, tasks.length) }, async () => {
            while (i < tasks.length) {
                const cur = i++;
                try { results[cur] = await tasks[cur](); }
                catch (e) { results[cur] = e; }
            }
        });
        await Promise.all(workers);
        return results;
    }

    saveAllBtn?.addEventListener('click', async () => {
        if (pending.size === 0) return;
        const items = Array.from(pending.values());
        saveAllBtn.disabled = true;
        discardBtn.disabled = true;

        const tasks = items.map(item => async () => {
            const fd = new FormData();
            fd.append('_method', 'PATCH');
            fd.append('internship_status', item.to);
            const res = await fetch(item.url, {
                method: 'POST', body: fd,
                headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return item;
        });

        try {
            const results = await runWithConcurrency(tasks, 4);
            let ok = 0, fail = 0;

            results.forEach((res) => {
                if (res instanceof Error) { fail++; return; }
                ok++;
                res.select.dataset.current = res.to;
                markSelect(res.select, false);
                applyBadge(res.badge, res.to);
                pending.delete(res.id);
            });

            updatePendingBar();
            if (ok)   pushToast(`${ok} perubahan berhasil disimpan.`);
            if (fail) pushToast(`${fail} perubahan gagal disimpan. Coba lagi.`, 'error');
        } catch (e) {
            pushToast('Gagal menyimpan perubahan.', 'error');
        } finally {
            saveAllBtn.disabled = pending.size === 0;
            discardBtn.disabled = false;
        }
    });

    // ── Load page (fetch API) ────────────────────────────────────────────────────
    async function loadPage(page = 1) {
        const searchQuery = (searchInput?.value || '').trim();
        const params = new URLSearchParams({
            scope:    activeFilter,
            page:     String(page),
            per_page: searchQuery ? '1000' : '25',
            search:   searchQuery,
        });

        rowsEl.innerHTML = `
            <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-[#4B5F5A]">
                Memuat data pemagang...
            </td></tr>`;

        try {
            const res = await fetch(`${API_URL}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const json = await res.json();
            renderRows(json);
            window.__CURRENT_PAGE = page;
            if (searchQuery) pagerEl.innerHTML = '';
        } catch (e) {
            console.error('Error loading interns:', e);
            rowsEl.innerHTML = `
                <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-[#D32F2F]">
                    Gagal memuat data. Silakan muat ulang halaman.
                </td></tr>`;
        }
    }

    // ── Export / global reload ───────────────────────────────────────────────────
    window.__CURRENT_PAGE  = 1;
    window.reloadInterns   = (p) => loadPage(p || window.__CURRENT_PAGE || 1);

    // ── Init ─────────────────────────────────────────────────────────────────────
    loadPage(Number(new URLSearchParams(location.search).get('page') || 1));

    window.addEventListener('beforeunload', (e) => {
        if (pending.size > 0) { e.preventDefault(); e.returnValue = ''; }
    });
});
</script>

@endsection
