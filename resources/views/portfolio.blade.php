<x-layout :js="['resources/js/portfolio.js', 'resources/js/carousel.js']" :css="['resources/css/portfolio.css']">
    <navbar>
        <a href=".aboutme" class="mr-4">About me</a>
        <a href=".skills" class="mr-4">skills</a>
        <a href=".projects" class="mr-4">projects</a>
        <a href=".contact" class="mr-4">contact</a>
    </navbar>
    <h1 class="text-3xl font-bold underline">
        About me
    </h1>
    <div class="aboutme">
        <p class="mt-4">Hi my name is Catherine Szobel </p>
        <p class="">I'm a web developer</p>
        <p class="mt-4">I build websites</p>
    </div>
    <div class="skills">
        <h3 class="text-xl font-bold">My skills</h3>
        <div class="mt-4 space-y-4">
            <div class="flex flex-wrap gap-4 justify-center">
                <div class="text-center p-4 bg-white rounded-lg shadow-lg w-full sm:w-1/2 lg:w-1/4">
                    <h1 class="text-lg font-bold">Frontend</h1>
                    <x-tech-list :items="['HTML','CSS','JavaScript','Tailwind','React.js']" class="justify-center" />
                </div>
                <div class="text-center p-4 bg-white rounded-lg shadow-lg w-full sm:w-1/2 lg:w-1/4">
                    <h1 class="text-lg font-bold">Backend</h1>
                    <x-tech-list :items="['Laravel','PHP','Node.js']" class="justify-center" />
                </div>
                <div class="text-center p-4 bg-white rounded-lg shadow-lg w-full sm:w-1/2 lg:w-1/4">
                    <h1 class="text-lg font-bold">Databases</h1>
                    <x-tech-list :items="['MySQL']" class="justify-center" />
                </div>
                <div class="text-center p-4 bg-white rounded-lg shadow-lg w-full sm:w-1/2 lg:w-1/4">
                    <h1 class="text-lg font-bold">DevOps</h1>
                    <x-tech-list :items="['Git','GitHub','Perforce','Azure']" class="justify-center" />
                </div>
                <div class="text-center p-4 bg-white rounded-lg shadow-lg w-full sm:w-1/2 lg:w-1/4">
                    <h1 class="text-lg font-bold">Tools</h1>
                    <x-tech-list :items="['Vite','npm','VsCode','Visual Studio','Jetbrains']" class="justify-center" />
                </div>
            </div>
        </div>
    </div>
    <div class="projects">
        <h3 class="text-xl font-bold">My projects</h3>
        <div class="mt-4 space-y-4">
            <label for="projects"></label>
        </div>
        <div class="mt-4 space-y-4">
            <div class="grid grid-cols-2">
                <x-project-card
                    title="Deck Trove"
                    subtitle="Personal project"
                    modalId="deck-trove-modal">
                    <p>A website where you can create and share deck collections...</p>

                    <x-tech-list :items="['Laravel','PHP','MySQL','HTML','CSS','JavaScript','Tailwind']" />
                </x-project-card>

                <x-modal id="deck-trove-modal">
                    <h1 class="text-2xl font-bold text-gray-800">Deck Trove</h1>
                    <p class="mt-3 text-gray-600">
                        A website where you can create and share your deck collections from TCG series like
                        Yu-Gi-Oh, Magic: The Gathering, Pokémon, and more.
                    </p>
                    <div class="mt-5">
                        <x-carousel :images="[
                        'resources/img/portfolio/decktrove-1.png',
                        'resources/img/portfolio/decktrove-2.png',
                        'resources/img/portfolio/decktrove-3.png']" />
                    </div>
                    <p class="mt-5 text-gray-700">
                        <span class="font-semibold">Tools used:</span>
                        <x-tech-list :items="['Laravel','PHP','MySQL','HTML','CSS','JavaScript','Tailwind']" />
                    </p>
                </x-modal>

                <x-project-card
                    title="Vending Machine"
                    subtitle="Personal project"
                    modalId="vending-machine-modal">
                    <p>As part of my first React project, I created a vending machine simulator.</p>
                    <x-tech-list :items="['React.js','HTML','CSS','JavaScript','Tailwind']" />
                </x-project-card>

                <x-modal id="vending-machine-modal">
                    <h1 class="text-2xl font-bold text-gray-800">Vending Machine</h1>
                    <p class="mt-3 text-gray-600">
                        A vending machine simulator where users can select products, insert money, and receive change.
                    </p>
                    <div class="mt-5">
                        <x-carousel :images="[
                        'resources/img/portfolio/vendingmachine-light.png',
                        'resources/img/portfolio/vendingmachine-dark.png',
                        'resources/img/portfolio/vendingmachine-dark-filled.png']" />
                    </div>
                    <p class="mt-5 text-gray-700">
                        <span class="font-semibold">Tools used:</span>
                        <x-tech-list :items="['React.js','HTML','CSS','JavaScript','Tailwind']" />
                    </p>
                </x-modal>
            </div>
        </div>
        <div class="contact">
            <h3 class="text-xl font-bold">Contact me</h3>
        </div>
</x-layout>