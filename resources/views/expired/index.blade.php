@extends('layouts.main', ['title' => 'Barang Expired'])

@section('title-content')
    <i class="fas fa-ban mr-2"></i>
    Barang Expired
@endsection

@section('content')
<div class="card card-danger card-outline">
        <div class="card-header form-inline">
            <a href="{{ route('expired.create') }}" class="btn btn-danger">
                <i class="fas fa-plus mr-2"></i> Tambah
            </a>
            <form action="" method="get" class="ml-auto">
                <div class="input-group">
                    <input type="date" class="form-control" name="search" value="{{ request()->search }}"
                        placeholder="Tanggal">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered table-striped">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>Nama Produk</th>
                    <th width="10%">Jumlah</th>
                    <th width="15%">Tanggal</th>
                    <th width="10%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expireds as $index => $row)
                    <tr>
                        <td>{{ $expireds->firstItem() + $index }}</td>
                        <td>{{ $row->produk->nama_produk }}</td>
                        <td>{{ $row->jumlah }}</td>
                        <td>{{ $row->tanggal }}</td>
                        <td class="text-center">
                            <form action="{{ route('expired.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data expired</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $expireds->links() }}
        </div>
    </div>
</div>
@endsection