@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Filing Date --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Filing Date</label>
        <input type="date" name="filing_date"
               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
               value="{{ old('filing_date', isset($travelOrder->filing_date) ? \Carbon\Carbon::parse($travelOrder->filing_date)->format('Y-m-d') : now()->format('Y-m-d')) }}">
    </div>

    {{-- Traveler(s) --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Traveler/s</label>

        <div id="traveler-list" class="space-y-3">
            @php
                $travelers = old('name', $travelOrder->name ?? [['name' => '', 'position' => '', 'division_agency' => '']]);
            @endphp

            @foreach($travelers as $index => $traveler)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 traveler-item">
                    <div>
                        <input type="text"
                               name="name[{{ $index }}][name]"
                               placeholder="Full Name"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                               value="{{ $traveler['name'] ?? '' }}">
                    </div>
                    <div>
                        <input type="text"
                               name="name[{{ $index }}][position]"
                               placeholder="Position"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                               value="{{ $traveler['position'] ?? '' }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text"
                               name="name[{{ $index }}][division_agency]"
                               placeholder="Division/Agency"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                               value="{{ $traveler['division_agency'] ?? '' }}">
                        @if($index > 0)
                            <button type="button"
                                    class="text-red-600 text-lg font-bold remove-traveler leading-none">&times;</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button"
                id="add-traveler"
                class="mt-3 px-3 py-2 bg-green-700 text-white rounded text-sm hover:bg-green-600 transition">
            + Add Traveler
        </button>
    </div>

    {{-- Destination --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
        <input type="text" name="destination"
               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
               value="{{ old('destination', $travelOrder->destination ?? '') }}">
    </div>

    {{-- Inclusive Dates --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Inclusive Dates of Travel</label>
        <input type="text" name="inclusive_dates" placeholder="e.g. November 12–14, 2025"
               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
               value="{{ old('inclusive_dates', $travelOrder->inclusive_dates ?? '') }}">
    </div>

    {{-- Purpose --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Purpose of Travel</label>
        <textarea name="purpose" rows="3"
                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">{{ old('purpose', $travelOrder->purpose ?? '') }}</textarea>
    </div>
</div>

{{-- FUND SOURCES --}}
<div class="mt-8">
    <h3 class="text-md font-semibold text-gray-800 mb-3">Fund Sources</h3>
    @php
        $fund = $travelOrder->expenses['fund_sources'] ?? [];
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="fund_sources[general_fund]" value="1"
                   {{ old('fund_sources.general_fund', $fund['general_fund'] ?? false) ? 'checked' : '' }}>
            <span>General Fund</span>
        </label>

        <label class="flex items-center space-x-2">
            <input type="checkbox" name="fund_sources[project_funds]" value="1"
                   {{ old('fund_sources.project_funds', $fund['project_funds'] ?? false) ? 'checked' : '' }}>
            <span>Project Funds</span>
        </label>

        <label class="flex items-center space-x-2">
            <input type="checkbox" name="fund_sources[others]" value="1"
                   {{ !empty($fund['others']) ? 'checked' : '' }}>
            <span>Others (Specify)</span>
        </label>
    </div>

    <input type="text" name="fund_sources[others_text]" placeholder="If Others, specify..."
           class="mt-2 w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
           value="{{ old('fund_sources.others_text', $fund['others'] ?? '') }}">
</div>

{{-- TRAVEL EXPENSES --}}
<div class="mt-10">
    <h3 class="text-md font-semibold text-gray-800 mb-3">Travel Expenses</h3>
    @php
        $cat = $travelOrder->expenses['categories'] ?? [];
    @endphp

    {{-- Actual --}}
    <div class="mb-4">
        <p class="font-semibold text-gray-700">Actual:</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2">
            @foreach (['accommodation', 'meals_food', 'incidental_expenses'] as $item)
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="categories[actual][{{ $item }}]" value="1"
                           {{ old("categories.actual.$item", $cat['actual'][$item] ?? false) ? 'checked' : '' }}>
                    <span class="capitalize">{{ str_replace('_', ' ', $item) }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Per Diem --}}
    <div class="mb-4">
        <p class="font-semibold text-gray-700">Per Diem:</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2">
            @foreach (['accommodation', 'subsistence', 'incidental_expenses'] as $item)
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="categories[per_diem][{{ $item }}]" value="1"
                           {{ old("categories.per_diem.$item", $cat['per_diem'][$item] ?? false) ? 'checked' : '' }}>
                    <span class="capitalize">{{ str_replace('_', ' ', $item) }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Transportation --}}
    <div class="mb-4">
        <p class="font-semibold text-gray-700">Transportation:</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2">
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="categories[transportation][official_vehicle]" value="1"
                       {{ old('categories.transportation.official_vehicle', $cat['transportation']['official_vehicle'] ?? false) ? 'checked' : '' }}>
                <span>Official Vehicle</span>
            </label>
            <div>
                <label class="block text-sm">Public Conveyance (Specify)</label>
                <input type="text" name="categories[transportation][public_conveyance]"
                       class="w-full border border-gray-300 rounded-md px-3 py-1 text-sm"
                       value="{{ old('categories.transportation.public_conveyance', $cat['transportation']['public_conveyance'] ?? '') }}">
            </div>
        </div>
    </div>

    {{-- Others --}}
    <div>
        <p class="font-semibold text-gray-700">Others (Specify):</p>
        <input type="text" name="categories[others]"
               class="mt-2 w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
               value="{{ old('categories.others', $cat['others'] ?? '') }}">
    </div>
</div>

{{-- Submit --}}
<div class="mt-10 flex justify-end gap-3">
    <a href="{{ route('travel_order.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">Cancel</a>
    <button type="submit" class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-700 text-sm transition">
        {{ isset($travelOrder) ? 'Update Travel Order' : 'Create Travel Order' }}
    </button>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const addButton = document.getElementById('add-traveler');
    const list = document.getElementById('traveler-list');

    addButton.addEventListener('click', () => {
        const index = list.querySelectorAll('.traveler-item').length;

        const template = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 traveler-item mt-2">
                <div>
                    <input type="text" name="name[${index}][name]" placeholder="Full Name"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                </div>
                <div>
                    <input type="text" name="name[${index}][position]" placeholder="Position"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" name="name[${index}][division_agency]" placeholder="Division/Agency"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <button type="button" class="text-red-600 text-lg font-bold remove-traveler leading-none">&times;</button>
                </div>
            </div>`;

        list.insertAdjacentHTML('beforeend', template);
        attachRemoveHandlers();
    });

    function attachRemoveHandlers() {
        document.querySelectorAll('.remove-traveler').forEach(btn => {
            btn.onclick = () => btn.closest('.traveler-item').remove();
        });
    }

    attachRemoveHandlers();
});
</script>
@endpush
