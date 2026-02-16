<x-theme.app title="{{ $title }}" table="Y" sizeCard="10">
    <x-slot name="cardHeader">
        <h5 class="float-start mt-1">{{ $title }}</h5>
        <div class="row ">
            <div class="col-lg-12">
                <a href="#" data-bs-toggle="modal" data-bs-target="#importhpp"
                    class="float-end btn btn-success me-2"><i class="fas fa-download"></i> Import hpp</a>
            </div>
        </div>


    </x-slot>
    <x-slot name="cardBody">
        <section class="row">
            <table class="table table-hover table-bordered" id="table">
                <thead>
                    <tr>
                        <th width="5">#</th>
                        <th class="text-center">Kandang</th>
                        <th class="text-end">Chick In</th>
                        <th class="text-end">Afkir</th>
                        <th class="text-end">Chick In2</th>
                        <th>Strain</th>
                        <th class="text-end">Pop Awal</th>
                        <th class="text-end">Rupiah</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($kandang as $no => $a)
                        @php
                            $tgl = date('Y-m-d', strtotime('-3 months', strtotime($a->tgl_masuk)));
                        @endphp
                        <tr>
                            <td>{{ $no + 1 }}</td>
                            <td class="text-center">{{ ucwords($a->nm_kandang) }}</td>
                            <td align="right">{{ tanggal($a->chick_in) }}</td>
                            <td align="right">{{ tanggal($a->chick_out) }}</td>
                            <td align="right" class="{{ date('Y-m-d') >= $tgl ? 'text-danger' : '' }}">
                                {{ tanggal($a->tgl_masuk) }} </td>
                            <td>{{ ucwords($a->nm_strain) }}</td>
                            <td class="text-end">
                                {{ $a->stok_awal }}
                            </td>
                            <td class="text-end">
                                Rp {{ number_format($a->rupiah, 0) }}
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm laba-rugi" data-bs-toggle="modal"
                                    data-bs-target="#laba-rugi" id_kandang="{{ $a->id_kandang }}">Detail</button>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
        <style>
            .modal-lg-max {
                max-width: 90%;

            }
        </style>
        <div class="modal fade" id="laba-rugi" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg-max">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Laba dan Rugi</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="laba-rugi_kandang"></div>
                    </div>
                </div>
            </div>
        </div>






    </x-slot>
    @section('scripts')
        <script>
            $(document).on('click', '.laba-rugi', function() {
                var id_kandang = $(this).attr('id_kandang');
                $.ajax({
                    type: "get",
                    url: "{{ route('labaRugiKandang') }}",
                    data: {
                        id_kandang: id_kandang,
                    },
                    success: function(r) {
                        $('#laba-rugi_kandang').html(r)
                    }
                });
            });
            $(document).on('click', '#myTab a', function(e) {

                e.preventDefault()
                $(this).tab('show')
            });
        </script>
    @endsection

</x-theme.app>
