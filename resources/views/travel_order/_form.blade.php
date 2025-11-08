@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Filing Date --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Filing Date</label>
        <input type="date" name="filing_date" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
            value="{{ old('filing_date', isset($travelOrder->filing_date) ? \Carbon\Carbon::parse($travelOrder->filing_date)->format('Y-m-d') : now()->format('Y-m-d')) }}">
    </div>

    {{-- Traveler(s) --}}
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Traveler/s</label>

        <div id="traveler-list" class="space-y-3">
            @php
                $travelers = old('name', $travelOrder->name ?? [['name' => '', 'position' => '', 'agency' => '']]);
            @endphp

            @foreach ($travelers as $index => $traveler)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 traveler-item">
                    <div>
                        <input type="text" name="name[{{ $index }}][name]" placeholder="Full Name"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                            value="{{ $traveler['name'] ?? '' }}">
                    </div>
                    <div>
                        <input type="text" name="name[{{ $index }}][position]" placeholder="Position"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                            value="{{ $traveler['position'] ?? '' }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" name="name[{{ $index }}][agency]" placeholder="Agency/Division"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                            value="{{ $traveler['agency'] ?? '' }}">
                        @if ($index > 0)
                            <button type="button"
                                class="text-red-600 text-lg font-bold remove-traveler leading-none">&times;</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" id="add-traveler"
            class="mt-3 px-3 py-2 bg-green-700 text-white rounded text-sm hover:bg-green-600 transition">
            + Add Traveler
        </button>
    </div>

    {{-- Travel Scope --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Travel Scope</label>
        <select name="scope" id="scope" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
            <option value="within" {{ old('scope', $travelOrder->scope ?? 'within') === 'within' ? 'selected' : '' }}>
                Within Surigao del Norte
            </option>
            <option value="outside" {{ old('scope', $travelOrder->scope ?? '') === 'outside' ? 'selected' : '' }}>
                Outside Surigao del Norte
            </option>
        </select>
    </div>

    {{-- Destination --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
        <input type="text" name="destination" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
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
        <textarea name="purpose" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">{{ old('purpose', $travelOrder->purpose ?? '') }}</textarea>
    </div>
</div>

{{-- FUND SOURCE --}}
<div class="mt-8">
    <h3 class="text-md font-semibold text-gray-800 mb-3">Fund Source</h3>

    @php
        // Determine active fund source from the model or old input
        $activeFund = old('fund_source', strtolower($travelOrder->fund_source ?? ''));
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- General Fund --}}
        <label class="flex items-center space-x-2">
            <input type="radio" name="fund_source" value="General Fund" class="fund-source-toggle"
                {{ $activeFund === 'general fund' ? 'checked' : '' }}>
            <span>General Fund</span>
        </label>

        {{-- Project Funds --}}
        <label class="flex items-center space-x-2">
            <input type="radio" name="fund_source" value="Project Funds" class="fund-source-toggle"
                {{ $activeFund === 'project funds' ? 'checked' : '' }}>
            <span>Project Funds</span>
        </label>

        {{-- Others --}}
        <label class="flex items-center space-x-2">
            <input type="radio" name="fund_source" value="Others" class="fund-source-toggle"
                {{ $activeFund === 'others' ? 'checked' : '' }}>
            <span>Others</span>
        </label>
    </div>

    {{-- Dynamic text input for Project Funds or Others --}}
    <div id="fund-details-box"
        class="mt-3 {{ in_array(strtolower($activeFund), ['project funds', 'others']) ? '' : 'hidden' }}">
        <label class="block text-sm text-gray-700 mb-1">Specify Details</label>
        <input type="text" name="fund_details" placeholder="e.g. SIDLAK, LGU, etc."
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
            value="{{ old('fund_details', $travelOrder->fund_details ?? '') }}">
    </div>
</div>


{{-- TRAVEL EXPENSES --}}
{{-- <div class="mt-10">
    <h3 class="text-md font-semibold text-gray-800 mb-3">Travel Expenses</h3>
    @php
        $cat = $travelOrder->expenses['categories'] ?? [];

        $cat['actual'] = is_array($cat['actual'] ?? null) ? $cat['actual'] : [];
        $cat['per_diem'] = is_array($cat['per_diem'] ?? null) ? $cat['per_diem'] : [];
        $cat['transportation'] = is_array($cat['transportation'] ?? null) ? $cat['transportation'] : [];
    @endphp --}}

{{-- Actual --}}
{{-- <div class="mb-4">
        <p class="font-semibold text-gray-700">Actual:</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2">
            @foreach (['accommodation', 'meals_food', 'incidental_expenses'] as $item)
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="expenses[categories][actual][{{ $key }}]" value="1"
                        {{ old("expenses.categories.actual.$key", $cat['actual'][$key] ?? false) ? 'checked' : '' }}>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div> --}}

{{-- Per Diem --}}
{{-- <div class="mb-4">
        <p class="font-semibold text-gray-700">Per Diem:</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mt-2">
            @foreach (['accommodation', 'subsistence', 'incidental_expenses'] as $item)
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="expenses[categories][per_diem][{{ $key }}]" value="1"
                        {{ old("expenses.categories.per_diem.$key", $cat['per_diem'][$key] ?? false) ? 'checked' : '' }}>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div> --}}

{{-- Transportation --}}
{{-- <div class="mb-4">
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
</div> --}}

{{-- TRAVEL EXPENSES --}}
<div class="mt-10">
    <h3 class="text-md font-semibold text-gray-800 mb-3">Travel Expenses</h3>
    @php
        $cat = $travelOrder->expenses['categories'] ?? [];

        $cat['actual'] = is_array($cat['actual'] ?? null) ? $cat['actual'] : [];
        $cat['per_diem'] = is_array($cat['per_diem'] ?? null) ? $cat['per_diem'] : [];
        $cat['transportation'] = is_array($cat['transportation'] ?? null) ? $cat['transportation'] : [];
    @endphp

    {{-- ACTUAL --}}
    <div class="mb-5">
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="expenses[categories][actual][enabled]" value="1" class="expense-toggle"
                data-target="#actual-options"
                {{ old('expenses.categories.actual.enabled', $cat['actual']['enabled'] ?? false) ? 'checked' : '' }}>
            <span class="font-semibold text-gray-700">Actual</span>
        </label>

        <div id="actual-options"
            class="ml-6 mt-2 grid grid-cols-1 md:grid-cols-3 gap-2 {{ old('expenses.categories.actual.enabled', $cat['actual']['enabled'] ?? false) ? '' : 'hidden' }}">
            @foreach ([
        'accommodation' => 'Accommodation',
        'meals_food' => 'Meals / Food',
        'incidental_expenses' => 'Incidental Expenses',
    ] as $key => $label)
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="expenses[categories][actual][{{ $key }}]" value="1"
                        {{ old("expenses.categories.actual.$key", $cat['actual'][$key] ?? false) ? 'checked' : '' }}>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- PER DIEM --}}
    <div class="mb-5">
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="expenses[categories][per_diem][enabled]" value="1"
                class="expense-toggle" data-target="#perdiem-options"
                {{ old('expenses.categories.per_diem.enabled', $cat['per_diem']['enabled'] ?? false) ? 'checked' : '' }}>
            <span class="font-semibold text-gray-700">Per Diem</span>
        </label>

        <div id="perdiem-options"
            class="ml-6 mt-2 grid grid-cols-1 md:grid-cols-3 gap-2 {{ old('expenses.categories.per_diem.enabled', $cat['per_diem']['enabled'] ?? false) ? '' : 'hidden' }}">
            @foreach ([
        'accommodation' => 'Accommodation',
        'subsistence' => 'Subsistence',
        'incidental_expenses' => 'Incidental Expenses',
    ] as $key => $label)
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="expenses[categories][per_diem][{{ $key }}]" value="1"
                        {{ old("expenses.categories.per_diem.$key", $cat['per_diem'][$key] ?? false) ? 'checked' : '' }}>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- TRANSPORTATION --}}
    <div class="mb-5">
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="expenses[categories][transportation][enabled]" value="1"
                class="expense-toggle" data-target="#transport-options"
                {{ old('expenses.categories.transportation.enabled', $cat['transportation']['enabled'] ?? false) ? 'checked' : '' }}>
            <span class="font-semibold text-gray-700">Transportation</span>
        </label>

        <div id="transportation-options"
            class="ml-6 mt-2 space-y-2 {{ old('expenses.categories.transportation.enabled', isset($cat['transportation']) && count(array_filter($cat['transportation'])) > 0) ? '' : 'hidden' }}">
            @foreach (['official_vehicle' => 'Official Vehicle', 'public_conveyance' => 'Public Conveyance (Specify)'] as $key => $label)
                <div>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="expenses[categories][transportation][{{ $key }}]"
                            value="1"
                            {{ old("expenses.categories.transportation.$key", $cat['transportation'][$key] ?? false) ? 'checked' : '' }}>
                        <span>{{ $label }}</span>
                    </label>
                    @if ($key === 'public_conveyance')
                        <input type="text" name="expenses[categories][transportation][public_conveyance_text]"
                            placeholder="Specify public conveyance"
                            class="mt-1 w-full border border-gray-300 rounded-md px-3 py-1 text-sm"
                            value="{{ old('expenses.categories.transportation.public_conveyance_text', $cat['transportation']['public_conveyance_text'] ?? '') }}">
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Others --}}
    <div class="mb-4">
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="expenses[categories][others_enabled]" value="1" id="others-checkbox"
                {{ old('expenses.categories.others_enabled', $cat['others_enabled'] ?? false) ? 'checked' : '' }}>
            <span class="font-semibold text-gray-700">Others</span>
        </label>
    </div>

