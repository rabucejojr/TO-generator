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
    <h3 class="text-md font-semibold text-gray-800 mb-3">Fund Source</h3>
    @php
        $fund = $travelOrder->expenses['fund_sources'] ?? [];
        // Detect which fund was previously selected
        $selectedFund = null;
        if (!empty($fund['general_fund'])) {
            $selectedFund = 'general_fund';
        } elseif (!empty($fund['project_funds'])) {
            $selectedFund = 'project_funds';
        } elseif (!empty($fund['others'])) {
            $selectedFund = 'others';
        }
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- General Fund --}}
        <label class="flex items-center space-x-2">
            <input type="radio" name="fund_sources[selected]" value="general_fund"
                   {{ old('fund_sources.selected', $selectedFund) === 'general_fund' ? 'checked' : '' }}
                   required>
            <span>General Fund</span>
        </label>

        {{-- Project Funds --}}
        <label class="flex items-center space-x-2">
            <input type="radio" name="fund_sources[selected]" value="project_funds"
                   {{ old('fund_sources.selected', $selectedFund) === 'project_funds' ? 'checked' : '' }}
                   required>
            <span>Project Funds</span>
        </label>

        {{-- Others --}}
        <label class="flex items-center space-x-2">
            <input type="radio" name="fund_sources[selected]" value="others"
                   {{ old('fund_sources.selected', $selectedFund) === 'others' ? 'checked' : '' }}
                   required>
            <span>Others (Specify)</span>
        </label>
    </div>

    {{-- Project Funds Text Field --}}
    <div id="projectFundsField" class="mt-2 {{ old('fund_sources.selected', $selectedFund) === 'project_funds' ? '' : 'hidden' }}">
        <input type="text" name="fund_sources[project_funds_text]" placeholder="Specify project title or source..."
               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
               value="{{ old('fund_sources.project_funds_text', $fund['project_funds_text'] ?? '') }}">
    </div>

    {{-- Others Text Field --}}
    <div id="othersField" class="mt-2 {{ old('fund_sources.selected', $selectedFund) === 'others' ? '' : 'hidden' }}">
        <input type="text" name="fund_sources[others_text]" placeholder="If Others, specify..."
               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
               value="{{ old('fund_sources.others_text', $fund['others'] ?? '') }}">
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const radios = document.querySelectorAll('input[name="fund_sources[selected]"]');
    const othersField = document.getElementById('othersField');
    const projectField = document.getElementById('projectFundsField');

    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            // Hide both first
            othersField.classList.add('hidden');
            projectField.classList.add('hidden');

            if (radio.value === 'others') {
                othersField.classList.remove('hidden');
            } else if (radio.value === 'project_funds') {
                projectField.classList.remove('hidden');
            }
        });
    });
});
</script>
@endpush



{{-- TRAVEL EXPENSES --}}
<div class="mt-10">
    <h3 class="text-md font-semibold text-gray-800 mb-3">Travel Expenses</h3>
    @php
        $cat = $travelOrder->expenses['categories'] ?? [];
    @endphp

    {{-- Actual --}}
    <div class="mb-4">
        <label class="flex items-center space-x-2 font-semibold text-gray-700">
            <input type="checkbox" name="categories[actual_enabled]" value="1"
                   {{ old('categories.actual_enabled', $cat['actual_enabled'] ?? false) ? 'checked' : '' }}>
            <span>Actual</span>
        </label>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2 ml-5">
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
        <label class="flex items-center space-x-2 font-semibold text-gray-700">
            <input type="checkbox" name="categories[per_diem_enabled]" value="1"
                   {{ old('categories.per_diem_enabled', $cat['per_diem_enabled'] ?? false) ? 'checked' : '' }}>
            <span>Per Diem</span>
        </label>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2 ml-5">
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
        <label class="flex items-center space-x-2 font-semibold text-gray-700">
            <input type="checkbox" name="categories[transportation_enabled]" value="1"
                   {{ old('categories.transportation_enabled', $cat['transportation_enabled'] ?? false) ? 'checked' : '' }}>
            <span>Transportation</span>
        </label>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2 ml-5">
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
