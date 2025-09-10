@extends('layouts.main', ['title' => 'User'])

@section('title-content')
<i class="fas fa-user-tie mr-2"></i>
User
@endsection

@section('content')
{{-- Alert: store --}}
@if (session('store') == 'success')
<x-alert type="success">
    <strong>Berhasil dibuat!</strong> User berhasil dibuat.
</x-alert>
@endif

{{-- Alert: update --}}
@if (session('update') == 'success')
<x-alert type="success">
    <strong>Berhasil diupdate!</strong> User berhasil diupdate.
</x-alert>
@endif

{{-- Alert: destroy --}}
@if (session('destroy') == 'success')
<x-alert type="success">
    <strong>Berhasil dihapus!</strong> User berhasil dihapus.
</x-alert>
@endif

{{-- Alert: error destroy --}}
@if (session('destroy') == 'error')
<x-alert type="danger">
    <strong>Gagal dihapus!</strong> {{ session('destroy_message') }}
</x-alert>
@endif

<div class="card card-orange card-outline">
    <div class="card-header form-inline">
        <a href="{{ route('user.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i> Tambah
        </a>

        <form action="" method="get" class="ml-auto">
            <div class="input-group">
                <input type="text" name="search" class="form-control"
                    value="{{ request()->search }}" placeholder="Nama, Username">
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
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $key => $user)
                <tr>
                    <td>{{ $users->firstItem() + $key }}</td>
                    <td>
                        {{ $user->nama }}
                        @if($user->isSuperAdmin())
                            <span class="badge badge-warning ml-1">Super Admin</span>
                        @endif
                    </td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->role }}</td>
                    <td class="text-right">
                        <a href="{{ route('user.edit', ['user' => $user->id]) }}"
                            class="btn btn-xs text-success p-0 mr-1">
                            <i class="fas fa-edit"></i>
                        </a>

                        @if($user->canBeDeleted())
                            <button type="button"
                                class="btn btn-xs text-danger p-0 btn-delete {{ $user->isAdmin() ? 'btn-delete-admin' : '' }}"
                                data-toggle="modal"
                                data-target="{{ $user->isAdmin() ? '#modalDeleteAdmin' : '#modalDelete' }}"
                                data-url="{{ route('user.destroy', ['user' => $user->id]) }}"
                                data-name="{{ $user->nama }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        @else
                            <span class="btn btn-xs text-muted p-0" title="Super Admin tidak dapat dihapus">
                                <i class="fas fa-lock"></i>
                            </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $users->links('vendor.pagination.bootstrap-4') }}
    </div>
</div>
@endsection

@push('modals')
<x-modal-delete />

{{-- Modal khusus untuk hapus admin --}}
<div class="modal fade" id="modalDeleteAdmin" tabindex="-1" role="dialog" aria-labelledby="modalDeleteAdminLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="modalDeleteAdminLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    PERINGATAN KERAS!
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> PERHATIAN!</h5>
                    Anda akan menghapus user dengan hak akses <strong>ADMINISTRATOR</strong>!
                </div>
                
                <p><strong>Konsekuensi penghapusan admin:</strong></p>
                <ul class="text-danger">
                    <li>User akan kehilangan semua hak akses administrator</li>
                    <li>Tidak dapat mengakses fitur manajemen sistem</li>
                    <li>Tidak dapat mengelola user lain</li>
                    <li>Tindakan ini <strong>TIDAK DAPAT DIBATALKAN</strong></li>
                </ul>
                
                <p class="mt-3">
                    Apakah Anda yakin ingin menghapus admin: <strong><span id="adminName"></span></strong>?
                </p>
                
                <div class="form-group mt-3">
                    <label>Ketik <strong>"HAPUS ADMIN"</strong> untuk konfirmasi:</label>
                    <input type="text" class="form-control" id="confirmText" placeholder="HAPUS ADMIN">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
                <form method="post" id="formDeleteAdmin">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-danger" id="btnConfirmDeleteAdmin" disabled>
                        <i class="fas fa-trash mr-1"></i> Hapus Admin
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle admin delete modal
    $('.btn-delete-admin').on('click', function() {
        var url = $(this).data('url');
        var name = $(this).data('name');
        
        $('#formDeleteAdmin').attr('action', url);
        $('#adminName').text(name);
        $('#confirmText').val('');
        $('#btnConfirmDeleteAdmin').prop('disabled', true);
    });
    
    // Enable delete button when confirmation text is correct
    $('#confirmText').on('input', function() {
        var confirmText = $(this).val();
        if (confirmText === 'HAPUS ADMIN') {
            $('#btnConfirmDeleteAdmin').prop('disabled', false);
        } else {
            $('#btnConfirmDeleteAdmin').prop('disabled', true);
        }
    });
});
</script>
@endpush