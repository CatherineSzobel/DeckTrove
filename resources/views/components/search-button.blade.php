@props([ 'placeholder' => '' ])
<form id="search-form" class="w-1/2 min-w-[200px]">
    <div class="flex">
        <x-search-button-input placeholder="{{ $placeholder }}" id="search-input"></x-search-button-input>
        
        <button type="submit"
            class="bg-blue-700 text-white px-4 py-2 rounded-r-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
            {{ $slot }}
        </button>
    </div>
</form>