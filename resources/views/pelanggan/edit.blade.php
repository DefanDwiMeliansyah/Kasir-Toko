@extends('layouts.main', ['title' => 'Pelanggan'])

@section('title-content')
    <i class="fas fa-users mr-2"></i> Pelanggan
@endsection

@section('content')
<div class="row">
    <div class="col-xl-4 col-lg-6">
        <form method="POST" class="card card-orange card-outline"
            action="{{ route('pelanggan.update', ['pelanggan' => $pelanggan->id]) }}">
            @csrf
            @method('PUT')
            <div class="card-header">
                <h3 class="card-title">Ubah Pelanggan</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <x-input name="nama" type="text" :value="$pelanggan->nama" />
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <x-textarea name="alamat" :value="$pelanggan->alamat" />
                </div>
                <div class="form-group">
                    <label>Nomor Telepon/HP</label>
                    <x-input name="nomor_tlp" type="text" :value="$pelanggan->nomor_tlp" />
                </div>
            </div>
            <div class="card-footer form-inline">
                <button type="submit" class="btn btn-primary">Update Pelanggan</button>
            </div>
        </form>
    </div>
</div>
@endsection
