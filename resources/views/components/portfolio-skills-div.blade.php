 @props(["title" => "", "description" => ""])
 <div class="text-center p-4 bg-white rounded-lg shadow-lg w-full sm:w-1/2 lg:w-1/4 duration-300 hover:scale-105">
     <h1 class="text-lg font-bold">{{ $title }}</h1>
     <p class="text-sm text-gray-500">{{ $description }}</p>
    {{ $slot }}
 </div>