{{-- The collection page's totals ($totals: CollectionPage::totals). Also the AJAX answer, so they follow the − / +. --}}
@foreach ($totals as $total)
<x-stat-chip :value="$total['value']" :label="$total['label']" :warn="$total['warn']" />
@endforeach
