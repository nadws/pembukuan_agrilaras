<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('planning-form');
    const products = @json($products->keyBy('id_produk'));
    let stock = @json($stock), sequence = 1000, generation = 0, blocked = false;
    const mode = @json($mode);
    const date = document.getElementById('planning-date'), kandang = document.getElementById('planning-kandang');
    const population = document.getElementById('planning-population'), message = document.getElementById('planning-context-message');
    const number = id => Number(document.getElementById(id).value) || 0;
    function initializeSelect2(root) {
        $(root).find('.planning-select2').each(function () {
            if (mode === 'detail') $(this).prop('disabled', true);
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({width: '100%'});
            }
        });
    }
    function recalculate() {
        let total = 0;
        form.querySelectorAll('.planning-row').forEach(row => {
            const product = products[row.querySelector('.planning-product').value] || {};
            const fill = (selector, value) => { const input = row.querySelector(selector); if (input) input.value = value; };
            fill('.planning-unit', product.satuan || ''); fill('.planning-mix-unit', product.satuan_campuran || '');
            fill('.planning-stock', stock[product.id_produk] || 0);
            if (row.dataset.category === 'pakan') {
                const grams = mode === 'detail' ? Number(row.querySelector('.planning-grams').value) || 0
                    : number('planning-population') * number('planning-per-bird') * (Number(row.querySelector('.planning-percent').value) || 0) / 100;
                fill('.planning-grams', Math.round(grams * 1000000) / 1000000); total += grams;
            }
            if (mode !== 'detail') {
                const selected = !!product.id_produk;
                row.querySelectorAll('.planning-dose,.planning-mix').forEach(input => { input.required = selected; });
            }
        });
        document.getElementById('planning-total').value = Math.round(total * 1000000) / 1000000;
        const ratio = number('planning-box') > 0 ? total / (number('planning-box') * 1000) : 0;
        document.getElementById('planning-full-bag').value = Math.floor(ratio);
        document.getElementById('planning-bag-rest').value = ((ratio - Math.floor(ratio)) * 10).toFixed(2);
    }
    form.addEventListener('input', recalculate);
    form.addEventListener('change', recalculate);
    $(form).on('change.planning', '.planning-select2', recalculate);
    form.querySelectorAll('.planning-add').forEach(button => button.addEventListener('click', () => {
        const category = button.dataset.category;
        const html = document.getElementById('template-' + category).innerHTML.replaceAll('__INDEX__', sequence++);
        const container = document.getElementById('planning-' + category);
        container.insertAdjacentHTML('beforeend', html);
        initializeSelect2(container); recalculate();
    }));
    form.addEventListener('click', event => {
        if (event.target.closest('.planning-remove')) {
            const row = event.target.closest('.planning-row');
            $(row).find('.select2-hidden-accessible').select2('destroy');
            row.remove(); recalculate();
        }
    });
    async function context() {
        if (mode !== 'create') return;
        const current = ++generation; blocked = true;
        const save = document.getElementById('planning-save'); save.disabled = true; population.value = 0; recalculate();
        message.replaceChildren(); message.classList.add('d-none');
        if (!date.value || !kandang.value) return;
        try {
            const params = new URLSearchParams({tgl: date.value, id_kandang: kandang.value});
            const response = await fetch(@json(route('history_perencanaan_pakan.context')) + '?' + params, {headers: {'Accept':'application/json'}});
            if (!response.ok) throw new Error('Gagal membaca populasi. Pilih tanggal/kandang kembali.');
            const data = await response.json(); if (current !== generation) return;
            population.value = data.populasi; stock = data.stok;
            blocked = data.exists;
            if (blocked) {
                message.classList.remove('d-none');
                message.append('Tanggal/kandang sudah ada. ');
                const link = document.createElement('a'); link.href = data.edit_url; link.textContent = 'Buka Koreksi'; message.append(link);
            }
            save.disabled = blocked; recalculate();
        } catch(error) {
            if (current !== generation) return;
            message.textContent = error.message; message.classList.remove('d-none');
        }
    }
    date.addEventListener('change', context); $(kandang).on('change.planningContext', context);
    form.addEventListener('submit', event => {
        if (mode === 'detail' || blocked) { event.preventDefault(); return; }
        document.getElementById('planning-save').disabled = true;
    });
    initializeSelect2(form); recalculate(); context();
});
</script>
