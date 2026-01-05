@props(['images' => []])

<div class="carousel-container relative w-full max-w-5xl mx-auto mt-8">
    <div class="overflow-hidden relative rounded-lg shadow-lg max-h-[70vh]">
        <div class="carousel-slides flex transition-transform duration-500 gap-0">
            @foreach ($images as $image)
            <div class="w-full flex-shrink-0 bg-gray-100 flex items-center justify-center">
                <img
                    src="{{ Vite::asset($image) }}"
                    alt="Carousel image"
                    class="w-full h-auto max-h-[70vh] object-contain">
            </div>
            @endforeach
        </div>

        <button class="carousel-prev absolute top-1/2 left-2 transform -translate-y-1/2 bg-white rounded-full p-3 shadow hover:bg-gray-100 z-10">
            &#10094;
        </button>

        <button class="carousel-next absolute top-1/2 right-2 transform -translate-y-1/2 bg-white rounded-full p-3 shadow hover:bg-gray-100 z-10">
            &#10095;
        </button>
    </div>

    <div class="carousel-dots flex justify-center mt-4 space-x-2">
        @foreach ($images as $image)
        <button class="w-3 h-3 rounded-full bg-gray-400"></button>
        @endforeach
    </div>
</div>