{{--
    Partial: Tabel Aspek Penilaian (untuk form single)
    Variables:
        $formId  — 'single'
        $aspects — array of ['aspek' => ..., 'nilai' => ...]
--}}
<div class="rounded-[12px] border border-[#DCE7E1] bg-white p-5 shadow-sm">
    <p class="mb-4 text-[11px] font-bold uppercase tracking-[0.08em] text-[#2D8659]">Aspek Penilaian</p>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white w-12 text-center">No</th>
                    <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white">Aspek Penilaian</th>
                    <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white w-28 text-center">Nilai</th>
                    <th class="bg-[#1B3A34] px-4 py-3 text-[11px] font-bold uppercase tracking-[0.06em] text-white w-12 text-center">Hapus</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#DCE7E1]" id="{{ $formId }}AspekTbody">
                @foreach($aspects as $i => $item)
                <tr class="hover:bg-[#F4F8F6]">
                    <td class="px-4 py-3 text-center text-[13px] font-semibold text-[#4B5F5A]">{{ $i + 1 }}</td>
                    <td class="px-3 py-2">
                        <input type="text" name="aspek[]" value="{{ $item['aspek'] }}"
                            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" name="nilai[]" value="{{ $item['nilai'] }}" min="0" max="100"
                            oninput="updateSingleAvg()"
                            class="w-full rounded-[8px] border border-[#DCE7E1] bg-[#F4F8F6] px-3 py-2 text-center text-[13px] text-[#1B3A34] outline-none focus:border-[#2D8659] transition">
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" onclick="deleteSingleRow(this)"
                            class="flex h-8 w-8 items-center justify-center rounded-[8px] border border-red-200 bg-red-50 text-[#D32F2F] transition hover:bg-red-100 mx-auto">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-[#DCE7E1] bg-[#F4F8F6]">
                    <td colspan="2" class="px-4 py-3 text-right text-[13px] font-bold text-[#1B3A34]">Rata-rata</td>
                    <td class="px-4 py-3 text-center text-[15px] font-bold text-[#2D8659]" id="{{ $formId }}Avg">0</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-4 flex items-center justify-between border-t border-[#DCE7E1] pt-4">
        <button type="button" onclick="addSingleRow()"
            class="flex items-center gap-2 rounded-[9px] border border-[#DCE7E1] bg-white px-3 py-2 text-[13px] font-semibold text-[#1B3A34] transition hover:border-[#2D8659] hover:text-[#1F5F3F]">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Aspek
        </button>
        <p class="text-[11px] text-[#4B5F5A]">81–100: Amat Baik &nbsp;|&nbsp; 65–80: Baik &nbsp;|&nbsp; 50–64: Cukup &nbsp;|&nbsp; &lt;50: Kurang</p>
    </div>
</div>
