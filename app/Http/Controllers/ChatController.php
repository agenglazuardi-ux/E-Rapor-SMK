<?php

namespace App\Http\Controllers;

use App\Models\ChatPercakapan;
use App\Models\ChatPesan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Chat pribadi (1 lawan 1) khusus admin dan guru. Pesan baru diambil lewat polling dari halaman.
class ChatController extends Controller
{
    private const ROLE_CHAT = ['admin', 'guru'];

    private const BATAS_PESAN = 50;

    public function index(Request $request)
    {
        return view('chat.index', ['dengan' => (int) $request->query('dengan')]);
    }

    // Semua admin & guru lain beserta pesan terakhir dan jumlah pesan belum dibaca.
    public function kontak(Request $request): JsonResponse
    {
        $saya = (int) $request->user()->id;
        $kontak = User::whereIn('role', self::ROLE_CHAT)->where('id', '!=', $saya)->orderBy('name')->get(['id', 'name', 'role']);

        $percakapan = ChatPercakapan::where('user_a_id', $saya)->orWhere('user_b_id', $saya)->get()
            ->keyBy(fn ($p) => (int) $p->user_a_id === $saya ? (int) $p->user_b_id : (int) $p->user_a_id);
        $ids = $percakapan->pluck('id');

        $terakhir = $ids->isEmpty() ? collect() : ChatPesan::whereIn('id', ChatPesan::selectRaw('MAX(id)')->whereIn('percakapan_id', $ids)->groupBy('percakapan_id'))
            ->get()->keyBy('percakapan_id');
        $belum = $ids->isEmpty() ? collect() : ChatPesan::whereIn('percakapan_id', $ids)->where('pengirim_id', '!=', $saya)->whereNull('dibaca_at')
            ->selectRaw('percakapan_id, COUNT(*) as n')->groupBy('percakapan_id')->pluck('n', 'percakapan_id');

        $data = $kontak->map(function ($u) use ($percakapan, $terakhir, $belum, $saya) {
            $p = $percakapan->get($u->id);
            $t = $p ? $terakhir->get($p->id) : null;

            return [
                'id' => $u->id,
                'nama' => $u->name,
                'role' => $u->role,
                'belum' => (int) ($p ? $belum->get($p->id, 0) : 0),
                'terakhir' => $t ? mb_strimwidth($t->isi, 0, 60, '…') : null,
                'dari_saya' => $t ? (int) $t->pengirim_id === $saya : false,
                'waktu' => $t?->created_at?->toIso8601String(),
            ];
        })->sort(function ($a, $b) {
            if ($a['waktu'] && $b['waktu']) {
                return strcmp($b['waktu'], $a['waktu']);
            }
            if ($a['waktu'] || $b['waktu']) {
                return $a['waktu'] ? -1 : 1;
            }

            return strcasecmp($a['nama'], $b['nama']);
        })->values();

        return response()->json(['kontak' => $data, 'total_belum' => (int) $data->sum('belum')]);
    }

    // Jumlah total pesan belum dibaca (untuk badge di menu).
    public function belumDibaca(Request $request): JsonResponse
    {
        $saya = (int) $request->user()->id;
        $jumlah = ChatPesan::whereNull('dibaca_at')->where('pengirim_id', '!=', $saya)
            ->whereIn('percakapan_id', ChatPercakapan::select('id')->where(fn ($q) => $q->where('user_a_id', $saya)->orWhere('user_b_id', $saya)))
            ->count();

        return response()->json(['jumlah' => $jumlah]);
    }

    // Tanpa parameter: pesan terbaru. ?setelah=ID: pesan baru. ?sebelum=ID: pesan yang lebih lama.
    public function pesan(Request $request, User $lawan): JsonResponse
    {
        $this->pastikanLawanValid($request, $lawan);
        $saya = (int) $request->user()->id;
        $p = ChatPercakapan::cari($saya, (int) $lawan->id);
        if (! $p) {
            return response()->json(['pesan' => [], 'ada_lama' => false, 'terbaca_sampai' => 0]);
        }

        $q = ChatPesan::query()->where('percakapan_id', $p->id);
        $adaLama = false;
        /** @var \Illuminate\Support\Collection<int, ChatPesan> $pesan */
        if ($request->filled('setelah')) {
            $pesan = $q->where('id', '>', (int) $request->query('setelah'))->orderBy('id')->limit(200)->get();
        } else {
            if ($request->filled('sebelum')) {
                $q->where('id', '<', (int) $request->query('sebelum'));
            }
            $rows = $q->orderByDesc('id')->limit(self::BATAS_PESAN + 1)->get();
            $adaLama = $rows->count() > self::BATAS_PESAN;
            $pesan = $rows->take(self::BATAS_PESAN)->reverse()->values();
        }

        // Pesan dari lawan yang sekarang dibuka dianggap sudah dibaca.
        ChatPesan::where('percakapan_id', $p->id)->where('pengirim_id', $lawan->id)->whereNull('dibaca_at')->update(['dibaca_at' => now()]);
        $terbacaSampai = (int) ChatPesan::where('percakapan_id', $p->id)->where('pengirim_id', $saya)->whereNotNull('dibaca_at')->max('id');

        return response()->json([
            'pesan' => $pesan->map(fn ($m) => $this->format($m, $saya))->values(),
            'ada_lama' => $adaLama,
            'terbaca_sampai' => $terbacaSampai,
        ]);
    }

    public function kirim(Request $request, User $lawan): JsonResponse
    {
        $this->pastikanLawanValid($request, $lawan);
        $data = $request->validate(['isi' => 'required|string|max:2000'], [
            'isi.required' => 'Pesan tidak boleh kosong.',
            'isi.max' => 'Pesan terlalu panjang (maksimal 2000 karakter).',
        ]);
        $saya = (int) $request->user()->id;
        $p = ChatPercakapan::antara($saya, (int) $lawan->id);
        $m = ChatPesan::create(['percakapan_id' => $p->id, 'pengirim_id' => $saya, 'isi' => $data['isi']]);

        return response()->json(['pesan' => $this->format($m, $saya)], 201);
    }

    // Lawan bicara harus admin/guru dan bukan diri sendiri (siswa tidak pernah bisa dihubungi).
    private function pastikanLawanValid(Request $request, User $lawan): void
    {
        abort_if((int) $lawan->id === (int) $request->user()->id || ! in_array($lawan->role, self::ROLE_CHAT, true), 404);
    }

    private function format(ChatPesan $m, int $saya): array
    {
        return [
            'id' => (int) $m->id,
            'dari_saya' => (int) $m->pengirim_id === $saya,
            'isi' => $m->isi,
            'dibuat' => $m->created_at->toIso8601String(),
        ];
    }
}