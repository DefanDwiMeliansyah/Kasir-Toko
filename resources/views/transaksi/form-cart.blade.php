<div class="card card-orange card-outline">
    <div class="card-body">
        <h3 class="m-0 text-right">Rp <span id="totalJumlah">0</span> ,-</h3>
    </div>
</div>

<!-- Form Diskon -->
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-tags mr-2"></i>
            Diskon
        </h3>
    </div>
    <div class="card-body">
        <div id="form-diskon">
            <div class="input-group">
                <input type="text" id="kodeDiskon" class="form-control" placeholder="Masukkan kode diskon">
                <div class="input-group-append">
                    <button type="button" class="btn btn-info" onclick="applyDiscount()">
                        <i class="fas fa-check mr-1"></i> Terapkan
                    </button>
                </div>
            </div>
        </div>

        <div id="diskon-active" style="display: none;">
            <div class="alert alert-success mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong id="namaDiskonActive"></strong><br>
                        <small>Kode: <span id="kodeDiskonActive"></span></small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDiscount()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="text-right">
                <strong class="text-success">Total Hemat: Rp <span id="nominalDiskonActive">0</span></strong>
            </div>
        </div>

        <!-- Alert untuk diskon yang otomatis dibatalkan -->
        <div id="diskon-auto-removed-alert" class="alert alert-warning" style="display: none;">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Diskon otomatis dibatalkan karena produk yang mendapat diskon telah dihapus dari keranjang.
        </div>
    </div>
</div>

<form action="{{ route('transaksi.store') }}" method="POST" class="card card-orange card-outline">
    @csrf
    <div class="card-body">
        <p class="text-right">
            Tanggal : {{ $tanggal }}
        </p>

        <div class="row">
            <div class="col">
                <label>Nama Pelanggan</label>
                <input type="text" id="namaPelanggan"
                    class="form-control @error('pelanggan_id') is-invalid @enderror"
                    disabled>
                @error('pelanggan_id')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror

                <input type="hidden" name="pelanggan_id" id="pelangganId">
            </div>

            <div class="col">
                <label>Nama Kasir</label>
                <input type="text" class="form-control" value="{{ $nama_kasir }}" disabled>
            </div>
        </div>

        <table class="table table-striped table-hover table-bordered mt-3">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Sub Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="resultCart">
                <tr>
                    <td colspan="5" class="text-center"> Tidak ada data.</td>
                </tr>
            </tbody>
        </table>

        <div class="row mt-3">
            <div class="col-2 offset-6">
                <p>Total</p>
                <p>Pajak 10 %</p>
                <p id="diskonRow" style="display: none;">Diskon</p>
                <p>Total Bayar</p>
            </div>
            <div class="col-4 text-right">
                <p id="subtotal">0</p>
                <p id="taxAmount">0</p>
                <p id="diskonAmount" class="text-success" style="display: none;">-0</p>
                <p id="total">0</p>
            </div>
        </div>

        <div class="col-6 offset-6">
            <hr class="mt-0">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">Cash</span>
                </div>
                <input type="text" name="cash" class="form-control @error('cash') is-invalid @enderror"
                    placeholder="Jumlah Cash" value="{{ old('cash') }}">
                <input type="hidden" name="total_bayar" id="totalBayar" />
                <input type="hidden" name="diskon_id" id="diskonId" />
                @error('cash')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
                @enderror
            </div>
        </div>

        <div class="col-12 form-inline mt-3">
            <a href="{{ route('transaksi.index') }}" class="btn btn-secondary mr-2">Ke Transaksi</a>
            <a href="{{ route('cart.clear') }}" class="btn btn-danger">Kosongkan</a>

            <button type="submit" class="btn btn-success ml-auto">
                <i class="fas fa-money-bill-wave mr-2"></i> Bayar Transaksi
            </button>
        </div>
    </div>
</form>

