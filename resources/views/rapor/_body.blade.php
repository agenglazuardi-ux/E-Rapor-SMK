{{-- Variabel opsional $bagian: 'nilai' (hanya bagian A) | 'lain' (bagian B dst.) | tidak diisi = semua. --}}
@php($bagian = $bagian ?? 'semua')
@php($genap = $ta->semester === 'Genap')
@if($bagian !== 'lain')
@php($no = 0)
<div class="rapor-sec">A. Nilai Akademik</div>
<table class="rapor-table" data-testid="rapor-nilai-table">
    <thead><tr><th style="width:5%">No</th><th style="width:28%">Mata Pelajaran</th><th style="width:10%">Nilai Akhir</th><th>Capaian Kompetensi</th></tr></thead>
    <tbody>
    @forelse($nilais as $kelompok => $list)
        <tr class="kelompok"><td colspan="4">{{ chr(65 + $loop->index) }}. {{ $kelompok === 'Muatan Lokal' ? 'Muatan Lokal' : 'Kelompok Mata Pelajaran '.$kelompok }}</td></tr>
        @foreach($list as $n)
            <tr>
                <td class="c">{{ ++$no }}</td>
                <td>{{ $n->mapel->nama }}</td>
                <td class="c {{ $n->nilai_akhir !== null && $n->nilai_akhir < $n->mapel->kkm ? 'merah' : '' }}">{{ $n->nilai_akhir ?? '-' }}</td>
                <td class="desc">
                    @if($n->capaian_tertinggi)<div>Menunjukkan penguasaan yang baik dalam {{ $n->capaian_tertinggi }}</div>@endif
                    @if($n->capaian_terendah)<div class="mt-1">Perlu peningkatan dalam {{ $n->capaian_terendah }}</div>@endif
                </td>
            </tr>
        @endforeach
    @empty
        <tr><td colspan="4" class="c">Belum ada nilai untuk semester ini.</td></tr>
    @endforelse
    </tbody>
</table>
@endif

@if($bagian !== 'nilai')
<div class="rapor-sec">B. Ekstrakurikuler</div>
<table class="rapor-table" data-testid="rapor-ekskul-table">
    <thead><tr><th style="width:5%">No</th><th style="width:35%">Kegiatan Ekstrakurikuler</th><th>Keterangan</th></tr></thead>
    <tbody>
    @forelse($ekskul as $i => $e)
        <tr>
            <td class="c">{{ $i + 1 }}</td>
            <td>{{ $e->ekstrakurikuler->nama }}</td>
            <td class="desc">{{ $e->keterangan ?? '-' }}</td>
        </tr>
    @empty
        <tr><td colspan="3" class="c">Belum ada data ekstrakurikuler untuk semester ini.</td></tr>
    @endforelse
    </tbody>
</table>

<div class="rapor-sec">C. Ketidakhadiran</div>
<table class="rapor-table rapor-absen">
    <tbody>
        <tr><td>Sakit</td><td data-testid="rapor-sakit">: {{ ($rapor->sakit ?? 0) > 0 ? $rapor->sakit.' hari' : '- hari' }}</td></tr>
        <tr><td>Izin</td><td data-testid="rapor-izin">: {{ ($rapor->izin ?? 0) > 0 ? $rapor->izin.' hari' : '- hari' }}</td></tr>
        <tr><td>Tanpa Keterangan</td><td data-testid="rapor-alpa">: {{ ($rapor->alpa ?? 0) > 0 ? $rapor->alpa.' hari' : '- hari' }}</td></tr>
    </tbody>
</table>

@if($genap)
<div class="rapor-sec">D. Kenaikan Kelas</div>
<div class="rapor-box">Berdasarkan hasil yang dicapai pada semester 1 dan 2, maka peserta didik dinyatakan ..............................................................</div>
@endif

<div class="rapor-sec">{{ $genap ? 'E' : 'D' }}. Catatan Wali Kelas</div>
<div class="rapor-box rapor-catatan" data-testid="rapor-catatan" style="white-space:pre-line">{{ $rapor->catatan_wali ?? '-' }}</div>
@endif