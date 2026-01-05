@props(['title' => "",'desc' => "", 'color' => ""])
<div class="bg-white text-center p-6 rounded-3xl shadow-lg hover:shadow-2xl transition-shadow">
    <p class="inline-block font-extrabold text-lg mb-3 px-4 py-1 rounded-full {{$color}} text-white shadow-md"> {{ $title }} </p>
    <p class="text-gray-600"> {{ $desc }} </p>
</div>