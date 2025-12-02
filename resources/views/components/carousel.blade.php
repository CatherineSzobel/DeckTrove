 @props([ 'images' => [] ])
 <div class="relative w-full max-w-lg mx-auto mt-8">
     <!-- Carousel wrapper -->
     <div class="overflow-hidden relative rounded-lg shadow-lg">
         <!-- Slides container -->
         <div id="carousel-slides" class="flex transition-transform duration-500">
             @foreach ( $images as $image )
             <div class="min-w-full flex-shrink-0 bg-gray-100 flex items-center justify-center h-48">
                 <img
                     alt="Decktrove Logo"
                     class="w-40 sm:w-48 md:w-64 lg:w-72 h-auto"
                     src="{{ Vite::asset($image) }}">
             </div>

             @endforeach
         </div>

         <!-- Prev button -->
         <button id="prev" class="absolute top-1/2 left-2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow hover:bg-gray-100">
             &#10094;
         </button>

         <!-- Next button -->
         <button id="next" class="absolute top-1/2 right-2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow hover:bg-gray-100">
             &#10095;
         </button>
     </div>

     <!-- Pagination dots -->
     <div id="carousel-dots" class="flex justify-center mt-4 space-x-2">
         <button class="w-3 h-3 rounded-full bg-gray-400"></button>
         <button class="w-3 h-3 rounded-full bg-gray-400"></button>
         <button class="w-3 h-3 rounded-full bg-gray-400"></button>
     </div>
 </div>