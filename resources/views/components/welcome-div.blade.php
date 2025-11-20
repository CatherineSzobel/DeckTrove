 @props([ 'heading' => '' ])
 <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg text-center">
     <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">{{$heading}}</h2>
     <p class="mt-4 text-gray-600">Access exclusive content, organize your deck collections, and connect with the community.</p>
     <ul class="mt-6 text-left space-y-2 list-disc list-inside text-gray-700">
         <li>Track your decks easily</li>
         <li>Join community challenges</li>
         <li>Exclusive cards and updates</li>
     </ul>
     <div class="relative w-full max-w-lg mx-auto mt-8">
         <!-- Carousel wrapper -->
         <div class="overflow-hidden relative rounded-lg shadow-lg">
             <!-- Slides container -->
             <div id="carousel-slides" class="flex transition-transform duration-500">
                 <div class="min-w-full flex-shrink-0 bg-gray-100 flex items-center justify-center h-48">
                     <img src={{ Vite::asset('resources/img/decktrove-logo.png') }}
                         alt="Decktrove Logo"
                         class="w-40 sm:w-48 md:w-64 lg:w-72 h-auto">
                 </div>
                 <div class="min-w-full flex-shrink-0 bg-gray-200 flex items-center justify-center h-48">
                     <img src={{ Vite::asset('resources/img/yugiohcards.png') }}
                         alt="Yu-Gi-Oh! cards"
                         class="w-40 sm:w-48 md:w-64 lg:w-72 h-auto">
                 </div>
                 <div class="min-w-full flex-shrink-0 bg-gray-300 flex items-center justify-center h-48">
                     <img src={{ Vite::asset('resources/img/magiccards.png') }}
                         alt="Magic cards"
                         class="w-40 sm:w-48 md:w-64 lg:w-72 h-auto">
                 </div>
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
 </div>