<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminModuleController extends Controller
{
    /**
     * Tampilkan semua modul pembelajaran.
     */
    public function index(): View
    {
        $modules = Module::orderBy('module_number', 'asc')->get();
        return view('admin.modules.index', compact('modules'));
    }

    /**
     * Tampilkan form pembuatan modul baru.
     */
    public function create(): View
    {
        // Berikan saran nomor modul berikutnya secara otomatis
        $nextNumber = (Module::max('module_number') ?? 0) + 1;
        return view('admin.modules.create', compact('nextNumber'));
    }

    /**
     * Simpan modul baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'module_number' => 'required|integer|min:1|unique:modules,module_number',
            'icon' => 'nullable|string|max:100',
        ], [
            'title.required' => 'Judul modul wajib diisi.',
            'description.required' => 'Deskripsi modul wajib diisi.',
            'module_number.required' => 'Nomor urut modul wajib diisi.',
            'module_number.unique' => 'Nomor modul ini sudah digunakan oleh modul lain.',
            'module_number.integer' => 'Nomor modul harus berupa angka bulat.',
        ]);

        $slug = Str::slug($validated['title']);
        $existingSlugCount = Module::where('slug', 'LIKE', "{$slug}%")->count();
        if ($existingSlugCount > 0) {
            $slug .= '-' . ($existingSlugCount + 1);
        }

        Module::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'module_number' => $validated['module_number'],
            'icon' => $validated['icon'] ?? 'zap',
        ]);

        return redirect()->route('admin.modules.index')->with('success', 'Modul pembelajaran baru berhasil ditambahkan!');
    }

    /**
     * Tampilkan form edit modul.
     */
    public function edit(int $id): View
    {
        $module = Module::findOrFail($id);
        return view('admin.modules.edit', compact('module'));
    }

    /**
     * Perbarui data modul yang sudah ada.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $module = Module::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'module_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('modules', 'module_number')->ignore($module->id),
            ],
            'icon' => 'nullable|string|max:100',
        ], [
            'title.required' => 'Judul modul wajib diisi.',
            'description.required' => 'Deskripsi modul wajib diisi.',
            'module_number.required' => 'Nomor urut modul wajib diisi.',
            'module_number.unique' => 'Nomor modul ini sudah digunakan oleh modul lain.',
        ]);

        $slug = Str::slug($validated['title']);
        $existingSlug = Module::where('slug', $slug)->where('id', '!=', $module->id)->first();
        if ($existingSlug) {
            $slug .= '-' . $module->id;
        }

        $module->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'module_number' => $validated['module_number'],
            'icon' => $validated['icon'] ?? $module->icon,
        ]);

        return redirect()->route('admin.modules.index')->with('success', "Modul \"{$module->title}\" berhasil diperbarui!");
    }

    /**
     * Hapus modul dari database.
     */
    public function destroy(int $id): RedirectResponse
    {
        $module = Module::findOrFail($id);
        $title = $module->title;
        $module->delete();

        return redirect()->route('admin.modules.index')->with('success', "Modul \"{$title}\" berhasil dihapus.");
    }
}
