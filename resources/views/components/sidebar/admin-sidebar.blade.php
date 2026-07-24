@php
    $icon = [
        'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'file' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h8"/></svg>',
        'clock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
        'check' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>',
        'x' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6m0-6-6 6"/></svg>',
        'award' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="5"/><path d="m8.5 12.5-1 8 4.5-2.5 4.5 2.5-1-8"/></svg>',
        'settings' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1 1.55V20.3h-3v-.09a1.7 1.7 0 0 0-1-1.55 1.7 1.7 0 0 0-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 0 0 7.08 15a1.7 1.7 0 0 0-1.55-1H5.4v-3h.13A1.7 1.7 0 0 0 7.08 10a1.7 1.7 0 0 0-.34-1.88l-.06-.06L8.8 5.94l.06.06A1.7 1.7 0 0 0 10.74 6.34a1.7 1.7 0 0 0 1-1.55V4.7h3v.09a1.7 1.7 0 0 0 1 1.55A1.7 1.7 0 0 0 17.62 6l.06-.06 2.12 2.12-.06.06A1.7 1.7 0 0 0 19.4 10a1.7 1.7 0 0 0 1.55 1h.13v3h-.13A1.7 1.7 0 0 0 19.4 15Z"/></svg>',
        'message' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 9.6 9.6 0 0 1-4-.9L3 21l1.8-4.4A8.2 8.2 0 0 1 3 11.5 8.4 8.4 0 0 1 12 3a8.4 8.4 0 0 1 9 8.5Z"/></svg>',
        'logout' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-6"/></svg>',
    ];

    $groups = [
        [
            'label' => 'Dashboard & Monitoring',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard.index', 'icon' => 'dashboard'],
                ['label' => 'Semua Pengguna', 'route' => 'admin.users.index', 'icon' => 'users'],
            ],
        ],
        [
            'label' => 'Pendaftaran & Status',
            'items' => [
                ['label' => 'Data Pendaftar Magang', 'route' => 'admin.interns.index', 'icon' => 'file'],
            ],
        ],
        [
            'label' => 'Dokumen & Sertifikat',
            'items' => [
                ['label' => 'Sertifikat', 'route' => 'admin.certificate.index', 'icon' => 'award'],
                ['label' => 'Member Card', 'route' => 'admin.membercards.index', 'icon' => 'award'],
                ['label' => 'Surat Penilaian', 'route' => 'interns.assessment.index', 'icon' => 'file'],
                ['label' => 'Data SKL', 'route' => 'admin.documents.skls', 'icon' => 'file'],
                ['label' => 'Data LOA', 'route' => 'admin.documents.loas', 'icon' => 'file'],
            ],
        ],
        [
            'label' => 'Template & Pengaturan',
            'items' => [
                ['label' => 'Template SKL', 'route' => 'admin.skl.editor', 'icon' => 'settings'],
                ['label' => 'Template LOA', 'route' => 'admin.loa.editor', 'icon' => 'settings'],
            ],
        ],
        [
            'label' => 'Feedback',
            'items' => [
                ['label' => 'Feedback Pemagang', 'route' => 'admin.feedback.index', 'icon' => 'message'],
            ],
        ],
    ];
@endphp

<aside
    id="admin-sidebar"
    class="fixed inset-y-0 left-0 z-40 flex w-[272px] -translate-x-full flex-col border-r border-admin-border bg-white transition-all duration-300 lg:translate-x-0"
>
    <div class="flex h-16 items-center gap-3 border-b border-admin-border px-5">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-[9px] bg-gradient-to-br from-admin-primary to-admin-primary-dark text-sm font-extrabold text-white">
            LM
        </div>

        <div class="admin-sidebar-label min-w-0">
            <p class="truncate text-[15px] font-bold text-admin-text-dark">Listmagang</p>
            <p class="text-[11px] text-admin-text-mid">Panel Admin</p>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4">
        @foreach ($groups as $groupIndex => $group)
            @php
                $isGroupActive = collect($group['items'])
                    ->contains(fn ($item) =>
                        $item['route'] === 'admin.interns.index'
                            ? request()->routeIs('admin.interns.*')
                            : request()->routeIs($item['route'])
                    );
            @endphp

            <section class="admin-nav-group mb-2" data-nav-group>
                <button
                    type="button"
                    class="flex w-full items-center justify-between px-2.5 py-2 text-left"
                    data-nav-group-toggle
                    aria-expanded="{{ $isGroupActive ? 'true' : 'false' }}"
                >
                    <span class="admin-sidebar-label text-[11px] font-bold uppercase tracking-[0.06em] text-admin-text-mid">
                        {{ $group['label'] }}
                    </span>

                    <svg class="admin-sidebar-label h-4 w-4 text-admin-text-mid transition-transform" data-nav-chevron viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </button>

                <div class="{{ $isGroupActive ? '' : 'hidden' }} space-y-1" data-nav-group-items>
                    @foreach ($group['items'] as $item)
                        @php
                            // Untuk "Data Pendaftar Magang": aktif jika route manapun di admin.interns.*
                            $isActive = $item['route'] === 'admin.interns.index'
                                ? request()->routeIs('admin.interns.*')
                                : request()->routeIs($item['route']);
                        @endphp

                        <a
                            href="{{ route($item['route']) }}"
                            title="{{ $item['label'] }}"
                            class="{{ $isActive
                                ? 'bg-admin-primary text-white shadow-sm'
                                : 'text-admin-text-mid hover:bg-admin-secondary hover:text-admin-primary-dark' }}
                                flex items-center gap-3 rounded-lg px-3 py-2 text-[13.5px] font-medium transition"
                        >
                            <span class="h-[18px] w-[18px] shrink-0 [&>svg]:h-full [&>svg]:w-full [&>svg]:stroke-[2]">
                                {!! $icon[$item['icon']] !!}
                            </span>
                            <span class="admin-sidebar-label truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <div class="border-t border-admin-border p-3">
        <form action="{{ route('user.logout') }}" method="POST">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-[13.5px] font-semibold text-admin-error transition hover:bg-red-50"
            >
                <svg class="h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-6"/>
                </svg>
                <span class="admin-sidebar-label">Logout</span>
            </button>
        </form>
    </div>

    <button
        id="admin-sidebar-collapse"
        type="button"
        class="absolute -right-3 top-5 hidden h-6 w-6 items-center justify-center rounded-full border border-admin-border bg-white text-admin-text-mid shadow-sm lg:flex"
        aria-label="Ciutkan sidebar"
    >
        <svg class="h-3.5 w-3.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="m14 7-5 5 5 5"/>
        </svg>
    </button>
</aside>

<div id="admin-sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-admin-text-dark/40 lg:hidden"></div>