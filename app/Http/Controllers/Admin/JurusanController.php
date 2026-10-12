<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index()
    {
        $items = Jurusan::withCount('kelas')->orderBy('kode')->get();

        return view('admin.jurusan.index', compact('items'));
    }

    public function create()
    {
        return view('admin.jurusan.form', ['item' => new Jurusan]);
    }

    public function store(Request $request)
    {
        Jurusan::create($this->validated($request));

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan ditambahkan.');
    }

    public function edit(Jurusan $jurusan)
    {
        return view('admin.jurusan.form', ['item' => $jurusan]);
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $jurusan->update($this->validated($request, $jurusan->id));

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        if ($jurusan->kelas()->exists()) {
            return back()->with('error', 'Jurusan masih memiliki kelas, tidak dapat dihapus.');
        }
        $jurusan->delete();

        return back()->with('success', 'Jurusan dihapus.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'kode' => 'required|string|max:10|unique:jurusans,kode,'.$id,
            'nama' => 'required|string|max:255',
        ]);
    }
}
