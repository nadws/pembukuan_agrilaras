<table x-show="!open" class="d-none table table-striped table-bordered" id="tablealdi2" x-data="{}">
    <thead>
        <tr>
            <th>TOTAL</th>
            <th class="text-end">{{ number_format(100000000, 0) }}</th>
            <th>&nbsp;</th>
            <th class="text-end">{{ number_format(100000000, 0) }}</th>
            <th>&nbsp;</th>
            <th class="text-end">{{ number_format(100000000, 0) }}</th>
            <th>&nbsp;</th>
            <th class="text-end">{{ number_format(100000000, 0) }}</th>
            <th>&nbsp;</th>
        </tr>
        <tr>
            <th>Uraian</th>
            <th class="text-center">Budget'19</th>
            <th>%</th>
            <th class="text-center">Budget'22</th>
            <th>%</th>
            <th class="text-center">Actual'19</th>
            <th>%</th>
            <th class="text-center">Actual'22</th>
            <th>%</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($biaya as $i => $d)
            <tr>
                <td>{{ ucwords(strtolower($d->nm_akun)) }}</td>
                <td>
                    <input value="5000000" type="text" x-mask:dynamic="$money($input)" class="form-control text-end"
                        name="budget[]">
                </td>
                <td class="">0.38</td>
                <td>
                    <input value="5000000" type="text" x-mask:dynamic="$money($input)" class="form-control text-end"
                        name="budget[]">
                </td>
                <td class="">0.38</td>
                <td>
                    <input value="5000000" type="text" x-mask:dynamic="$money($input)" class="form-control text-end"
                        name="budget[]">
                </td>
                <td class="">0.38</td>
                <td>
                    <input value="5000000" type="text" x-mask:dynamic="$money($input)" class="form-control text-end"
                        name="budget[]">
                </td>
                <td class="">0.38</td>
            </tr>
        @endforeach
    </tbody>
</table>
