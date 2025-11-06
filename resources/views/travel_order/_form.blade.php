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

    {{-- Destination --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Destination</label>
        <input type="text" name="destination" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
            value="{{ old('destination', $travelOrder->destination ?? '') }}">
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

{{-- FUND SOURCES --}}
<div class="mt-8">
    <h3 class="text-md font-semibold text-gray-800 mb-3">Fund Source</h3>
    @php
        $fundSource = $travelOrder->expenses['fund_source'] ?? '';
        $fundDetails = $travelOrder->expenses['fund_details'] ?? '';
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- General Fund --}}
        <label class="flex items-center space-x-2">
            <input type="radio" name="fund_source" value="General Fund"
                {{ old('fund_source', $fundSource) === 'General Fund' ? 'checked' : '' }}>
            <span>General Fund</span>
        </label>

        {{-- Project Funds --}}
        <label class="flex items-center space-x-2">
            <input type="radio" name="fund_source" value="Project Funds"
                {{ old('fund_source', $fundSource) === 'Project Funds' ? 'checked' : '' }}>
            <span>Project Funds (Specify)</span>
        </label>

        {{-- Others --}}
        <label class="flex items-center space-x-2">
            <input type="radio" name="fund_source" value="Others"
                {{ old('fund_source', $fundSource) === 'Others' ? 'checked' : '' }}>
            <span>Others (Specify)</span>
        </label>
    </div>

    {{-- Dynamic text area for details --}}
    <div id="fund-details-wrapper"
        class="mt-3 {{ in_array(old('fund_source', $fundSource), ['Project Funds', 'Others']) ? '' : 'hidden' }}">
        <textarea name="fund_details" id="fund-details" placeholder="Enter project name or funding details..."
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">{{ old('fund_details', $fundDetails ?? '') }}</textarea>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const radios = document.querySelectorAll('input[name="fund_source"]');
            const wrapper = document.getElementById('fund-details-wrapper');
            const textArea = document.getElementById('fund-details');

            radios.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (radio.value === 'Project Funds' || radio.value === 'Others') {
                        wrapper.classList.remove('hidden');
                    } else {
                        wrapper.classList.add('hidden');
                        textArea.value = '';
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

        $cat['actual'] = is_array($cat['actual'] ?? null) ? $cat['actual'] : [];
        $cat['per_diem'] = is_array($cat['per_diem'] ?? null) ? $cat['per_diem'] : [];
        $cat['transportation'] = is_array($cat['transportation'] ?? null) ? $cat['transportation'] : [];
    @endphp

    {{-- Actual --}}
    <div class="mb-4">
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="expenses[categories][actual][enabled]" value="1" id="actual-checkbox"
                {{ old('expenses.categories.actual.enabled', isset($cat['actual']) && count(array_filter($cat['actual'])) > 0) ? 'checked' : '' }}>
            <span class="font-semibold text-gray-700">Actual</span>
        </label>

        <div id="actual-options"
            class="ml-6 mt-2 space-y-2 {{ old('expenses.categories.actual.enabled', isset($cat['actual']) && count(array_filter($cat['actual'])) > 0) ? '' : 'hidden' }}">
            @foreach (['accommodation' => 'Accommodation', 'meals_food' => 'Meals / Food', 'incidental_expenses' => 'Incidental Expenses'] as $key => $label)
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="expenses[categories][actual][{{ $key }}]" value="1"
                        {{ old("expenses.categories.actual.$key", $cat['actual'][$key] ?? false) ? 'checked' : '' }}>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Per Diem --}}
    <div class="mb-4">
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="expenses[categories][per_diem][enabled]" value="1" id="perdiem-checkbox"
                {{ old('expenses.categories.per_diem.enabled', isset($cat['per_diem']) && count(array_filter($cat['per_diem'])) > 0) ? 'checked' : '' }}>
            <span class="font-semibold text-gray-700">Per Diem</span>
        </label>

        <div id="perdiem-options"
            class="ml-6 mt-2 space-y-2 {{ old('expenses.categories.per_diem.enabled', isset($cat['per_diem']) && count(array_filter($cat['per_diem'])) > 0) ? '' : 'hidden' }}">
            @foreach (['accommodation' => 'Accommodation', 'subsistence' => 'Subsistence', 'incidental_expenses' => 'Incidental Expenses'] as $key => $label)
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="expenses[categories][per_diem][{{ $key }}]" value="1"
                        {{ old("expenses.categories.per_diem.$key", $cat['per_diem'][$key] ?? false) ? 'checked' : '' }}>
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Transportation --}}
    <div class="mb-4">
        <label class="flex items-center space-x-2">
            <input type="checkbox" name="expenses[categories][transportation][enabled]" value="1"
                id="transportation-checkbox"
                {{ old('expenses.categories.transportation.enabled', isset($cat['transportation']) && count(array_filter($cat['transportation'])) > 0) ? 'checked' : '' }}>
            <span class="font-semibold text-gray-700">Transportation</span>
        </label>

        <div id="transportation-options"
            class="ml-6 mt-2 space-y-2 {{ old('expenses.categories.transportation.enabled', isset($cat['transportation']) && count(array_filter($cat['transportation'])) > 0) ? '' : 'hidden' }}">
            @foreach (['official_vehicle' => 'Official Vehicle', 'public_conveyance' => 'Public Conveyance (Specify)'] as $key => $label)
                <div>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="expenses[categories][transportation][{{ $key }}]" value="1"
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
</div>


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sections = [{
                    main: '#actual-checkbox',
                    sub: '#actual-options'
                },
                {
                    main: '#perdiem-checkbox',
                    sub: '#perdiem-options'
                },
                {
                    main: '#transportation-checkbox',
                    sub: '#transportation-options'
                },
            ];

            sections.forEach(({
                main,
                sub
            }) => {
                const mainCheckbox = document.querySelector(main);
                const subSection = document.querySelector(sub);

                mainCheckbox.addEventListener('change', () => {
                    if (mainCheckbox.checked) {
                        subSection.classList.remove('hidden');
                    } else {
                        subSection.classList.add('hidden');
                        subSection.querySelectorAll('input[type="checkbox"], input[type="text"]')
                            .forEach(i => {
                                if (i.type === 'checkbox') i.checked = false;
                                if (i.type === 'text') i.value = '';
                            });
                    }
                });
            });
        });
    </script>
@endpush


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
        });
    </script>
@endpush
