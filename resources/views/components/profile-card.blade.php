@props(['user', 'editable' => false])
<div class="max-w-sm mx-auto mt-12">
    <div class="bg-white dark:bg-gray-900 shadow-xl rounded-2xl flex flex-col items-center">
        <div class="w-full relative">
            <div class="w-full h-48 overflow-hidden rounded-t-2xl">
                <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                    <rect fill="#ffffff" width="540" height="450"></rect>
                    <defs>
                        <linearGradient id="a" gradientUnits="userSpaceOnUse" x1="0" x2="0" y1="0" y2="100%" gradientTransform="rotate(222,648,379)">
                            <stop offset="0" stop-color="#ffffff" />
                            <stop offset="1" stop-color="#FC726E" />
                        </linearGradient>
                    </defs>
                    <rect x="0" y="0" fill="url(#a)" width="100%" height="100%"></rect>
                </svg>
            </div>

            <div class="absolute left-1/2 -bottom-14 transform -translate-x-1/2 w-28 h-28 rounded-full bg-white border-4 border-gray-200 dark:border-gray-700 shadow-md flex items-center justify-center overflow-hidden">
                <x-profile-avatar :user="$user" :editable="$editable" />
            </div>
        </div>

        {{ $slot }}

    </div>
</div>