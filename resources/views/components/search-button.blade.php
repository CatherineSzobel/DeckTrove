@props(['placeholder' => '', 'value' => '', 'hidden' => []])
<form method="GET" class="w-full sm:w-1/2 min-w-[200px]" role="search">
    @foreach ($hidden as $name => $hiddenValue)
    <input type="hidden" name="{{ $name }}" value="{{ $hiddenValue }}">
    @endforeach
    <div class="flex">
        <input type="search" name="search" value="{{ $value }}" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}"
            class="flex-1 p-3 text-sm text-gray-900 border border-gray-300 rounded-l-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" />
        <button type="submit"
            class="bg-blue-700 text-white px-4 py-2 rounded-r-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
            {{ $slot }}
        </button>
    </div>
</form>
