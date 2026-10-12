<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $items = Mapel::orderByRaw("FIELD(kelompok,'Umum','Kejuruan','Muatan Lokal')")->orderBy('urutan')->get();

        return view('admin.mapel.index', compact('items'));
    }

    public function create()
    {
        return view('admin.mapel.form', ['item' => new Mapel(['kkm' => 75, 'urutan' => 1])]);
    }

    public function store(Request $request)
    {
        Mapel::create($this->validated($request));

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran ditambahkan.');
    }

    public function edit(Mapel $mapel)
    {
        return view('admin.mapel.form', ['item' => $mapel]);
    }

    public function update(Request $request, Mapel $mapel)
    {
        $mapel->update($this->validated($request, $mapel->id));

        return redirect()->route('admin.mapel.index')->with('success', 'Mata pelajaran diperbarui.');
    }

    public function destroy(Mapel $mapel)
    {
        $mapel->delete();

        return back()->with('success', 'Mata pelajaran dihapus.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'kode' => 'required|string|max:15|unique:mapels,kode,'.$id,
            'nama' => 'required|string|max:255',
            'kelompok' => 'required|in:'.implode(',', Mapel::KELOMPOK),
            'kkm' => 'required|integer|min:0|max:100',
            'urutan' => 'required|integer|min:1',
        ]);
    }
}
