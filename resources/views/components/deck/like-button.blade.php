@props(['deck', 'liked' => false, 'count' => 0, 'size' => 'sm'])

{{--
    A deck's likes. Other players get a toggle: a real form (POST to like, DELETE to unlike) that the likeButton Alpine
    component (resources/js/likes.js) turns into an in-place fetch. Guests get the same button as a link to log in;
    the owner sees the count. `sm` is the compact heart on deck cards, `lg` the deck page button.
--}}
@guest
<a href="{{ route('login') }}" data-like="guest" {{ $attributes->class(['group btn-like', "btn-like-$size"]) }}
    aria-label="Log in to like {{ $deck->name }} ({{ $count }} {{ Str::plural('like', $count) }})">
    <x-deck.like-label label="Log in to like" :count="$count" :size="$size" />
</a>
@else
@if (auth()->id() === $deck->user_id)
@if ($size === 'lg')
<p data-like="own" {{ $attributes->class('flex items-center justify-center gap-1.5 text-[13px] text-body') }}>
    <x-heroicon-s-heart class="h-4 w-4 text-accent-ink" aria-hidden="true" />
    {{ $count }} {{ Str::plural('player', $count) }} liked this deck
</p>
@else
<span data-like="own" {{ $attributes->class('relative z-10 inline-flex min-h-11 items-center gap-1.5 font-mono text-sm font-bold text-body') }}>
    <x-heroicon-s-heart class="h-5 w-5 text-accent-ink" aria-hidden="true" />
    {{ $count }}<span class="sr-only"> {{ Str::plural('like', $count) }}</span>
</span>
@endif
@else
<form method="POST" action="{{ route('decks.like', $deck) }}" data-like="toggle"
    x-data="likeButton({ liked: @js((bool) $liked), count: @js($count), url: @js(route('decks.like', $deck)) })"
    @submit.prevent="toggle($el)" {{ $attributes->class(['relative z-10', 'w-full' => $size === 'lg']) }}>
    @csrf
    <input type="hidden" name="_method" value="{{ $liked ? 'DELETE' : 'POST' }}" :value="liked ? 'DELETE' : 'POST'">
    <button type="submit" class="group btn-like btn-like-{{ $size }}"
        aria-pressed="{{ $liked ? 'true' : 'false' }}" :aria-pressed="liked.toString()" :aria-busy="busy.toString()"
        aria-label="{{ $liked ? 'Unlike' : 'Like' }} {{ $deck->name }}, {{ $count }} {{ Str::plural('like', $count) }}"
        :aria-label="(liked ? 'Unlike ' : 'Like ') + @js($deck->name) + ', ' + count + (count === 1 ? ' like' : ' likes')">
        <x-deck.like-label :label="$liked ? 'Liked' : 'Like'" :count="$count" :size="$size" live />
    </button>
</form>
@endif
@endguest
