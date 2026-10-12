@php($no = 0)
<table class="rapor-table" data-testid="rapor-nilai-table">
    <thead><tr><th style="width:5%">No</th><th style="width:28%">Mata Pelajaran</th><th style="width:10%">Nilai Akhir</th><th>Capaian Kompetensi</th></tr></thead>
    <tbody>
    @forelse($nilais as $kelompok => $list)
        <tr class="kelompok"><td colspan="4">{{ $kelompok === 'Muatan Lokal' ? 'Muatan Lokal' : 'Kelompok Mata Pelajaran '.$kelompok }}</td></tr>
        @foreach($list as $n)
            <tr>
                <td class="c">{{ ++$no }}</td>
                <td>{{ $n->mapel->nama }}</td>
                <td class="c fw-bold {{ $n->nilai_akhir !== null && $n->nilai_akhir < $n->mapel->kkm ? 'merah' : '' }}">{{ $n->nilai_akhir ?? '-' }}</td>
                <td class="desc">
                    @if($n->capaian_tertinggi)<div>Menunjukkan penguasaan yang baik dalam {{ $n->capaian_tertinggi }}.</div>@endif
                    @if($n->capaian_terendah)<div class="mt-1">Perlu peningkatan dalam {{ $n->capaian_terendah }}.</div>@endif
                </td>
            </tr>
        @endforeach
    @empty
        <tr><td colspan="4" class="c">Belum ada nilai untuk semester ini.</td></tr>
    @endforelse
    </tbody>
</table>

<div class="rapor-grid">
    <table class="rapor-table">
        <thead><tr><th colspan="2">Ketidakhadiran</th></tr></thead>
        <tbody>
            <tr><td>Sakit</td><td class="c" data-testid="rapor-sakit">{{ $rapor->sakit ?? 0 }} hari</td></tr>
            <tr><td>Izin</td><td class="c" data-testid="rapor-izin">{{ $rapor->izin ?? 0 }} hari</td></tr>
            <tr><td>Tanpa Keterangan</td><td class="c" data-testid="rapor-alpa">{{ $rapor->alpa ?? 0 }} hari</td></tr>
        </tbody>
    </table>
    <table class="rapor-table">
        <thead><tr><th>Catatan Wali Kelas</th></tr></thead>
        <tbody><tr><td class="desc" style="height:84px" data-testid="rapor-catatan">{{ $rapor->catatan_wali ?? '-' }}</td></tr></tbody>
    </table>
</div>
