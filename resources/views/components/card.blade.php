 
 @props([ 'desc' => 'No description available'])
 <div class="relative group">
     <div class="relative group bg-white rounded-lg shadow hover:shadow-lg p-2 flex justify-center transition transform hover:-translate-y-1">
            {{ $slot }}
     </div>
     <!-- Description Tooltip -->
     <x-card-description-tooltip :desc="$desc" />
 </div>