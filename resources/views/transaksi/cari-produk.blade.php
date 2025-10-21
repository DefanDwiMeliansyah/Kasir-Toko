<!-- Form Cari Produk -->
<form action="" method="get" id="formCariProduk">
    <div class="input-group">
        <input type="text" class="form-control" placeholder="Nama Produk" id="searchProduk">
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search d-sm-none"></i>
                <span class="d-none d-sm-inline">Cari</span>
            </button>
        </div>
    </div>
</form>

<div class="mt-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted">Hasil Pencarian:</small>
    </div>
    
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <tbody id="resultProduk">
                <tr>
                    <td class="text-center text-muted">Ketik minimal 3 karakter untuk mencari</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Input Quantity -->
<div class="modal fade" id="qtyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <form id="qtyForm">
                <div class="modal-header">
                    <h5 class="modal-title">Masukkan Jumlah</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="qtyInput" class="form-label">Quantity:</label>
                        <input type="number" class="form-control form-control-lg text-center" 
                               id="qtyInput" value="1" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cart-plus mr-2"></i>Tambah ke Keranjang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let selectedKodeProduk = null;
    
    $(function () {
        $('#formCariProduk').submit(function (e) {
            e.preventDefault();
            const search = $('#searchProduk').val().trim();
            if (search.length >= 1) {
                fetchCariProduk(search);
            } else {
                $('#resultProduk').html('<tr><td class="text-center text-muted">Ketik minimal 3 karakter untuk mencari</td></tr>');
            }
        });
        
        // Real-time search
        $('#searchProduk').on('input', function() {
            const search = $(this).val().trim();
            if (search.length >= 1) {
                fetchCariProduk(search);
            } else if (search.length === 0) {
                $('#resultProduk').html('<tr><td class="text-center text-muted">Ketik minimal 3 karakter untuk mencari</td></tr>');
            }
        });
        
        // Auto focus on quantity input when modal opens
        $('#qtyModal').on('shown.bs.modal', function() {
            $('#qtyInput').focus().select();
        });
    });

    function fetchCariProduk(search) {
        $('#resultProduk').html('<tr><td class="text-center text-muted"><i class="fas fa-spinner fa-spin mr-2"></i>Mencari...</td></tr>');
        
        $.getJSON("/transaksi/produk", { search: search }, function (response) {
            $('#resultProduk').html('');
            if (response.length === 0) {
                $('#resultProduk').html('<tr><td class="text-center text-muted">Tidak ada hasil ditemukan</td></tr>');
            } else {
                response.forEach(item => {
                    addResultProduk(item);
                });
            }
        }).fail(function() {
            $('#resultProduk').html('<tr><td class="text-center text-danger">Terjadi kesalahan saat mencari</td></tr>');
        });
    }

    function addResultProduk(item) {
        const { nama_produk, kode_produk } = item;

        const btn = `<button type="button"
                        class="btn btn-xs btn-success btn-block"
                        onclick="addItem('${kode_produk}')"
                        title="Tambah ${nama_produk} ke keranjang">
                        <i class="fas fa-plus mr-1 d-none d-sm-inline"></i>
                        <span class="d-none d-sm-inline">Tambah</span>
                        <i class="fas fa-plus d-sm-none"></i>
                    </button>`;

        const row = `<tr class="cursor-pointer" onclick="addItem('${kode_produk}')">
                        <td>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${nama_produk}</strong>
                                    <div class="d-none d-md-block">
                                        <small class="text-muted">Kode: ${kode_produk}</small>
                                    </div>
                                </div>
                                <div class="ml-2">
                                    ${btn}
                                </div>
                            </div>
                        </td>
                    </tr>`;
        $('#resultProduk').append(row);
    }

    function addItem(kode_produk) {
        if (!kode_produk) return;

        selectedKodeProduk = kode_produk; // simpan ke global
        $('#qtyInput').val(1);
        $('#qtyModal').modal('show');
    }

    $('#qtyForm').submit(function(e) {
        e.preventDefault();
        const qty = parseInt($('#qtyInput').val()) || 1;
        if (!selectedKodeProduk || qty < 1) return;

        // Show loading state
        const submitBtn = $('#qtyForm button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Menambahkan...').prop('disabled', true);

        $('#qtyModal').modal('hide');
        
        $.post("/cart", {
            kode_produk: selectedKodeProduk,
            quantity: qty
        }, function(response) {
            fetchCart(); // Reload cart
            
            // Show success feedback
            const productRow = $(`button[onclick="addItem('${selectedKodeProduk}')"]`).closest('tr');
            productRow.addClass('table-success');
            setTimeout(() => {
                productRow.removeClass('table-success');
            }, 1000);
            
        }, "json").fail(function(error) {
            if (error.status === 422) {
                $('#msgErrorBarcode').addClass('d-block')
                    .html(error.responseJSON.errors.kode_produk[0]);
                $('#barcode').addClass('is-invalid');
            }
            
            // Show error feedback
            const productRow = $(`button[onclick="addItem('${selectedKodeProduk}')"]`).closest('tr');
            productRow.addClass('table-danger');
            setTimeout(() => {
                productRow.removeClass('table-danger');
            }, 1000);
            
        }).always(function() {
            // Reset button state
            submitBtn.html(originalText).prop('disabled', false);
            selectedKodeProduk = null;
        });
    });
