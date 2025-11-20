 @props(['title'])
 <div class="bg-gray-400 p-4 rounded-lg shadow mb-4 text-gray-800">
     <h1 class="text-2xl font-bold mb-4">{{$title}}</h1>

     {{ $slot }}
 </div>