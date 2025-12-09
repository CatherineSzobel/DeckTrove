@props(["labels" => [], "targets" => [], 
"tabClass" => "relative flex flex-wrap gap-2 bg-white rounded-lg shadow-lg p-2 w-1/2 mx-auto justify-center", 
"buttonClass" => "tab-link px-4 py-2 rounded-md cursor-pointer bg-slate-100 hover:bg-slate-200"])
<div  {{ $attributes->merge(['class' => $tabClass]) }} >
    @foreach ($labels as $index => $label)
    @if (empty($targets[$index]))
    <a  {{ $attributes->merge(['class' => $buttonClass]) }}>{{ $label }}</a>
    @else
    <a {{ $attributes->merge(['class' => $buttonClass]) }} data-tab-target="{{ $targets[$index] }}">{{ $label }}</a>
    @endif
    @endforeach
</div>