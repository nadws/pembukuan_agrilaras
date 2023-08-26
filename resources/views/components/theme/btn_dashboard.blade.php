@props([
    'route' => '',
    'title' => '',
])
<a data-bs-toggle="tooltip" data-bs-placement="top" title="{{$title ?? 'dashboard'}}" href="{{ route($route) }}"
    class="btn btn-sm icon icon-left btn-primary me-2 float-end"><i class="fas fa-home"></i></a>
