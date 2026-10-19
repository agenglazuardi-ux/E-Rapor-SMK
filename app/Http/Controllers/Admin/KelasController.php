<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $items = Kelas::with('jurusan', 'waliKelas')->withCount('siswas')->orderBy('tingkat')->orderBy('nama')->get();

        return view('admin.kelas.index', compact('items'));
    }

    public function create()
    {
        return view('admin.kelas.form', $this->formData(new Kelas));
    }

    public function store(Request $request)
    {
        Kelas::create($this->validated($request));

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas ditambahkan.');
    }

    public function edit(Kelas $kelas)
    {
        return view('admin.kelas.form', $this->formData($kelas));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $kelas->update($this->validated($request, $kelas->id));
        \App\Models\RiwayatKelas::sinkron($kelas->siswas()->get());

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        if ($kelas->siswas()->exists()) {
            return back()->with('error', "Kelas {$kelas->nama} masih memiliki siswa. Pindahkan siswa (Kenaikan Kelas / Edit Siswa) terlebih dahulu.");
        }
        $kelas->delete();

        return back()->with('success', 'Kelas dihapus.');
    }

    private function formData(Kelas $item): array
    {
        return ['item' => $item, 'jurusans' => Jurusan::orderBy('kode')->get(), 'gurus' => Guru::orderBy('nama')->get()];
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nama' => 'required|string|max:50|unique:kelas,nama,'.$id,
            'tingkat' => 'required|in:X,XI,XII',
            'jurusan_id' => 'required|exists:jurusans,id',
            'wali_kelas_id' => 'nullable|exists:gurus,id',
        ]);
    }
}