<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $items = TahunAjaran::orderByDesc('nama')->orderByDesc('semester')->get();

        return view('admin.tahun_ajaran.index', compact('items'));
    }

    public function create()
    {
        return view('admin.tahun_ajaran.form', ['item' => new TahunAjaran]);
    }

    public function store(Request $request)
    {
        TahunAjaran::create($this->validated($request));

        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Tahun ajaran ditambahkan.');
    }

    public function edit(TahunAjaran $tahunAjaran)
    {
        return view('admin.tahun_ajaran.form', ['item' => $tahunAjaran]);
    }

    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        $tahunAjaran->update($this->validated($request, $tahunAjaran->id));

        return redirect()->route('admin.tahun-ajaran.index')->with('success', 'Tahun ajaran diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        $tahunAjaran->delete();

        return back()->with('success', 'Tahun ajaran dihapus.');
    }

    public function aktifkan(TahunAjaran $tahunAjaran)
    {
        $siswas = Siswa::where('status', 'aktif')->whereNotNull('kelas_id')->with('kelas')->get();
        RiwayatKelas::bekukan($siswas);
        if ($lama = TahunAjaran::aktif()) {
            RiwayatKelas::sinkron($siswas, $lama, false);
        }
        TahunAjaran::query()->update(['aktif' => false]);
        $tahunAjaran->update(['aktif' => true]);

        return back()->with('success', "Tahun ajaran {$tahunAjaran->label} sekarang aktif.");
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nama' => ['required', 'regex:/^\d{4}\/\d{4}$/', Rule::unique('tahun_ajarans')->where('semester', $request->semester)->ignore($id)],
            'semester' => 'required|in:Ganjil,Genap',
            'tanggal_rapor' => 'nullable|date',
            'kepala_sekolah' => 'nullable|string|max:255',
            'nip_kepsek' => 'nullable|string|max:30',
        ], ['nama.regex' => 'Format tahun ajaran harus seperti 2025/2026.', 'nama.unique' => 'Tahun ajaran & semester sudah ada.']);
    }
}