<script>
    // Variabel global untuk menyimpan data diskon per item
    let activeDiscountItems = {};

    $(function() {
        fetchCart();
    });

    function fetchCart() {
        $.getJSON("/cart",
            function(response) {
                $('#resultCart').empty();

                const {
                    items,
                    subtotal,
                    tax_amount,
                    total,
                    extra_info
                } = response;

                // Reset discount items
                activeDiscountItems = {};

                $('#subtotal').html(rupiah(subtotal));
                $('#taxAmount').html(rupiah(tax_amount));

                // PERBAIKAN: Handle diskon yang dihapus otomatis
                if (extra_info && extra_info.diskon_auto_removed) {
                    // Reset UI diskon
                    resetDiscountUI();

                    // Tampilkan alert
                    $('#diskon-auto-removed-alert').show();
                    setTimeout(() => {
                        $('#diskon-auto-removed-alert').fadeOut();
                    }, 5000);
                } else {
                    $('#diskon-auto-removed-alert').hide();
                }

                // Handle diskon
                let finalTotal = total; // Backend sudah kirim total yang benar
                if (extra_info && extra_info.diskon && !extra_info.diskon_auto_removed) {
                    const diskon = extra_info.diskon;
                    // PERBAIKAN: finalTotal sudah benar dari backend, tidak perlu dikurangi lagi
                    finalTotal = total;

                    // Simpan data diskon per item
                    if (diskon.items_diskon) {
                        activeDiscountItems = diskon.items_diskon;
                    }

                    // Tampilkan info diskon
                    showDiscountUI(diskon);
                } else {
                    // Reset UI diskon jika tidak ada diskon atau diskon dihapus
                    resetDiscountUI();
                }

                $('#total, #totalJumlah').html(rupiah(finalTotal));
                $('#totalBayar').val(finalTotal);

                for (const property in items) {
                    addRow(items[property])
                }

                if (Array.isArray(items)) {
                    $('#resultCart').html('<tr><td colspan="5" class="text-center">Tidak ada data.</td></tr>');
                }

                if (!Array.isArray(extra_info) && extra_info && extra_info.pelanggan) {
                    const {
                        id,
                        nama
                    } = extra_info.pelanggan
                    $('#namaPelanggan').val(nama);
                    $('#pelangganId').val(id);
                }
            }
        );
    }

    // TAMBAHAN: Fungsi untuk reset UI diskon
    function resetDiscountUI() {
        $('#diskonRow, #diskonAmount').hide();
        $('#form-diskon').show();
        $('#diskon-active').hide();
        $('#diskonId').val('');
        $('#kodeDiskon').val(''); // Reset input kode diskon
        activeDiscountItems = {};
    }

    // TAMBAHAN: Fungsi untuk tampilkan UI diskon
    function showDiscountUI(diskon) {
        $('#diskonRow, #diskonAmount').show();
        $('#diskonAmount').html('-' + rupiah(diskon.nominal));

        // Update form diskon
        $('#form-diskon').hide();
        $('#diskon-active').show();
        $('#kodeDiskonActive').text(diskon.kode);
        $('#namaDiskonActive').text(diskon.nama);
        $('#nominalDiskonActive').text(rupiah(diskon.nominal));
        $('#diskonId').val(diskon.id);
    }

    function addRow(item) {
        const {
            hash,
            title,
            quantity,
            price,
            total_price
        } = item;

        let btn = `<button type="button" class="btn btn-xs btn-success mr-2" onclick="ePut('${hash}',1)">
                <i class="fas fa-plus"></i>
            </button>`;

        btn += `<button type="button" class="btn btn-xs btn-primary mr-2" onclick="ePut('${hash}',-1)">
                <i class="fas fa-minus"></i>
            </button>`;

        btn += `<button type="button" class="btn btn-xs btn-danger" onclick="eDel('${hash}')">
                <i class="fas fa-times"></i>
            </button>`;

        // Cek apakah item ini mendapat diskon
        let titleDisplay = title;
        let subtotalDisplay = rupiah(total_price);

        if (activeDiscountItems[hash]) {
            const discountInfo = activeDiscountItems[hash];
            titleDisplay += `<br><small class="text-success"><i class="fas fa-tag mr-1"></i>Diskon: -${rupiah(discountInfo.diskon_nominal)}</small>`;
            subtotalDisplay = `<span style="text-decoration: line-through; color: #6c757d;">${rupiah(discountInfo.subtotal_asli)}</span><br>
                              <strong class="text-success">${rupiah(discountInfo.subtotal_setelah_diskon)}</strong>`;
        }

        const row = `<tr>
                <td>${titleDisplay}</td>
                <td>${quantity}</td>
                <td>${rupiah(price)}</td>
                <td>${subtotalDisplay}</td>
                <td>${btn}</td>
            </tr>`;

        $('#resultCart').append(row);
    }

    function rupiah(number) {
        return new Intl.NumberFormat("id-ID").format(number);
    }

    function ePut(hash, qty) {
        $.ajax({
            type: "PUT",
            url: "/cart/" + hash,
            data: {
                qty: qty
            },
            dataType: "json",
            success: function(response) {
                fetchCart()
            }
        });
    }

    function eDel(hash) {
        $.ajax({
            type: "DELETE",
            url: "/cart/" + hash,
            dataType: "json",
            success: function(response) {
                fetchCart()
            }
        });
    }

    function applyDiscount() {
        const kodeDiskon = $('#kodeDiskon').val();

        if (!kodeDiskon) {
            alert('Silakan masukkan kode diskon');
            return;
        }

        $.ajax({
            type: "POST",
            url: "/cart/apply-discount",
            data: {
                kode_diskon: kodeDiskon,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $('#kodeDiskon').val('');
                    fetchCart();

                    // Tampilkan notifikasi sukses
                    showAlert('success', response.message);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showAlert('danger', response.message || 'Terjadi kesalahan');
            }
        });
    }

    function removeDiscount() {
        $.ajax({
            type: "POST",
            url: "/cart/remove-discount",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    // PERBAIKAN: Gunakan data cart yang dikembalikan dari response
                    if (response.cart_data) {
                        updateCartUI(response.cart_data);
                    } else {
                        // Fallback: refresh cart jika tidak ada data
                        fetchCart();
                    }
                    showAlert('success', response.message);
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showAlert('danger', response.message || 'Terjadi kesalahan');
            }
        });
    }

    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show mt-2" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`;

        // Masukkan ke dalam container alert jika ada
        if ($('#alert-container').length) {
            $('#alert-container').html(alertHtml);
        } else {
            // Jika tidak ada container khusus, tambahkan ke atas form diskon
            $('#form-diskon').before(alertHtml);
        }

        // Auto close alert setelah 5 detik
        setTimeout(() => {
            $('.alert').alert('close');
        }, 5000);
    }

    function updateCartUI(cartData) {
        $('#resultCart').empty();

        const {
            items,
            subtotal,
            tax_amount,
            total,
            extra_info
        } = cartData;

        // Reset discount items
        activeDiscountItems = {};

        $('#subtotal').html(rupiah(subtotal));
        $('#taxAmount').html(rupiah(tax_amount));

        // Handle diskon - seharusnya sudah tidak ada setelah dihapus
        let finalTotal = total;
        if (extra_info && extra_info.diskon) {
            const diskon = extra_info.diskon;

            if (diskon.items_diskon) {
                activeDiscountItems = diskon.items_diskon;
            }

            showDiscountUI(diskon);
        } else {
            // Reset UI diskon karena diskon sudah dihapus
            resetDiscountUI();
        }

        $('#total, #totalJumlah').html(rupiah(finalTotal));
        $('#totalBayar').val(finalTotal);

        // Render items
        for (const property in items) {
            addRow(items[property]);
        }

        if (Array.isArray(items)) {
            $('#resultCart').html('<tr><td colspan="5" class="text-center">Tidak ada data.</td></tr>');
        }

        // Handle pelanggan
        if (!Array.isArray(extra_info) && extra_info && extra_info.pelanggan) {
            const {
                id,
                nama
            } = extra_info.pelanggan;
            $('#namaPelanggan').val(nama);
            $('#pelangganId').val(id);
        }
    }
</script>