</div>




{{-- Submit --}}
<div class="mt-10 flex justify-end gap-3">
    <a href="{{ route('travel_order.index') }}"
        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">Cancel</a>
    <button type="submit" class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-700 text-sm transition">
        {{ isset($travelOrder) ? 'Update Travel Order' : 'Create Travel Order' }}
    </button>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            /* ============================================================
               TRAVELER ADD / REMOVE LOGIC
            ============================================================ */
            const addButton = document.getElementById('add-traveler');
            const list = document.getElementById('traveler-list');

            if (addButton && list) {
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
                            <input type="text" name="name[${index}][agency]" placeholder="Agency/Division"
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
            }

            /* ============================================================
               TRAVEL EXPENSES TOGGLE LOGIC
            ============================================================ */
            document.querySelectorAll('.expense-toggle').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const target = document.querySelector(this.dataset.target);
                    if (!target) return;

                    if (this.checked) {
                        target.classList.remove('hidden');
                    } else {
                        target.classList.add('hidden');
                        // Uncheck and clear all nested inputs
                        target.querySelectorAll('input[type="checkbox"], input[type="text"]')
                            .forEach(input => {
                                input.checked = false;
                                if (input.type === 'text') input.value = '';
                            });
                    }
                });
            });


            /* ============================================================
               FUND SOURCE TOGGLE LOGIC
            ============================================================ */
            document.querySelectorAll('.fund-source-toggle').forEach(radio => {
                radio.addEventListener('change', function() {
                    const fundDetailsBox = document.getElementById('fund-details-box');
                    if (['project', 'others'].includes(this.value)) {
                        fundDetailsBox.classList.remove('hidden');
                    } else {
                        fundDetailsBox.classList.add('hidden');
                        const input = fundDetailsBox.querySelector('input');
                        if (input) input.value = '';
                    }
                });
            });

            const toggles = document.querySelectorAll('.fund-source-toggle');
            const detailsBox = document.getElementById('fund-details-box');

            toggles.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (radio.value === 'Project Funds' || radio.value === 'Others') {
                        detailsBox.classList.remove('hidden');
                    } else {
                        detailsBox.classList.add('hidden');
                    }
                });

            }); // ✅ Properly close DOMContentLoaded
        });
    </script>
@endpush
