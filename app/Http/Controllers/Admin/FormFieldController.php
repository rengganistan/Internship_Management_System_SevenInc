<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormFieldController extends Controller
{
    /**
     * GET /admin/form-settings/fields
     * Halaman utama pengaturan form dengan live preview.
     */
    public function index()
    {
        // Kelompokkan field berdasarkan group_name, ordered by sort_order
        $mainFields  = FormField::orderBy('sort_order')->whereNull('group_name')->get();
        $extraFields = FormField::orderBy('sort_order')->where('group_name', 'informasi_tambahan')->get();

        $types  = FormField::supportedTypes();
        $groups = FormField::groups();

        return view('admin.form_settings.form_fields', compact(
            'mainFields', 'extraFields', 'types', 'groups'
        ));
    }

    /**
     * POST /admin/form-settings/fields
     * Tambah field baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'field_type'  => 'required|in:' . implode(',', array_keys(FormField::supportedTypes())),
            'label'       => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'helper_text' => 'nullable|string|max:500',
            'is_required' => 'nullable|boolean',
            'group_name'  => 'nullable|in:,informasi_tambahan',
            'column_span' => 'nullable|integer|in:1,2',
            'options'     => 'nullable|string', // JSON string dari textarea
        ]);

        // Buat field_key unik dari label
        $baseKey = 'custom_' . Str::slug($validated['label'], '_');
        $key     = $baseKey;
        $i       = 1;
        while (FormField::where('field_key', $key)->exists()) {
            $key = $baseKey . '_' . $i++;
        }

        // Parse options dari JSON text
        $options = null;
        if (!empty($validated['options'])) {
            $decoded = json_decode($validated['options'], true);
            if (is_array($decoded)) {
                $options = $decoded;
            }
        }

        // Tentukan sort_order (setelah field terakhir di grup yang sama)
        $maxOrder = FormField::where('group_name', $validated['group_name'] ?: null)
            ->max('sort_order') ?? 0;

        FormField::create([
            'field_key'   => $key,
            'field_type'  => $validated['field_type'],
            'label'       => trim($validated['label']),
            'placeholder' => $validated['placeholder'] ?? null,
            'helper_text' => $validated['helper_text'] ?? null,
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'is_active'   => true,
            'is_system'   => false,
            'group_name'  => $validated['group_name'] ?: null,
            'sort_order'  => $maxOrder + 10,
            'column_span' => (int) ($validated['column_span'] ?? 1),
            'options'     => $options,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'message' => 'Field berhasil ditambahkan.']);
        }

        return redirect()->route('admin.form-settings.fields')
            ->with('success', "Field \"{$validated['label']}\" berhasil ditambahkan.");
    }

    /**
     * PUT /admin/form-settings/fields/{field}
     * Update field yang sudah ada.
     */
    public function update(Request $request, FormField $field)
    {
        $validated = $request->validate([
            'label'       => 'required|string|max:255',
            'placeholder' => 'nullable|string|max:255',
            'helper_text' => 'nullable|string|max:500',
            'is_required' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'column_span' => 'nullable|integer|in:1,2',
            'options'     => 'nullable|string', // JSON string
            'field_type'  => 'nullable|in:' . implode(',', array_keys(FormField::supportedTypes())),
            'group_name'  => 'nullable|in:,informasi_tambahan',
        ]);

        $updateData = [
            'label'       => trim($validated['label']),
            'placeholder' => $validated['placeholder'] ?? $field->placeholder,
            'helper_text' => $validated['helper_text'] ?? $field->helper_text,
            'is_required' => (bool) ($validated['is_required'] ?? $field->is_required),
            'is_active'   => isset($validated['is_active'])
                                ? (bool) $validated['is_active']
                                : $field->is_active,
            'column_span' => (int) ($validated['column_span'] ?? $field->column_span),
        ];

        // Field system: hanya boleh edit label, placeholder, helper_text, is_active, column_span
        if (!$field->is_system) {
            // Non-system: boleh ubah field_type, group_name, options
            if (!empty($validated['field_type'])) {
                $updateData['field_type'] = $validated['field_type'];
            }
            if (array_key_exists('group_name', $validated)) {
                $updateData['group_name'] = $validated['group_name'] ?: null;
            }
        }

        // Parse & update options (untuk semua field yang punya type yg butuh options)
        if (isset($validated['options']) && $validated['options'] !== null) {
            $decoded = json_decode($validated['options'], true);
            if (is_array($decoded)) {
                $updateData['options'] = $decoded;
            }
        }

        $field->update($updateData);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'      => true,
                'message' => 'Field berhasil diperbarui.',
                'field'   => $field->fresh(),
            ]);
        }

        return redirect()->route('admin.form-settings.fields')
            ->with('success', "Field \"{$field->label}\" berhasil diperbarui.");
    }

    /**
     * POST /admin/form-settings/fields/{field}/toggle
     * Aktifkan / nonaktifkan field (is_active).
     */
    public function toggle(FormField $field)
    {
        $field->update(['is_active' => !$field->is_active]);
        $status = $field->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'ok'        => true,
                'is_active' => $field->is_active,
                'message'   => "Field \"{$field->label}\" berhasil {$status}.",
            ]);
        }

        return redirect()->route('admin.form-settings.fields')
            ->with('success', "Field \"{$field->label}\" berhasil {$status}.");
    }

    /**
     * POST /admin/form-settings/fields/{field}/toggle-required
     * Toggle wajib diisi (is_required).
     */
    public function toggleRequired(FormField $field)
    {
        $field->update(['is_required' => !$field->is_required]);
        $status = $field->is_required ? 'dijadikan wajib' : 'dijadikan opsional';

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'ok'          => true,
                'is_required' => $field->is_required,
                'message'     => "Field \"{$field->label}\" berhasil {$status}.",
            ]);
        }

        return redirect()->route('admin.form-settings.fields')
            ->with('success', "Field \"{$field->label}\" berhasil {$status}.");
    }

    /**
     * POST /admin/form-settings/fields/reorder
     * Reorder via AJAX drag & drop.
     * Body: { ids: [1, 5, 3, ...], group: 'null' | 'informasi_tambahan' }
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:form_fields,id',
        ]);

        foreach ($validated['ids'] as $order => $id) {
            FormField::where('id', $id)->update(['sort_order' => ($order + 1) * 10]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * DELETE /admin/form-settings/fields/{field}
     * Hapus field (hanya non-system).
     */
    public function destroy(FormField $field)
    {
        if ($field->is_system) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Field inti sistem tidak dapat dihapus.',
                ], 422);
            }

            return redirect()->route('admin.form-settings.fields')
                ->with('error', 'Field inti sistem tidak dapat dihapus.');
        }

        $label = $field->label;
        $field->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['ok' => true, 'message' => "Field \"{$label}\" berhasil dihapus."]);
        }

        return redirect()->route('admin.form-settings.fields')
            ->with('success', "Field \"{$label}\" berhasil dihapus.");
    }

    /**
     * GET /admin/form-settings/fields/{field}/data
     * Mengembalikan satu field sebagai JSON (untuk modal edit).
     */
    public function show(FormField $field)
    {
        return response()->json(['field' => $field]);
    }

    /**
     * GET /admin/form-settings/fields/preview-data
     * Mengembalikan data field terbaru untuk live preview (AJAX).
     */
    public function previewData()
    {
        $mainFields  = FormField::active()->whereNull('group_name')->get();
        $extraFields = FormField::active()->where('group_name', 'informasi_tambahan')->get();
        $divisions   = \App\Models\Division::active()->pluck('name');

        return response()->json([
            'main_fields'  => $mainFields,
            'extra_fields' => $extraFields,
            'divisions'    => $divisions,
        ]);
    }
}
