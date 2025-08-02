@extends('layouts.main', ['title' => 'Tambah Diskon'])
@section('title-content')
<i class="fas fa-tags mr-2"></i>
Tambah Diskon
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-orange card-outline">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Diskon</h3>
            </div>
            <form action="{{ route('diskon.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kode_diskon">Kode Diskon <span class="text-danger">*</span></label>
                                <input type="text" name="kode_diskon" id="kode_diskon" 
                                    class="form-control @error('kode_diskon') is-invalid @enderror"
                                    value="{{ old('kode_diskon') }}" placeholder="Contoh: DISKON10">
                                @error('kode_diskon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_diskon">Nama Diskon <span class="text-danger">*</span></label>
                                <input type="text" name="nama_diskon" id="nama_diskon" 
                                    class="form-control @error('nama_diskon') is-invalid @enderror"
                                    value="{{ old('nama_diskon') }}" placeholder="Contoh: Diskon Hari Raya">
                                @error('nama_diskon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="3" 
                            class="form-control @error('deskripsi') is-invalid @enderror"
                            placeholder="Deskripsi diskon (opsional)">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jenis_diskon">Jenis Diskon <span class="text-danger">*</span></label>
                                <select name="jenis_diskon" id="jenis_diskon" 
                                    class="form-control @error('jenis_diskon') is-invalid @enderror">
                                    <option value="">Pilih Jenis</option>
                                    <option value="persen" {{ old('jenis_diskon') == 'persen' ? 'selected' : '' }}>Persentase (%)</option>
                                    <option value="nominal" {{ old('jenis_diskon') == 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                                </select>
                                @error('jenis_diskon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="nilai_diskon">Nilai Diskon <span class="text-danger">*</span></label>
                                <input type="number" name="nilai_diskon" id="nilai_diskon" step="0.01" min="0"
                                    class="form-control @error('nilai_diskon') is-invalid @enderror"
                                    value="{{ old('nilai_diskon') }}" placeholder="0">
                                @error('nilai_diskon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="maksimal_diskon">Maksimal Diskon</label>
                                <input type="number" name="maksimal_diskon" id="maksimal_diskon" step="0.01" min="0"
                                    class="form-control @error('maksimal_diskon') is-invalid @enderror"
                                    value="{{ old('maksimal_diskon') }}" placeholder="0">
                                <small class="text-muted">Kosongkan jika tidak ada batas maksimal</small>
                                @error('maksimal_diskon')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="minimal_belanja">Minimal Belanja <span class="text-danger">*</span></label>
                                <input type="number" name="minimal_belanja" id="minimal_belanja" step="0.01" min="0"
                                    class="form-control @error('minimal_belanja') is-invalid @enderror"
                                    value="{{ old('minimal_belanja', 0) }}" placeholder="0">
                                @error('minimal_belanja')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kuota">Kuota Penggunaan</label>
                                <input type="number" name="kuota" id="kuota" min="1"
                                    class="form-control @error('kuota') is-invalid @enderror"
                                    value="{{ old('kuota') }}" placeholder="Kosongkan jika unlimited">
                                @error('kuota')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="jenis_kondisi">Jenis Kondisi <span class="text-danger">*</span></label>
                        <select name="jenis_kondisi" id="jenis_kondisi" 
                            class="form-control @error('jenis_kondisi') is-invalid @enderror">
                            <option value="">Pilih Kondisi</option>
                            <option value="semua" {{ old('jenis_kondisi') == 'semua' ? 'selected' : '' }}>Semua Produk</option>
                            <option value="kategori" {{ old('jenis_kondisi') == 'kategori' ? 'selected' : '' }}>Kategori Tertentu</option>
                            <option value="produk" {{ old('jenis_kondisi') == 'produk' ? 'selected' : '' }}>Produk Tertentu</option>
                        </select>
                        @error('jenis_kondisi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="kondisi-kategori" class="form-group" style="display: none;">
                        <label>Pilih Kategori</label>
                        <div class="row">
                            @foreach($kategoris as $kategori)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="kondisi_ids[]" value="{{ $kategori->id }}" 
                                        class="form-check-input" id="kategori_{{ $kategori->id }}"
                                        {{ in_array($kategori->id, old('kondisi_ids', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="kategori_{{ $kategori->id }}">
                                        {{ $kategori->nama_kategori }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @error('kondisi_ids')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="kondisi-produk" class="form-group" style="display: none;">
                        <label>Pilih Produk</label>
                        <div class="row">
                            @foreach($produks as $produk)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" name="kondisi_ids[]" value="{{ $produk->id }}" 
                                        class="form-check-input" id="produk_{{ $produk->id }}"
                                        {{ in_array($produk->id, old('kondisi_ids', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="produk_{{ $produk->id }}">
                                        {{ $produk->nama_produk }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @error('kondisi_ids')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="tanggal_mulai" id="tanggal_mulai" 
                                    class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                    value="{{ old('tanggal_mulai') }}">
                                @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_berakhir">Tanggal Berakhir <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="tanggal_berakhir" id="tanggal_berakhir" 
                                    class="form-control @error('tanggal_berakhir') is-invalid @enderror"
                                    value="{{ old('tanggal_berakhir') }}">
                                @error('tanggal_berakhir')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" name="aktif" value="1" class="form-check-input" id="aktif"
                                {{ old('aktif', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="aktif">
                                Aktif
                            </label>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <a href="{{ route('diskon.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    // Handle jenis kondisi change
    $('#jenis_kondisi').change(function() {
        const value = $(this).val();
        
        $('#kondisi-kategori, #kondisi-produk').hide();
        
        if (value === 'kategori') {
            $('#kondisi-kategori').show();
            $('#kondisi-produk input[type="checkbox"]').prop('checked', false);
        } else if (value === 'produk') {
            $('#kondisi-produk').show();
            $('#kondisi-kategori input[type="checkbox"]').prop('checked', false);
        } else {
            $('#kondisi-kategori input[type="checkbox"], #kondisi-produk input[type="checkbox"]').prop('checked', false);
        }
    });

    // Trigger change on page load
    $('#jenis_kondisi').trigger('change');

    // Handle jenis diskon change
    $('#jenis_diskon').change(function() {
        const value = $(this).val();
        const maksimalDiskonGroup = $('#maksimal_diskon').closest('.form-group');
        
        if (value === 'persen') {
            maksimalDiskonGroup.show();
            $('#nilai_diskon').attr('placeholder', 'Contoh: 10 (untuk 10%)');
        } else if (value === 'nominal') {
            maksimalDiskonGroup.hide();
            $('#maksimal_diskon').val('');
            $('#nilai_diskon').attr('placeholder', 'Contoh: 5000 (untuk Rp 5.000)');
        }
    });

    // Trigger change on page load
    $('#jenis_diskon').trigger('change');
});
</script>
@endpush

@endsection