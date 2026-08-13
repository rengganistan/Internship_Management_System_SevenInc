<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormCustomField;
use App\Models\FormSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormSettingController extends Controller
{
    // Field system yang TIDAK boleh dinonaktifkan
    private const CORE_FIELDS = [
        'fullname', 'student_id', 'born_date', 'gender', 'email',
        'phone_number', 'institution_name', 'study_program',
        'internship_interest', 'internship_type', 'internship_reason',
    ];

    // Field yang dihapus dari sistem (tidak boleh diaktifkan lagi)
    private const REMOVED_FIELDS = ['family_status'];

    /** GET /admin/form-settings */
    public function index()
    {
        $setting  = FormSetting::where('form_key', 'internship_registration')->first();
        $saved    = $setting?->fields ?? [];

        $defaults = FormSetting::defaultInternshipFields();
        $savedMap = [];
        foreach ($saved as $f) {
            $savedMap[$f['key']] = $f;
        }

        $fields = [];
        foreach ($defaults as $def) {
            // Skip field yang sudah dihapus dari sistem
            if (in_array($def['key'], self::REMOVED_FIELDS)) continue;

            if (isset($savedMap[$def['key']])) {
                $def['is_active']   = (bool) $savedMap[$def['key']]['is_active'];
                $def['is_required'] = (bool) $savedMap[$def['key']]['is_required'];
                $def['sort_order']  = (int) ($savedMap[$def['key']]['sort_order'] ?? 999);
            } else {
                $def['sort_order'] = 999;
            }
            $def['is_core']   = in_array($def['key'], self::CORE_FIELDS);
            $def['is_custom'] = false;
            $fields[] = $def;
        }

        // Ambil custom fields
        $customFields = FormCustomField::forForm()->orderBy('sort_order')->withTrashed(false)->get();

        // Kelompokkan per section
        $sections = [];
        foreach ($fields as $f) {
            $sections[$f['section']][] = $f;
        }

        return view('admin.form_settings.index', compact(
            'sections', 'fields', 'customFields'
        ));
    }

    /** POST /admin/form-settings — simpan toggle aktif/wajib system fields */
    public function update(Request $request)
    {
        $defaults = FormSetting::defaultInternshipFields();

        $fields = [];
        foreach ($defaults as $def) {
            if (in_array($def['key'], self::REMOVED_FIELDS)) continue;

            $key    = $def['key'];
            $isCore = in_array($key, self::CORE_FIELDS);

            $fields[] = [
                'key'         => $key,
                'label'       => $def['label'],
                'section'     => $def['section'],
                'is_active'   => $isCore ? true  : (bool) $request->has("active_{$key}"),
                'is_required' => $isCore ? true  : (bool) $request->has("required_{$key}"),
                'sort_order'  => (int) $request->input("sort_{$key}", 999),
                'is_lockable' => $isCore,
            ];
        }

        FormSetting::updateOrCreate(
            ['form_key' => 'internship_registration'],
            ['fields'   => $fields]
        );

        FormSetting::clearCache();

        return redirect()->route('admin.form-settings.index')
            ->with('success', 'Pengaturan form pendaftaran berhasil disimpan.');
    }

    /** POST /admin/form-settings/reset */
    public function reset()
    {
        FormSetting::where('form_key', 'internship_registration')->delete();
        FormSetting::clearCache();

        return redirect()->route('admin.form-settings.index')
            ->with('success', 'Pengaturan form berhasil direset ke default.');
    }

    // ─── CUSTOM FIELDS ──────────────────────────────────────────────────────

    /** POST /admin/form-settings/custom — simpan field baru */
    public function storeCustom(Request $request)
    {
        $validated = $request->validate([
            'label'       => 'required|string|max:150',
            'field_key'   => [
                'required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('form_custom_fields', 'field_key')->whereNull('deleted_at'),
            ],
            'type'        => 'required|string|in:' . implode(',', array_keys(FormCustomField::$supportedTypes)),
            'placeholder' => 'nullable|string|max:255',
            'section'     => 'nullable|string|max:100',
            'is_required' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'options'     => 'nullable|array',
            'options.*'   => 'nullable|string|max:100',
        ], [
            'field_key.regex'  => 'Key harus diawali huruf kecil, hanya boleh huruf kecil, angka, dan underscore (contoh: domicile, phone_number).',
            'field_key.unique' => 'Key tersebut sudah digunakan. Gunakan key yang berbeda.',
        ]);

        // Pastikan field_key tidak bentrok dengan system fields
        $systemKeys = array_column(FormSetting::defaultInternshipFields(), 'key');
        if (in_array($validated['field_key'], $systemKeys)) {
            return back()->withErrors(['field_key' => 'Key tersebut sudah digunakan oleh field sistem.'])->withInput();
        }

        // Proses options untuk select/radio/checkbox
        $options = null;
        if (FormCustomField::needsOptions($validated['type']) && !empty($validated['options'])) {
            $options = array_values(array_filter(
                array_map(fn($o) => trim((string)$o), $validated['options']),
                fn($o) => $o !== ''
            ));
        }

        // Sort order = setelah field custom terakhir
        $maxOrder = FormCustomField::forForm()->max('sort_order') ?? 900;

        FormCustomField::create([
            'form_key'    => 'internship_registration',
            'label'       => $validated['label'],
            'field_key'   => $validated['field_key'],
            'type'        => $validated['type'],
            'placeholder' => $validated['placeholder'] ?? null,
            'section'     => $validated['section'] ?: 'Field Tambahan',
            'options'     => $options,
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'is_active'   => (bool) ($validated['is_active'] ?? true),
            'sort_order'  => $maxOrder + 1,
        ]);

        FormSetting::clearCache();

        return redirect()->route('admin.form-settings.index')
            ->with('success', "Field \"{$validated['label']}\" berhasil ditambahkan.");
    }

    /** PUT /admin/form-settings/custom/{field} — update field */
    public function updateCustom(Request $request, FormCustomField $customField)
    {
        $validated = $request->validate([
            'label'       => 'required|string|max:150',
            'type'        => 'required|string|in:' . implode(',', array_keys(FormCustomField::$supportedTypes)),
            'placeholder' => 'nullable|string|max:255',
            'section'     => 'nullable|string|max:100',
            'is_required' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'options'     => 'nullable|array',
            'options.*'   => 'nullable|string|max:100',
        ]);

        $options = $customField->options;
        if (FormCustomField::needsOptions($validated['type']) && isset($validated['options'])) {
            $options = array_values(array_filter(
                array_map(fn($o) => trim((string)$o), $validated['options']),
                fn($o) => $o !== ''
            ));
        } elseif (!FormCustomField::needsOptions($validated['type'])) {
            $options = null;
        }

        $customField->update([
            'label'       => $validated['label'],
            'type'        => $validated['type'],
            'placeholder' => $validated['placeholder'] ?? null,
            'section'     => $validated['section'] ?: $customField->section,
            'options'     => $options,
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'is_active'   => (bool) ($validated['is_active'] ?? true),
        ]);

        FormSetting::clearCache();

        return redirect()->route('admin.form-settings.index')
            ->with('success', "Field \"{$customField->label}\" berhasil diperbarui.");
    }

    /** DELETE /admin/form-settings/custom/{field} — soft delete */
    public function destroyCustom(FormCustomField $customField)
    {
        $label = $customField->label;
        $customField->delete(); // soft delete
        FormSetting::clearCache();

        return redirect()->route('admin.form-settings.index')
            ->with('success', "Field \"{$label}\" berhasil dihapus.");
    }

    /** POST /admin/form-settings/custom/reorder — update sort_order via AJAX */
    public function reorderCustom(Request $request)
    {
        $validated = $request->validate([
            'orders'    => 'required|array',
            'orders.*.id'    => 'required|integer|exists:form_custom_fields,id',
            'orders.*.order' => 'required|integer|min:1',
        ]);

        foreach ($validated['orders'] as $item) {
            FormCustomField::where('id', $item['id'])->update(['sort_order' => $item['order']]);
        }

        FormSetting::clearCache();
        return response()->json(['ok' => true]);
    }

    /** POST /admin/form-settings/custom/{field}/toggle — toggle aktif */
    public function toggleCustom(FormCustomField $customField)
    {
        $customField->update(['is_active' => !$customField->is_active]);
        FormSetting::clearCache();

        $status = $customField->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.form-settings.index')
            ->with('success', "Field \"{$customField->label}\" berhasil {$status}.");
    }
}
