<form action="{{ route('update_harga_martadah') }}" method="POST" id="form-edit-harga-mtd">
    @csrf
    <input type="hidden" name="no_nota" value="{{ $no_nota }}">

    <div class="modal-header bg-light">
        <h5 class="modal-title"><i class="fas fa-tag text-warning me-2"></i> Edit Harga / Diskon Nota {{ $no_nota }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <div class="card bg-light border-0 mb-3">
            <div class="card-body py-2 px-3">
                <div class="row" style="font-size: 0.9rem;">
                    <div class="col-md-4">
                        <span class="text-muted d-block">Nomor Nota</span>
                        <strong>{{ $no_nota }}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted d-block">Tanggal</span>
                        <strong>{{ tanggal($first->tgl) }}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted d-block">Customer</span>
                        <strong>{{ $first->customer_name }}</strong>
                    </div>
                </div>
            </div>
        </div>

        @if($isPaid)
            <div class="alert alert-warning d-flex align-items-center mb-3">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <strong>Nota Sudah Lunas / Ada Pelunasan!</strong><br>
                    Untuk menjaga keakuratan buku besar dan mutasi kas/bank, silakan batalkan pelunasan nota ini terlebih dahulu di menu <strong>Transaksi &rarr; Piutang</strong> sebelum mengubah harga.
                </div>
            </div>
        @else
            <div class="alert alert-info py-2 px-3 mb-3" style="font-size: 0.85rem;">
                <i class="fas fa-info-circle me-1"></i> Masukkan <strong>Harga Baru (Rp)</strong> pada kolom di bawah. Total nota dan jurnal piutang akan otomatis disesuaikan secara balance.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Produk</th>
                            <th width="18%">Qty Jual</th>
                            <th width="20%">Harga Lama</th>
                            <th width="24%">Harga Baru (Rp)</th>
                            <th width="22%">Subtotal Baru</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $grandTotalLama = 0; ?>
                        @foreach($items as $item)
                            <?php
                                $qtyJual = strtolower($item->tipe) === 'pcs' ? (float) $item->pcs : (float) $item->kg_jual;
                                $unit = strtolower($item->tipe) === 'pcs' ? 'Pcs' : 'Kg';
                                $subtotalLama = (float) $item->total_rp;
                                $grandTotalLama += $subtotalLama;
                            ?>
                            <tr>
                                <td>
                                    <input type="hidden" name="id_invoice_telur[]" value="{{ $item->id_invoice_telur }}">
                                    <strong>{{ $item->nm_telur }}</strong>
                                </td>
                                <td class="text-end">
                                    {{ number_format($qtyJual, strtolower($item->tipe) === 'pcs' ? 0 : 1, ',', '.') }} {{ $unit }}
                                </td>
                                <td class="text-end text-muted">
                                    Rp {{ number_format($item->rp_satuan, 0, ',', '.') }}
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" 
                                               name="rp_satuan[]" 
                                               value="{{ (int) $item->rp_satuan }}" 
                                               class="form-control form-control-sm text-end input-harga-baru" 
                                               data-qty="{{ $qtyJual }}" 
                                               data-subtotal-lama="{{ $subtotalLama }}"
                                               min="0" 
                                               step="50"
                                               required>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-subtotal-baru">
                                    Rp {{ number_format($subtotalLama, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Total Lama:</th>
                            <th class="text-end" id="display-total-lama">Rp {{ number_format($grandTotalLama, 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Total Baru:</th>
                            <th class="text-end text-primary fw-bold" id="display-total-baru">Rp {{ number_format($grandTotalLama, 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-end">Selisih Penyesuaian:</th>
                            <th class="text-end" id="display-selisih">Rp 0</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        @if(!$isPaid)
            <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i> Simpan Perubahan Harga</button>
        @endif
    </div>
</form>

<script>
    (function() {
        const totalLama = {{ $grandTotalLama ?? 0 }};

        function hitungTotal() {
            let totalBaru = 0;
            $('.input-harga-baru').each(function() {
                const qty = parseFloat($(this).data('qty')) || 0;
                const harga = parseFloat($(this).val()) || 0;
                const subtotal = Math.round(qty * harga);
                totalBaru += subtotal;
                $(this).closest('tr').find('.text-subtotal-baru').text('Rp ' + subtotal.toLocaleString('id-ID'));
            });

            const selisih = totalBaru - totalLama;
            $('#display-total-baru').text('Rp ' + totalBaru.toLocaleString('id-ID'));

            if (selisih < 0) {
                $('#display-selisih').html('<span class="text-success fw-bold"><i class="fas fa-arrow-down me-1"></i>Turun Rp ' + Math.abs(selisih).toLocaleString('id-ID') + '</span>');
            } else if (selisih > 0) {
                $('#display-selisih').html('<span class="text-danger fw-bold"><i class="fas fa-arrow-up me-1"></i>Naik Rp ' + selisih.toLocaleString('id-ID') + '</span>');
            } else {
                $('#display-selisih').text('Rp 0');
            }
        }

        $(document).off('input change', '.input-harga-baru').on('input change', '.input-harga-baru', function() {
            hitungTotal();
        });
    })();
</script>
