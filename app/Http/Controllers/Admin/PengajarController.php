<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Pengajar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengajarController extends Controller
{
    public function index(Request $request)
    {
        $items = Pengajar::with('guru', 'mapel', 'kelas')
            ->when($request->kelas_id, fn ($q, $k) => $q->where('kelas_id', $k))
            ->get()->sortBy(fn ($p) => $p->kelas->nama.$p->mapel->urutan);
        $kelas = Kelas::orderBy('nama')->get();

        return view('admin.pengajar.index', compact('items', 'kelas'));
    }

    public function create()
    {
        return view('admin.pengajar.form', $this->formData(new Pengajar));
    }

    public function store(Request $request)
    {
        Pengajar::create($this->validated($request));

        return redirect()->route('admin.pengajar.index')->with('success', 'Penugasan guru mengajar ditambahkan.');
    }

    public function edit(Pengajar $pengajar)
    {
        return view('admin.pengajar.form', $this->formData($pengajar));
    }

    public function update(Request $request, Pengajar $pengajar)
    {
        $pengajar->update($this->validated($request, $pengajar->id));

        return redirect()->route('admin.pengajar.index')->with('success', 'Penugasan diperbarui.');
    }

    public function destroy(Pengajar $pengajar)
    {
        $pengajar->delete();

        return back()->with('success', 'Penugasan dihapus.');
    }

    private function formData(Pengajar $item): array
    {
        return [
            'item' => $item,
            'gurus' => Guru::orderBy('nama')->get(),
            'mapels' => Mapel::orderBy('urutan')->get(),
            'kelas' => Kelas::orderBy('nama')->get(),
        ];
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => ['required', 'exists:mapels,id', Rule::unique('pengajars')->where('kelas_id', $request->kelas_id)->ignore($id)],
        ], ['mapel_id.unique' => 'Mapel ini sudah memiliki guru pengajar di kelas tersebut.']);
    }
}
