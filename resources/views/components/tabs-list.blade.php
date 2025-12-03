@props(["labels" => [], "targets" => []])
<div class="relative flex flex-wrap gap-2 bg-white rounded-lg shadow-lg p-2 w-1/2 mx-auto justify-center">
    @foreach ($labels as $index => $label)
    @if (empty($targets[$index]))
    <a class="tab-link px-4 py-2 rounded-md cursor-pointer bg-slate-100 hover:bg-slate-200">{{ $label }}</a>
    @else
    <a class="tab-link px-4 py-2 rounded-md cursor-pointer bg-slate-100 hover:bg-slate-200" data-tab-target="{{ $targets[$index] }}">{{ $label }}</a>
    @endif
    @endforeach
</div>