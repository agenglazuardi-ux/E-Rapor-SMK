<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $items = Guru::with('kelasWali')->withCount('pengajars')
            ->when($request->q, fn ($q, $s) => $q->where('nama', 'like', "%$s%")->orWhere('nip', 'like', "%$s%"))
            ->orderBy('nama')->paginate(15)->withQueryString();

        return view('admin.guru.index', compact('items'));
    }

    public function create()
    {
        return view('admin.guru.form', ['item' => new Guru]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['nama'],
                'username' => $data['nip'],
                'role' => 'guru',
                'password' => $data['password'] ?: $data['nip'],
            ]);
            $user->guru()->create($data);
        });

        return redirect()->route('admin.guru.index')->with('success', 'Guru ditambahkan. Login: NIP, password default: NIP (jika dikosongkan).');
    }

    public function edit(Guru $guru)
    {
        return view('admin.guru.form', ['item' => $guru]);
    }

    public function update(Request $request, Guru $guru)
    {
        $data = $this->validated($request, $guru);
        DB::transaction(function () use ($data, $guru) {
            $guru->update($data);
            $userData = ['name' => $data['nama'], 'username' => $data['nip']];
            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }
            $guru->user->update($userData);
        });

        return redirect()->route('admin.guru.index')->with('success', 'Data guru diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $guru->user->delete();

        return back()->with('success', 'Guru dihapus.');
    }

    private function validated(Request $request, ?Guru $guru = null): array
    {
        return $request->validate([
            'nip' => 'required|string|max:30|unique:gurus,nip,'.$guru?->id.'|unique:users,username,'.$guru?->user_id,
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'telepon' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
        ]);
    }
}
