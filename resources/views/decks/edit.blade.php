<x-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">

        <!-- Edit Deck Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-gray-900">Edit Deck</h1>
            <p class="text-gray-500 mt-1">Modify your deck details and card counts</p>
        </div>

        <!-- Edit Deck Form -->
        <form action="{{ route('decks.update', $deck->id) }}" method="POST" class="space-y-10">
            @csrf
            @method('PUT')

            <!-- Deck Info -->
            <div class="bg-white shadow-lg rounded-2xl p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Deck Info</h2>

                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Deck Name</label>
                        <input type="text" name="name" value="{{ $deck->name }}"
                            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ $deck->description }}</textarea>
                    </div>

                    <div>
                        <form action="{{ route('decks.destroy', $deck->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-8 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 shadow">
                                Delete Deck
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cards Editing Grid -->
            <div class="bg-white shadow-lg rounded-2xl p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Cards in Deck</h2>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    @foreach ($cards as $card)
                    @php
                    $img = data_get($card, 'card_images.0.image_url_small')
                    ?: data_get($card, 'image_uris.normal')
                    ?: data_get($card, 'image');

                    $name = $card['name'] ?? $card['card_name'] ?? 'Card';
                    $count = $card['pivot']['count'] ?? 1;
                    @endphp

                    <div class="group bg-white rounded-lg shadow-md hover:shadow-xl transition overflow-hidden">
                        <img src="{{ $img }}" alt="{{ $name }}" class="h-48 w-full object-cover">

                        <div class="p-3">
                            <h3 class="text-sm font-semibold text-gray-800 truncate">{{ $name }}</h3>

                            <!-- Count Input -->
                            <label class="text-xs mt-1 block text-gray-600">Quantity</label>
                            <input type="number" name="cards[{{ $card['id'] }}]" value="{{ $count }}" min="0"
                                class="w-20 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 mt-1">
                        </div>


                    </div>
                    @endforeach
                </div>

                @if($cards->isEmpty())
                <div class="mt-6 text-center text-gray-500">No cards in this deck yet.</div>
                @endif
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-between">
                <a href="{{ route('decks.show', $deck->id) }}"
                    class="px-6 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-800 font-medium">
                    Cancel
                </a>

                <button type="submit"
                    class="px-8 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 shadow">
                    Save Changes
                </button>
                <!-- Delete Button -->

            </div>

        </form>

    </div>
</x-layout>