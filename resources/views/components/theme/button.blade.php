@props([
    'size' => 'sm',
    'variant' => 'primary',
    'teks' => 'primary',
    'addClass' => '',
    'id' => '',
    'href' => '#',
    'icon' => '',
    'modal' => 'T',
    'idModal' => '',
    'hapus' => 'T',
    'data' => '',
])

<a href="{{ $href }}" {{ $data }}
    @if ($hapus == 'Y') onclick="return confirm('Yakin ingin dihapus ?')" @endif id="{{ $id }}"
    @if ($modal == 'Y') data-bs-toggle="modal" data-bs-target="#{{ $idModal }}" @endif
    class="{{ $addClass }} btn btn-{{ $size }} icon icon-left btn-{{ $variant }} me-2">
    <i class="fas {{ $icon }}"></i> {{ $teks }}
</a>
