@extends('layouts.main', ['title' => 'Diskon'])
@section('title-content')
<i class="fas fa-tags mr-2"></i>
Diskon
@endsection

@section('content')
@if (session('store') == 'success')
<x-alert type="success">
    <strong>Berhasil dibuat!</strong> Diskon berhasil dibuat.
</x-alert>
@endif

@if (session('update') == 'success')
<x-alert type="success">
    <strong>Berhasil diupdate!</strong> Diskon berhasil diupdate.
</x-alert>
@endif

@if (session('destroy') == 'success')
<x-alert type="success">
    <strong>Berhasil dihapus!</strong> Diskon berhasil dihapus.
</x-alert>
@endif

@if (session('toggle') == 'success')
<x-alert type="success">
    <strong>Berhasil diubah!</strong> Status diskon berhasil diubah.
</x-alert>
@endif

<div class="card card-orange card-outline">
    <div class="card-header form-inline">
        <a href="{{ route('diskon.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Tambah Diskon
        </a>
        <form action="?" method="get" class="ml-auto">
            <div class="input-group">
                <input type="text" class="form-control" name="search" value="<?= request()->search ?>"
                    placeholder="Kode atau Nama Diskon">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode</th>
                    <th>Nama Diskon</th>
                    <th>Jenis</th>
                    <th>Nilai</th>
                    <th>Minimal Belanja</th>
                    <th>Status</th>
                    <th>Periode</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($diskons as $key => $diskon)
                <tr>
                    <td>{{ ($diskons->firstItem() ?? 0) + $key }}</td>
                    <td><span class="badge badge-info">{{ $diskon->kode_diskon }}</span></td>
                    <td>{{ $diskon->nama_diskon }}</td>
                    <td>
                        @if($diskon->jenis_diskon == 'persen')
                        <span class="badge badge-success">{{ $diskon->nilai_diskon }}%</span>
                        @else
                        <span class="badge badge-warning">Rp {{ number_format($diskon->nilai_diskon, 0, ',', '.') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($diskon->jenis_diskon == 'persen')
                            {{ $diskon->nilai_diskon }}%
                            @if($diskon->maksimal_diskon) 
                                <small class="text-muted">(Max: Rp {{ number_format($diskon->maksimal_diskon, 0, ',', '.') }})</small>
                            @endif
                        @else
                            Rp {{ number_format($diskon->nilai_diskon, 0, ',', '.') }}
                        @endif
                    </td>
                    <td>Rp {{ number_format($diskon->minimal_belanja, 0, ',', '.') }}</td>
                    <td>
                        @php
                        $status = $diskon->status;
                        $badgeClass = match($status) {
                            'Aktif' => 'badge-success',
                            'Tidak Aktif' => 'badge-secondary',
                            'Expired' => 'badge-danger',
                            'Belum Dimulai' => 'badge-warning',
                            'Kuota Habis' => 'badge-dark',
                            default => 'badge-secondary'
                        };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                        @if($diskon->kuota)
                        <br><small class="text-muted">{{ $diskon->terpakai }}/{{ $diskon->kuota }}</small>
                        @endif
                    </td>
                    <td>
                        <small>
                            {{ $diskon->tanggal_mulai->format('d/m/Y') }} - 
                            {{ $diskon->tanggal_berakhir->format('d/m/Y') }}
                        </small>
                    </td>
                    <td class="text-right">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-xs btn-secondary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cog"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('diskon.edit', $diskon) }}">
                                    <i class="fas fa-edit mr-2"></i> Edit
                                </a>
                                <form action="{{ route('diskon.toggle', $diskon) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-toggle-{{ $diskon->aktif ? 'off' : 'on' }} mr-2"></i>
                                        {{ $diskon->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <div class="dropdown-divider"></div>
                                <form action="{{ route('diskon.destroy', $diskon) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus diskon ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash mr-2"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $diskons->links('vendor.pagination.bootstrap-4') }}
    </div>
</div>
@endsection