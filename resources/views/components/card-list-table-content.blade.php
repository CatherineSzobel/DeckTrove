@props(['card'])

<tr class="hover:bg-gray-50 transition-colors">
    <td class="px-4 py-2 text-sm font-semibold text-gray-800 underline hover:decoration-blue-500 hover:text-blue-600">
        <a href="{{ $card->link() }}" target="_blank">{{ $card->name() }}</a>
    </td>
    <td class="px-4 py-2 text-sm text-gray-800 whitespace-pre-line max-w-md">{{ $card->description() ?: '-' }}</td>
    <td class="px-4 py-2 text-sm text-gray-800">{{ trim($card->type().' '.$card->subtype()) ?: '-' }}</td>
    <td class="px-4 py-2 text-sm {{ $card->rarityColor() }}">{{ $card->rarityLabel() }}</td>
    <td class="px-4 py-2 text-sm text-gray-800">{{ $card->setName() }}</td>
    <td class="px-4 py-2 text-sm text-gray-800">${{ $card->price() }}</td>
</tr>
