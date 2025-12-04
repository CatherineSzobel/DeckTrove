 @props(['name' => '', 'contents' => [], 'urlPrefix' => '/yugioh/card/', 'cardId' => null])
 <tr class="border-b hover:bg-gray-50 transition">
     <td class="px-3 py-2 text-sm text-gray-800 underline hover:decoration-blue-500 hover:text-blue-600">
         <a href="{{ url($urlPrefix . $cardId) }}">
             {{ $name ?? 'Unknown' }}
         </a>
     </td>
     @foreach ( $contents as $content )
     <td class="px-3 py-2 text-sm text-gray-800">
         {{ $content ?? 'Unknown' }}
     </td>
     @endforeach
 </tr>