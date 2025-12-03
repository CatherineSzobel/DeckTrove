@props(['title', 'subtitle', 'modalId', 'images' => [], 'tools' => [], 'description' => '', 'modalDescription' => ''])
<x-portfolio-project-card
    :title="$title"
    :subtitle="$subtitle"
    :modalId="$modalId">
    <p>{{ $description }}</p>

    <x-portfolio-tech-list :items="$tools" />
</x-portfolio-project-card>

<x-modal :id="$modalId">
    <h1 class="text-2xl font-bold text-gray-800">{{ $title }}</h1>
    <p class="mt-3 text-gray-600">
        {{ $modalDescription }}
    </p>
    <div class="mt-5">
        <x-carousel :images="$images" />
    </div>
    {{$slot}}
    <p class="mt-5 text-gray-700">
        <span class="font-semibold">Tools used:</span>
        <x-portfolio-tech-list :items="$tools" />
    </p>
</x-modal>