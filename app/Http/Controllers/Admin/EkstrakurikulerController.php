<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\Guru;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class EkstrakurikulerController extends Controller
{
    public function index()
    {
        $ta = TahunAjaran::aktif();
        $items = Ekstrakurikuler::with('guru')
            ->withCount(['anggotas' => fn ($q) => $q->where('tahun_ajaran_id', $ta?->id ?? 0)])
            ->orderBy('nama')->get();

        return view('admin.ekstrakurikuler.index', compact('items', 'ta'));
    }

    public function create()
    {
        return view('admin.ekstrakurikuler.form', ['item' => new Ekstrakurikuler, 'gurus' => Guru::orderBy('nama')->get()]);
    }

    public function store(Request $request)
    {
        Ekstrakurikuler::create($this->validated($request));

        return redirect()->route('admin.ekstrakurikuler.index')->with('success', 'Ekstrakurikuler ditambahkan.');
    }

    public function edit(Ekstrakurikuler $ekstrakurikuler)
    {
        return view('admin.ekstrakurikuler.form', ['item' => $ekstrakurikuler, 'gurus' => Guru::orderBy('nama')->get()]);
    }

    public function update(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $ekstrakurikuler->update($this->validated($request, $ekstrakurikuler->id));

        return redirect()->route('admin.ekstrakurikuler.index')->with('success', 'Ekstrakurikuler diperbarui.');
    }

    public function destroy(Ekstrakurikuler $ekstrakurikuler)
    {
        if ($ekstrakurikuler->anggotas()->exists()) {
            return back()->with('error', "Ekstrakurikuler {$ekstrakurikuler->nama} sudah memiliki data anggota/keterangan. Menghapusnya akan menghilangkan data tersebut dari rapor, jadi penghapusan ditolak.");
        }
        $ekstrakurikuler->delete();

        return back()->with('success', 'Ekstrakurikuler dihapus.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nama' => 'required|string|max:255|unique:ekstrakurikulers,nama,'.$id,
            'guru_id' => 'required|exists:gurus,id',
        ], [], ['guru_id' => 'guru pembina']);
    }
}
