@if (session('success'))
<div role="status" class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
    {{ session('success') }}
</div>
@endif

@if (session('warning'))
<div role="alert" class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
    {{ session('warning') }}
</div>
@endif