</script>
@endpush

<style>
@media (max-width: 576px) {
    .table-sm td {
        padding: 0.5rem 0.25rem;
        font-size: 0.875rem;
    }
    
    .btn-xs {
        padding: 0.25rem 0.375rem;
        font-size: 0.75rem;
        min-height: 28px;
    }
    
    .input-group .btn {
        min-height: 38px;
        padding: 0.375rem 0.5rem;
    }
    
    .form-control {
        min-height: 38px;
    }
    
    .form-control-lg {
        min-height: 48px;
        font-size: 1.125rem;
    }
    
    .modal-footer .btn {
        flex: 1;
        margin: 0 0.25rem;
    }
}

@media (max-width: 768px) {
    .table-responsive {
        border: none;
        margin-bottom: 0;
    }
    
    .d-flex {
        flex-wrap: wrap;
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    .cursor-pointer:hover {
        background-color: rgba(0, 123, 255, 0.1);
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-footer {
        padding: 0.75rem;
    }
}

/* Touch-friendly improvements */
@media (max-width: 768px) {
    .table td {
        padding: 0.75rem 0.5rem;
    }
    
    .btn {
        touch-action: manipulation;
        min-height: 44px; /* Better touch target */
    }
    
    .btn-xs {
        min-height: 36px;
    }
    
    .modal-content {
        border-radius: 0.5rem;
    }
}

/* Loading and feedback states */
.table-success {
    animation: successFade 1s ease-in-out;
}

.table-danger {
    animation: errorFade 1s ease-in-out;
}

@keyframes successFade {
    0% { background-color: transparent; }
    50% { background-color: #d4edda; }
    100% { background-color: transparent; }
}

@keyframes errorFade {
    0% { background-color: transparent; }
    50% { background-color: #f8d7da; }
    100% { background-color: transparent; }
}

/* Improved spacing */
@media (max-width: 576px) {
    .mt-3 {
        margin-top: 1rem !important;
    }
    
    .mb-2 {
        margin-bottom: 0.5rem !important;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
}

/* Modal enhancements */
@media (max-width: 576px) {
    .modal-sm {
        max-width: calc(100% - 1rem);
    }
    
    .modal-header {
        padding: 0.75rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .modal-body {
        padding: 1rem 0.75rem;
    }
    
    .modal-title {
        font-size: 1.1rem;
    }
}

/* Enhanced search experience */
.input-group {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.375rem;
    overflow: hidden;
}

.input-group .form-control {
    border-right: none;
}

.input-group .btn {
    border-left: none;
    z-index: 2;
}

/* Better visual feedback */
.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

@media (min-width: 768px) {
    .table tbody tr:hover .btn {
        transform: scale(1.05);
        transition: transform 0.1s ease-in-out;
    }
}
</style>