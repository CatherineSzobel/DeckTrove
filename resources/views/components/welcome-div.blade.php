 @props([ 'heading' => '' ,
 'images' => [
     'resources/img/decktrove-logo.png',
     'resources/img/screenshots/card-database.png',
     'resources/img/screenshots/deck-builder.png',
     'resources/img/yugiohcards.png',
     'resources/img/magiccards.png',
 ]])

 <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg text-center">
     <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900">{{$heading}}</h2>
     <p class="mt-4 text-gray-600">Access exclusive content, organize your deck collections, and connect with the community.</p>
     <ul class="mt-6 text-left space-y-2 list-disc list-inside text-gray-700">
         <li>Track your decks easily</li>
         <li>Join community challenges</li>
         <li>Exclusive cards and updates</li>
     </ul>
    <x-carousel :images="$images"></x-carousel>
 </div>