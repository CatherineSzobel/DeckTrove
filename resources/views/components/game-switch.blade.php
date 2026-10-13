{{-- Switches to the same section in another game; App\View\Components\GameSwitch picks the target route. --}}

<div role="group" aria-label="Game" data-game-switch class="inline-flex flex-wrap gap-1 rounded-xl border border-line bg-surface p-1">
    @foreach (config('series') as $key => $config)
    <a href="{{ route($target, $key) }}" data-game="{{ $key }}" @if ($key === $series) aria-current="page" @endif class="segment">{{ $config['label'] }}</a>
    @endforeach
</div>
