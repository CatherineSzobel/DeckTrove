<x-layout class="bg-gradient-to-b from-gray-100 to-gray-400" :hideNav="true">
    <div class=" px-6">
        <div class="text-center mb-4">
            <h1 class="text-6xl font-extrabold text-gray-900 mb-6 drop-shadow-lg"> Welcome to DeckTrove </h1>
            <p class="text-lg text-gray-700 max-w-3xl mx-auto"> Explore your favorite trading card games, build unique decks, and share them with the global community. Stay ahead with the latest series and exclusive updates! </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto mt-6 mb-6">
            <x-action-component 
            title=" Browse Your Favorite Cards" 
            desc=" Explore an extensive card database, learn detailed card info, and stay up to date with the newest and upcoming sets."
            color="bg-gradient-to-r from-indigo-500 to-purple-600"/>

            <x-action-component 
            title="Browse Your Favorite Cards" 
            desc=" Explore an extensive card database, learn detailed card info, and stay up to date with the newest and upcoming sets."
            color="bg-gradient-to-r from-emerald-500 to-teal-600 "/>

            <x-action-component 
            title="Discover New TCGs" 
            desc="Maybe you want to get into a new TCG? Explore new series, and expand your collection beyond your comfort zone."
            color="bg-gradient-to-r from-amber-500 to-orange-600"/>

        </div>

        <div id="sectionHeading" class="text-center mb-4">
            <h2 class="text-4xl font-extrabold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                Explore Trading Card Games
            </h2>
            <p class="text-gray-600 mt-2">
                Choose a game and start building your deck
            </p>
        </div>

        <div
            class="relative mt-6 px-6 py-16 rounded-[3rem] 
            bg-gradient-to-b from-gray-100 to-gray-400  
            shadow-inner">

            <div class="flex justify-center items-center gap-6 max-w-6xl mx-auto flex-wrap">
                @foreach ($tcgs as $tcg)
                <div
                    class="relative flex-1 h-96 min-w-[240px] rounded-[1.35rem] p-[3px]
                    bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500
                    shadow-lg hover:shadow-[0_0_35px_rgba(168,85,247,0.35)]
                    transition-all duration-500 hover:flex-[2] group">

                    <div class="relative h-full rounded-[1.35rem] overflow-hidden bg-black flex items-center justify-center">

                        @if (!$tcg['comingSoon'])
                        <img id="logoImage"
                            src="{{ Vite::asset('resources/img/' . $tcg['src']) }}"
                            alt="{{ $tcg['series'] }} Logo"
                            data-series="{{ $tcg['link'] }}"
                            class="w-4/5 h-4/5 object-contain block drop-shadow-[0_0_8px_rgba(255,255,255,1)] 
                            cursor-pointer transition-transform duration-500 group-hover:scale-105 rounded-[1.35rem] dashboard-logo cursor-pointer" />
                        @else
                        <img id="logoImage"
                            src="{{ Vite::asset('resources/img/' . $tcg['src']) }}"
                            alt="{{ $tcg['series'] }} Logo"
                            class="w-4/5 h-4/5 object-contain block drop-shadow-[0_0_8px_rgba(255,255,255,0.6)] 
                            transition-transform duration-500 group-hover:scale-105 rounded-[1.35rem]" />
                        @endif

                        @if ($tcg['comingSoon'])
                        <div id="comingSoonOverlay"
                            class="absolute inset-0 flex items-center justify-center
                            bg-gradient-to-t from-black/40 to-black/10 text-white text-xl font-bold
                            opacity-0 group-hover:opacity-100 transition-opacity duration-300
                            pointer-events-none rounded-3xl">
                            Coming Soon
                        </div>
                        @endif

                        <div id="descriptionOverlay"
                            class="absolute top-0 left-0 right-0 
                            bg-gradient-to-r from-indigo-600/80 to-purple-600/80
                            text-white text-center font-semibold text-sm
                            py-2 px-4 opacity-0 group-hover:opacity-100
                            transition-opacity duration-300 rounded-t-[1.35rem] pointer-events-none">
                            {{ $tcg['description'] }}
                        </div>

                        <div id="seriesLabel"
                            class="absolute bottom-0 left-0 right-0 
                            bg-gradient-to-t from-black/90 via-black/60 to-transparent
                            text-white text-center font-semibold py-2 tracking-wide">
                            {{ $tcg['series'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layout>