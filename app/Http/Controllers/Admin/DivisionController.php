<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DivisionController extends Controller
{
    /** GET /admin/form-settings/divisions */
    public function index()
    {
        $divisions = Division::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.form_settings.divisions', compact('divisions'));
    }

    /** POST /admin/form-settings/divisions */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:divisions,name',
        ]);

        $maxOrder = Division::max('sort_order') ?? 0;

        Division::create([
            'name'       => trim($validated['name']),
            'slug'       => Str::slug(trim($validated['name']), '_'),
            'is_active'  => true,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('admin.form-settings.divisions')
            ->with('success', "Divisi \"{$validated['name']}\" berhasil ditambahkan.");
    }

    /** PUT /admin/form-settings/divisions/{division} */
    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name' => "required|string|max:100|unique:divisions,name,{$division->id}",
        ]);

        $division->update([
            'name' => trim($validated['name']),
            'slug' => Str::slug(trim($validated['name']), '_'),
        ]);

        return redirect()->route('admin.form-settings.divisions')
            ->with('success', "Divisi berhasil diperbarui.");
    }

    /** POST /admin/form-settings/divisions/{division}/toggle */
    public function toggle(Division $division)
    {
        $division->update(['is_active' => !$division->is_active]);

        $status = $division->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'ok'        => true,
                'is_active' => $division->is_active,
                'message'   => "Divisi \"{$division->name}\" berhasil {$status}.",
            ]);
        }

        return redirect()->route('admin.form-settings.divisions')
            ->with('success', "Divisi \"{$division->name}\" berhasil {$status}.");
    }

    /** POST /admin/form-settings/divisions/reorder — AJAX drag & drop */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:divisions,id',
        ]);

        foreach ($validated['ids'] as $order => $id) {
            Division::where('id', $id)->update(['sort_order' => $order + 1]);
        }

        return response()->json(['ok' => true]);
    }

    /** DELETE /admin/form-settings/divisions/{division} */
    public function destroy(Division $division)
    {
        $name = $division->name;
        $division->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['ok' => true, 'message' => "Divisi \"{$name}\" dihapus."]);
        }

        return redirect()->route('admin.form-settings.divisions')
            ->with('success', "Divisi \"{$name}\" berhasil dihapus.");
    }
}
