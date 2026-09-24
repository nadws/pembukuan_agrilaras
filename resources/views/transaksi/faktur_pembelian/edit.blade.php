        @php
            $jenisFakturTerpilih = old('jenis_faktur', $faktur->jenis_faktur);
            $itemsLama = collect(old('item'))->values();
            if ($itemsLama->isEmpty()) {
                $diskonLama = (float) ($faktur->diskon_total ?? 0);
                $totalBersihLama = (float) $faktur->detail->sum('subtotal');
                $sisaDiskonLama = $diskonLama;
                $jumlahItemLama = $faktur->detail->count();

                $itemsLama = $faktur->detail->values()->map(
                    function ($detail, $index) use ($diskonLama, $totalBersihLama, &$sisaDiskonLama, $jumlahItemLama, $akunPembayaranDefaultId) {
                        $diskonItem = $index === $jumlahItemLama - 1
                            ? $sisaDiskonLama
                            : ($totalBersihLama > 0 ? round((float) $detail->subtotal / $totalBersihLama * $diskonLama, 2) : 0);
                        $sisaDiskonLama = round($sisaDiskonLama - $diskonItem, 2);
                        $subtotalSebelumDiskon = round((float) $detail->subtotal + $diskonItem, 2);

                        return [
                        'pakan_id' => $detail->pakan_id,
                        'sumber_produk' => $detail->sumber_produk,
                        'qty' => $detail->qty,
                        'satuan' => $detail->satuan,
                        'harga_satuan' => $detail->qty > 0 ? round($subtotalSebelumDiskon / (float) $detail->qty, 6) : 0,
                        'subtotal' => $subtotalSebelumDiskon,
                        'id_akun_pembayaran' => $detail->id_akun_pembayaran ?? $akunPembayaranDefaultId,
                        ];
                    },
                );
            }
        @endphp

@include('transaksi.faktur_pembelian.form')
