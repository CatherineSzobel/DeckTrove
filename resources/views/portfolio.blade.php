<x-layout :js="['resources/js/portfolio.js', 'resources/js/carousel.js']" :css="['resources/css/portfolio.scss']">

    <!-- About Me -->
    <div class="aboutme grid grid-cols-1 md:grid-cols-2 gap-4 items-center mx-auto mt-8">
        <div class="profile-picture w-48 h-48 mx-auto md:mx-0">
            <img src="{{ Vite::asset('resources/img/portfolio/portfolio-picture.jpg') }}" alt="Profile Picture" class="rounded-full w-full h-full object-cover shadow-lg">
            <button id="download-cv-btn" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md w-full">
                <i class="fa fa-download"></i> Download Resume
            </button>
        </div>
        <div>
            <h1 class="text-3xl">Catherine Szobel</h1>
            <div class="animated-text">
                I'm a <span></span>
            </div>
            <p class="mt-4">I am an ambitious Full Stack Developer with a strong passion for continuous learning and growth. I thrive in dynamic environments where I can challenge myself, adapt, and refine my skills. Open to feedback and new perspectives, I strive to turn every experience into an opportunity for improvement.
                With hands-on experience in both front-end and back-end technologies, I merge technical expertise with a problem-solving mindset to build scalable, efficient, and user-friendly solutions. My goal is to contribute to innovative projects while continuously evolving as a developer and making a meaningful impact through technology.</p>
        </div>
    </div>

    <!-- Skills Section -->
    <div class="skills mt-8">
        <h3 class="text-xl font-bold border-b border-gray-300 border-b-8 p-2 w-1/5">My Skills</h3>

        <!-- Tabs for Skills -->
        <div class="tab-container mt-4 ">
            <x-tabs-list :labels="['Full stack developer', 'Game developer', 'Miscellaneous']" :targets="['fullstackSkills', 'gamedevSkills', 'miscSkills']" />
            <div class="tab-content mt-4">

                <!-- Full Stack Skills -->
                <div id="fullstackSkills" class="tab-panel flex flex-wrap gap-4 justify-center">

                    <x-portfolio-skills-div title="Frontend" description="I craft responsive, accessible, and user-focused interfaces.">
                        <x-portfolio-tech-list :items="['HTML','CSS','JavaScript','Tailwind','React.js']" class="justify-center" />
                    </x-portfolio-skills-div>

                    <x-portfolio-skills-div title="Backend" description="I build secure and scalable backend systems and RESTful APIs.">
                        <x-portfolio-tech-list :items="['Laravel','PHP','Node.js']" class="justify-center" />
                    </x-portfolio-skills-div>

                    <x-portfolio-skills-div title="Databases" description="I design and maintain efficient relational database structures.">
                        <x-portfolio-tech-list :items="['MySQL']" class="justify-center" />
                    </x-portfolio-skills-div>

                    <x-portfolio-skills-div title="DevOps" description="I streamline development workflows using modern DevOps tools.">
                        <x-portfolio-tech-list :items="['Git','GitHub','Perforce','Azure']" class="justify-center" />
                    </x-portfolio-skills-div>

                    <x-portfolio-skills-div title="Tools" description="I work with a range of tools that enhance development efficiency.">
                        <x-portfolio-tech-list :items="['Vite','npm','VsCode']" class="justify-center" />
                    </x-portfolio-skills-div>

                </div>
            </div>

            <!-- Game Developer Skills -->
            <div id="gamedevSkills" class="tab-panel hidden flex flex-wrap gap-4 justify-center">
                <x-portfolio-skills-div title="Engine" description="Experience with leading game engines for immersive experiences.">
                    <x-portfolio-tech-list :items="['Unreal Engine','Unity']" class="justify-center" />
                </x-portfolio-skills-div>

                <x-portfolio-skills-div title="Language" description="Proficient in programming languages for game development.">
                    <x-portfolio-tech-list :items="['C++','C#', 'Python (very basic)']" class="justify-center" />
                </x-portfolio-skills-div>

                <x-portfolio-skills-div title="DevOps" description="Streamlined development workflows using modern DevOps tools.">
                    <x-portfolio-tech-list :items="['Git','GitHub','Perforce','Azure']" class="justify-center" />
                </x-portfolio-skills-div>
                <x-portfolio-skills-div title="Tools" description="Utilized various tools to enhance game development efficiency.">
                    <x-portfolio-tech-list :items="['Visual Studio','Jetbrains']" class="justify-center" />
                </x-portfolio-skills-div>
            </div>

            <!-- Miscellaneous Skills -->
            <div id="miscSkills" class="tab-panel hidden flex flex-wrap gap-4 justify-center">
                <x-portfolio-skills-div title="Soft Skills" description="Key interpersonal skills that enhance teamwork and productivity.">
                    <x-portfolio-tech-list :items="['Eager to learn','Teamwork','Problem-solving','Adaptability','Time management']" class="justify-center" />
                </x-portfolio-skills-div>

                <x-portfolio-skills-div title="Languages" description="Languages I am proficient in.">
                    <x-portfolio-tech-list :items="['Dutch (Native)', 'English (Fluent)', 'French (Basic)']" class="justify-center" />
                </x-portfolio-skills-div>

                <x-portfolio-skills-div title="Project management tools" description="Tools I use for effective project management.">
                    <x-portfolio-tech-list :items="['HacknPlan','Trello','Miro']" class="justify-center" />
                </x-portfolio-skills-div>
            </div>
        </div>
    </div>

    <!-- Projects Section -->
    <div class="projects mt-8">
        <h3 class="text-xl font-bold border-b border-gray-300 border-b-8 p-2 w-1/5">My Projects</h3>

        <!-- Tabs for Projects -->
        <div class="tab-container mt-4">
            <x-tabs-list :labels="['Full Stack Projects', 'Game Dev Projects']" :targets="['fullstackProjects', 'gamedevProjects']" />

            <div class="tab-content mt-4">
                <!-- Full Stack Projects -->
                <div id="fullstackProjects" class="tab-panel flex flex-wrap gap-4 justify-center">
                    <x-portfolio-piece
                        title="Deck Trove"
                        subtitle="Personal project"
                        modalId="deck-trove-modal"
                        :images="[
                        'resources/img/portfolio/decktrove-1.png',
                        'resources/img/portfolio/decktrove-2.png',
                        'resources/img/portfolio/decktrove-3.png']"
                        :tools="['Laravel','PHP','MySQL','HTML','CSS','JavaScript','Tailwind']"
                        description="A website where you can create and share deck collections..."
                        modalDescription="A website where you can create and share your deck collections from TCG series like Yu-Gi-Oh, Magic: The Gathering, Pokémon, and more." />

                    <x-portfolio-piece
                        title="Vending Machine"
                        subtitle="Personal project"
                        modalId="vending-machine-modal"
                        :images="[
                        'resources/img/portfolio/vendingmachine-light.png',
                        'resources/img/portfolio/vendingmachine-dark.png',
                        'resources/img/portfolio/vendingmachine-dark-filled.png']"
                        :tools="['React.js','HTML','CSS','JavaScript','Tailwind']"
                        description="A vending machine simulator where users can select products, insert money, and receive change."
                        modalDescription="A vending machine simulator where users can select products, insert money, and receive change.">
                        <div class="mt-4">
                            <h2 class="text-xl font-bold mb-2">Problem:</h2>
                            <p class="text-gray-700">As part of my first ever React project, this was a learning experience to me. </p>
                            <p class="text-gray-700">I wanted to create a simple yet interactive application that simulates the functionality of a vending machine.
                                The goal was to provide users with an engaging experience while also honing my React skills.</p>
                        </div>
                        <div class="mt-4">
                            <h2 class="text-xl font-bold mb-2">Features:</h2>
                            <ul class="list-disc list-inside text-gray-700">
                                <li>Product Selection: Choose from a variety of snacks and drinks.</li>
                                <li>Money Insertion: Simulate inserting coins and bills.</li>
                                <li>Change Calculation: Automatically calculates and dispenses change.</li>
                                <li>User Interface: Intuitive and responsive design for easy navigation.</li>
                            </ul>
                        </div>
                    </x-portfolio-piece>
                </div>

                <!-- Game Dev Projects -->
                <div id="gamedevProjects" class="tab-panel hidden flex flex-wrap gap-4 justify-center">
                    <x-portfolio-piece
                        title="DreamBots"
                        subtitle="Team project"
                        modalId="dreambots-modal"
                        :images="[
                        'resources/img/portfolio/dreambots.png',
                        'resources/img/portfolio/ridingchicken.png',
                        'resources/img/portfolio/shootingdrone.png',
                        'resources/img/portfolio/dronesgif.gif',
                        'resources/img/portfolio/wasps.png',
                        'resources/img/portfolio/gallery.gif']"
                        :tools="['Unreal Engine','C++','Git','Perforce', 'Visual Studio','HacknPlan']"
                        description="A 3D side scroller platform game where players control a robot navigating and explore through the level while avoiding obstacles."
                        modalDescription="As part of a team project and during my last year of university, 
                        I contributed to the development of a 3D side scroller platform game where players control a robot exploring through a level filled with dreams. 
                        In collaboration with an international group with MyMachine, this project was made using the ideas of children between the age of 9 - 12, we asked 16 children from the class we were instructed to, to give their idea on their dream machine, a machine they wish existed. ">
                        <div class="mt-4">
                            <h2 class="text-xl font-bold mb-2">My Role:</h2>
                            <ul class="list-disc list-inside text-gray-700">
                                <li>Gameplay Programming: Implemented core gameplay mechanics using C++ in Unreal Engine.
                                    <ul class="list-disc list-inside ml-6">
                                        <li>Drone: Programmed the movement and behavior of the robot, mainly the possesion between player to the drone and back,
                                            and the ability to shoot together with an auto aim feature.</li>
                                        <li>Obstacles: Programmed one of the obstacles, being the wasp</li>
                                        <li>UI: Worked on the UI and user interaction for the game, one notable feature is the gallery showcasing all the dreams picked up by the player.</li>
                                    </ul>
                                </li>
                                <li>Collaboration: Worked closely with artists and designers to ensure cohesive game development.</li>
                                <li>Version Control: Managed codebase using Git, Perforce for efficient team collaboration and worked on managing the ticket system in HacknPlan.</li>
                            </ul>
                        </div>
                        <div class="mt-4">
                            <h2 class="text-xl font-bold mb-2">Collaborators:</h2>
                            <ul class="list-disc list-inside text-gray-700">
                                <li>Programmers: Azarafroz Nick, Debrabandere Mendel, Catherine Szobel</li>
                                <li>Artists: Merzari Geremia, Schmitz Boccia Eric, Vanneste Arthur</li>
                                <li>Sound Design: Denis Robbe</li>
                            </ul>
                        </div>
                    </x-portfolio-piece>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="contact mt-8">
        <h3 class=" text-xl font-bold border-b border-gray-300 border-b-8 p-2 w-1/5">Get in touch with me</h3>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div class="mt-4">
                <p class="text-gray-500">If you have any questions or would like to work together, please don't hesitate to contact me.</p>
                <div class="mt-2 bg-white rounded-lg shadow-lg p-4">
                    <div>
                        <p class="text-gray-500">You can reach me via email:</p>
                        <i class="fa fa-envelope"></i><a href="mailto:nXr4K@example.com" class="text-blue-500 hover:text-blue-600">xxx@xxx.com</a>
                        <p class="text-gray-500 mt-4">Or find me on GitHub:</p>
                        <i class="fa fa-github"></i><a href="https://github.com/xxx" class="text-blue-500 hover:text-blue-600">xxx</a>
                    </div>
                </div>
            </div>
            <div class="mt-4 border bg-white rounded-lg shadow-lg p-4">
                <form method="POST" action="/contact" class="flex flex-col gap-4">
                    @csrf
                    <div class="flex flex-col">
                        <label for="name" class="font-bold">Name</label>
                        <input type="text" id="name" name="name" class="border rounded-md p-2" required>
                    </div>
                    <div class="flex flex-col">
                        <label for="email" class="font-bold">Email</label>
                        <input type="email" id="email" name="email" class="border rounded-md p-2" required>
                    </div>
                    <div class="flex flex-col">
                        <label for="message" class="font-bold">Message</label>
                        <textarea id="message" name="message" rows="4" class="border rounded-md p-2" required></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md">Send</button>
                </form>
            </div>
        </div>
    </div>
</x-layout>