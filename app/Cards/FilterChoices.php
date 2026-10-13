<?php

namespace App\Cards;

/**
 * The filter dropdowns for a series: one per `filters` entry in config/series.php, with the values a provider's
 * filterOptions() found. Lists of values become value => label maps, and config `labels` give nicer names
 * (e.g. Magic's "R" => "Red").
 */
final class FilterChoices
{
    /**
     * @param  array<string, array<int|string, string>>  $options  from CardProvider::filterOptions()
     * @return array<string, array{label: string, choices: array<string, string>}>
     */
    public static function for(string $series, array $options): array
    {
        return collect(config("series.$series.filters"))
            ->map(function (array $filter, string $key) use ($options) {
                $values = $options[$key] ?? [];
                $choices = array_is_list($values) ? array_combine($values, $values) : $values;

                return [
                    'label' => $filter['label'],
                    'choices' => collect($choices)->mapWithKeys(fn ($label, $value) => [$value => $filter['labels'][$value] ?? $label])->all(),
                ];
            })
            ->all();
    }
}
