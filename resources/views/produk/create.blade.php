@extends('layouts.main', ['title' => 'Produk'])

@section('title-content')
<i class="fas fa-box-open mr-2"></i> Produk
@endsection

@section('content')
<div class="row">
    <div class="col-xl-4 col-lg-6">
        <form method="POST" action="{{ route('produk.store') }}" class="card card-orange card-outline">
            <div class="card-header">
                <h3 class="card-title">Buat Produk Baru</h3>
            </div>
            <div class="card-body">
                @csrf
                <div class="form-group">
                    <label>Kode Produk</label>
                    <x-input name="kode_produk" type="text" />
                </div>
                <div class="form-group">
                    <label>Nama Produk</label>
                    <x-input name="nama_produk" type="text" />
                </div>
                <div class="form-group">
                    <label>Harga Produk</label>
                    <x-input name="harga" type="text" />
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <x-select name="kategori_id" :options="$kategoris" />
                </div>
            </div>
            <div class="card-footer form-inline">
                <button type="submit" class="btn btn-primary">Simpan Produk</button>
                <a href="{{ route('produk.index') }}" class="btn btn-secondary ml-auto">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
