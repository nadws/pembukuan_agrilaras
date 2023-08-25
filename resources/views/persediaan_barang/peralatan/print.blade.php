


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

    <link rel="stylesheet" href="{{ asset('theme') }}/assets/css/main/app.css">
    <link rel="stylesheet" href="{{ asset('theme') }}/assets/css/pages/fontawesome.css">
    <style>
        table {
            font-size: 11px;
        }

        .dhead {
            background-color: #435EBE !important;
            color: white;
        }

        .dborder {
            border-color: #435EBE
        }
    </style>
</head>

<body>
    <div class="py-5 px-5 container">
        <div class="row tbl1">
            <div class="col-lg-12">
                <table width="100%" cellpadding="10px">
                    <tr>
                        <th style="background-color: white;" width="10%">Tanggal</th>
                        <th style="background-color: white;" width="2%">:</th>
                        <th style="background-color: white;">{{date('d-m-Y',strtotime($head_jurnal->tgl))}}</th>
                        <th style="background-color: white;" width="15%">No Nota</th>
                        <th style="background-color: white;" width="2%">:</th>
                        <th style="background-color: white;">{{$head_jurnal->no_nota}}</th>
                    </tr>
                    <tr>
                        <th style="background-color: white; " width="10%">Proyek</th>
                        <th style="background-color: white; " width="2%">:</th>
                        <th style="background-color: white; ">{{$head_jurnal->nm_proyek}}</th>
                        <th style="background-color: white; " width="10%">Suplier</th>
                        <th style="background-color: white; " width="2%">:</th>
                        <th style="background-color: white; ">{{$head_jurnal->nm_suplier ?? ''}}</th>
                    </tr>
                </table>
            </div>
            
        </div>
      
        <section class="row tbl2">
            <div class="col-lg-12">
                <h6 class="text-center">Print {{ $title }}</h6>
                <hr style="border: 1px solid black">
            </div>
            <div class="col-lg-12">
                <div class="col-lg-12">
                    <table class="table table-hover table-bordered dborder">
                        <thead>
                            <tr>
                                <th class="dhead" width="5">#</th>
                                <th class="dhead">Akun</th>
                                <th class="dhead">Keterangan</th>
                                <th class="dhead" style="text-align: right">Debit</th>
                                <th class="dhead" style="text-align: right">Kredit</th>
                                <th class="dhead" >Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jurnal as $no => $a)
                            <tr>
                                <td>{{$no + 1}}</td>
                                <td>{{$a->akun->nm_akun}}</td>
                                <td>{{$a->ket}}</td>
                                <td align="right">{{number_format($a->debit,0)}}</td>
                                <td align="right">{{number_format($a->kredit,0)}}</td>
                                <td>{{$a->admin}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
            </div>
        </section>

    </div>
<script>
    window.print()
</script>
</body>

</html>
