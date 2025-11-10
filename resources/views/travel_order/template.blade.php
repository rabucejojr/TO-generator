@extends('layouts.form')
@section('title', 'DOST Travel Order')
@section('form_title', 'DEPARTMENT OF SCIENCE AND TECHNOLOGY')
@section('content')

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 4px;
            vertical-align: top;
        }

        .bold {
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .mt-3 {
            margin-top: 15px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .no-border,
        .no-border th,
        .no-border td {
            border: none !important;
        }

        .checkbox {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #000;
            margin-right: 3px;
            text-align: center;
            /* font-size: 9pt; */
            line-height: 10pt;
        }

        .subitem {
            padding-left: 20px;
        }

        .cell-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 90%;
            text-align: center;
        }

        .cell-center {
            text-align: center;
        }

        .text-justify {
            text-align: justify;
            line-height: 1.3;
        }

        .signature p {
            margin: 0;
            line-height: 1;
        }

        th.underline {
            text-decoration: underline;
            text-underline-offset: 3px;
        }
    </style>

    {{-- HEADER --}}
    <table class="no-border">
        <tr>
            <td class="no-border bold" width="70%">
                LOCAL TRAVEL ORDER No.
                {{ $travelOrder->travel_order_no ?: '__________' }}
            </td>

            <td class="no-border bold" width="30%">
                @php
                    $filingDate = $travelOrder->filing_date
                        ? \Carbon\Carbon::parse($travelOrder->filing_date)->format('F j, Y')
                        : '__________';
                @endphp
                Date: {{ $filingDate }}
            </td>
        </tr>
        <tr>
            <td class="no-border bold" colspan="2">
                Series of {{ $travelOrder->series ?? '____' }}
            </td>
        </tr>
    </table>

    {{-- TRAVELERS --}}
    <p class="mt-3 bold" style="padding-bottom: 5px;">Authority to Travel is hereby granted to:</p>
    @php
        // Normalize traveler data (array of { name, position, agency })
        $travelers = $travelOrder->name;

        // If stored as JSON string, decode it
        if (is_string($travelers)) {
            $decoded = json_decode($travelers, true);
            $travelers = is_array($decoded) ? $decoded : [];
        }

        // Ensure it's always a collection of arrays
$normalizedTravelers = collect($travelers)->map(function ($t) {
    return [
        'name' => $t['name'] ?? 'N/A',
        'position' => $t['position'] ?? '—',
        'agency' => $t['agency'] ?? '—',
            ];
        });
    @endphp


    <table class="no-border mb-2" style="padding-bottom: 20px;">
        <tr class="bold center">
            <th class="no-border underline" width="33%">NAME</th>
            <th class="no-border underline" width="33%">POSITION</th>
            <th class="no-border underline" width="34%">DIVISION / AGENCY</th>
        </tr>

        @forelse ($normalizedTravelers as $traveler)
            <tr>
                <td class="no-border center">{{ $traveler['name'] ?: '________________________' }}</td>
                <td class="no-border center">{{ $traveler['position'] ?: '________________________' }}</td>
                <td class="no-border center">{{ $traveler['agency'] ?: '________________________' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="no-border center">No travelers listed</td>
            </tr>
        @endforelse
    </table>


    {{-- DESTINATION / PURPOSE --}}
    @php
        // Handle multiple destinations if stored as array or JSON
        $destinations = $travelOrder->destination;

        if (is_string($destinations)) {
            $decodedDest = json_decode($destinations, true);
            $destinations = is_array($decodedDest) ? $decodedDest : [$destinations];
        } elseif (!is_array($destinations)) {
            $destinations = [$destinations];
        }

        // Join destinations nicely
        $destinationList = collect($destinations)->filter()->implode(', ');
    @endphp

    <table class="no-border mb-2" style="padding-bottom: 20px;">
        <tr class="bold center">
            <th class="no-border underline" width="33%">Destination</th>
            <th class="no-border underline" width="33%">Inclusive Date/s of Travel</th>
            <th class="no-border underline" width="34%">Purpose(s) of the Travel</th>
        </tr>
        <tr class="center">
            <td class="no-border" height="30">{{ $travelOrder->destination ?: '________________' }}</td>
            <td class="no-border">{{ $travelOrder->inclusive_dates ?: '________________' }}</td>
            <td class="no-border">{{ $travelOrder->purpose ?: '________________' }}</td>
        </tr>
    </table>

    {{-- TRAVEL EXPENSES --}}
    @php
        $mark = 'X';

        // Always prefer database columns, fallback to JSON only if empty
        $fundSource = strtolower(
            trim($travelOrder->fund_source ?? data_get($travelOrder->expenses, 'fund_source', '')),
        );
        $fundDetails = trim($travelOrder->fund_details);

        // Determine active fund for use in expense section
        $activeFund = match (true) {
            str_contains($fundSource, 'general') => 'general',
            str_contains($fundSource, 'project') => 'project',
            str_contains($fundSource, 'other') => 'others',
            default => null,
        };
    @endphp



    <table class="no-border" style="margin-bottom:10px; width:100%;">
        <tr class="bold center">
            <th class="no-border" width="30%">Travel Expenses to be incurred</th>
            <th class="no-border" colspan="3">Appropriate / Fund to which travel expenses would be charged to:</th>
        </tr>

        {{-- FUND HEADERS --}}
        <tr class="center">
            <td class="no-border"></td>

            {{-- General Fund --}}
            <td class="no-border fund-header" style="vertical-align: top;">
                <span class="checkbox">
                    {!! str_contains(strtolower($fundSource), 'general') ? $mark : '&nbsp;' !!}
                </span>
                General Fund
            </td>

            {{-- Project Funds --}}
            <td class="no-border fund-header" style="vertical-align: top;">
                <span class="checkbox">
                    {!! str_contains(strtolower($fundSource), 'project') ? $mark : '&nbsp;' !!}
                </span>
                Project Funds
                @if (!empty($travelOrder->fund_details) && str_contains(strtolower($fundSource), 'project'))
                    <div style="margin:0px; font-size: 9pt; text-align: center;">
                        <strong>({{ strtoupper(trim($fundDetails)) }})</strong>
                    </div>
                @endif
            </td>

            {{-- Others --}}
            <td class="no-border fund-header" style="vertical-align: top;">
                <span class="checkbox">
                    {!! str_contains(strtolower($fundSource), 'other') ? $mark : '&nbsp;' !!}
                </span>
                Others
                @if (!empty($travelOrder->fund_details) && str_contains(strtolower($fundSource), 'other'))
                    <div style="margin:0px; font-size: 9pt; text-align: center;">
                        <strong>({{ strtoupper(trim($fundDetails)) }})</strong>
                    </div>
                @endif
            </td>
        </tr>

        {{-- ACTUAL EXPENSES --}}
        <tr>
            <td class="no-border">
                <span class="checkbox">
                    {!! $travelOrder->isExpenseChecked('actual') ? $mark : '&nbsp;' !!}
                </span>
                Actual
            </td>
            <td class="no-border" colspan="3">&nbsp;</td>
        </tr>

        @php
            $actualItems = [
                'accommodation' => 'Accommodation',
                'meals_food' => 'Meals / Food',
                'incidental_expenses' => 'Incidental Expenses',
            ];
        @endphp

        @foreach ($actualItems as $key => $label)
            <tr>
                <td class="no-border subitem">{{ $label }}</td>
                @foreach (['general', 'project', 'others'] as $fundType)
                    <td class="no-border cell-center">
                        <span class="cell-line">
                            {!! $activeFund === $fundType && $travelOrder->isExpenseChecked('actual', $key) ? $mark : '&nbsp;' !!}
                        </span>
                    </td>
                @endforeach
            </tr>
        @endforeach

        {{-- PER DIEM --}}
        <tr>
            <td class="no-border">
                <span class="checkbox">
                    {!! $travelOrder->isExpenseChecked('per_diem') ? $mark : '&nbsp;' !!}
                </span>
                Per Diem
            </td>
            <td class="no-border" colspan="3">&nbsp;</td>
        </tr>

        @php
            $perDiemItems = [
                'accommodation' => 'Accommodation',
                'subsistence' => 'Subsistence',
                'incidental_expenses' => 'Incidental Expenses',
            ];
        @endphp

        @foreach ($perDiemItems as $key => $label)
            <tr>
                <td class="no-border subitem">{{ $label }}</td>
                @foreach (['general', 'project', 'others'] as $fundType)
                    <td class="no-border cell-center">
                        <span class="cell-line">
                            {!! $activeFund === $fundType && $travelOrder->isExpenseChecked('per_diem', $key) ? $mark : '&nbsp;' !!}
                        </span>
                    </td>
                @endforeach
            </tr>
        @endforeach

        {{-- TRANSPORTATION --}}
        <tr>
            <td class="no-border">
                <span class="checkbox">
                    {!! $travelOrder->isExpenseChecked('transportation') ? $mark : '&nbsp;' !!}
                </span>
                Transportation
            </td>
            <td class="no-border" colspan="3">&nbsp;</td>
        </tr>

        @php
            $transportText = data_get($travelOrder->expenses, 'categories.transportation.public_conveyance_text');
            $transportItems = [
                'official_vehicle' => 'Official Vehicle',
                'public_conveyance' => 'Public Conveyance (Airplane, Bus, Taxi)',
            ];
        @endphp

        @foreach ($transportItems as $key => $label)
            <tr>
                <td class="no-border subitem">
                    {{ $label }}
                    @if ($key === 'public_conveyance' && !empty($transportText))
                        ({{ $transportText }})
                    @endif
                </td>
                @foreach (['general', 'project', 'others'] as $fundType)
                    <td class="no-border cell-center">
                        <span class="cell-line">
                            {!! $activeFund === $fundType && $travelOrder->isExpenseChecked('transportation', $key) ? $mark : '&nbsp;' !!}
                        </span>
                    </td>
                @endforeach
            </tr>
        @endforeach

        {{-- OTHERS --}}
        <tr>
            <td class="no-border">
                <span class="checkbox">
                    {!! $travelOrder->isExpenseChecked('others_enabled') ? $mark : '&nbsp;' !!}
                </span>
                Others
            </td>
            @foreach (['general', 'project', 'others'] as $fundType)
                <td class="no-border cell-center">
                    <span class="cell-line">
                        {!! $activeFund === $fundType && $travelOrder->isExpenseChecked('others_enabled') ? $mark : '&nbsp;' !!}
                    </span>
                </td>
            @endforeach
        </tr>

        {{-- REMARKS SECTION (aligned neatly with expense lines) --}}
        <tr>
            {{-- Label --}}
            <td class="no-border bold" style="padding-top:10px; vertical-align: top;">
                Remarks / Special Instructions:
            </td>

            {{-- Underline or remarks text --}}
            <td class="no-border" colspan="3" style="padding-top:10px;">
                @if (!empty($travelOrder->remarks))
                    <span style="font-weight: normal; padding-left:10px;">
                        {{ $travelOrder->remarks }}
                    </span>
                @else
                    {{-- Slight padding to align neatly under first column text --}}
                    <span
                        style="padding-left:10px; display:inline-block; width:96%; border-bottom:1px solid #000; vertical-align:bottom;">
                        &nbsp;
                    </span>
                @endif
            </td>
        </tr>

    </table>

    <p class="text-justify">
        A report of your travel must be submitted to the Agency Head/Supervising Official within 7 days of completion of
        travel, liquidation of cash advance should be in accordance with Executive Order No. 77, series of 2019: Prescribing
        Rules and Regulations, and Rates of Expenses and Allowances for Official Local and Foreign Travels of Government
        Personnel.
    </p>

    {{-- SIGNATURE --}}
    {{-- <div class="signature" style="margin-top: 20px; page-break-inside: avoid;">
        @foreach ($travelOrder->signatories as $key => $signatory)
            @if ($key === 'recommending')
                <p class="bold">{{ $signatory['label'] }}</p>
            @else
                <p class="bold" style="margin-top: 40px;">{{ $signatory['label'] }}</p>
            @endif
            <div style="margin-top: 10px;">
                <p class="bold" style="text-transform: uppercase;">{{ $signatory['name'] }}</p>
                <p>{{ $signatory['position'] }}</p>
            </div>
        @endforeach
    </div> --}}

    {{-- SIGNATURE --}}
    <div class="signature" style="margin-top: 25px; page-break-inside: avoid; width: 100%; font-size: 10pt;">
        @php
            $signatories = $travelOrder->signatories;
            $hasRecommending = isset($signatories['recommending']);
        @endphp

        @if ($hasRecommending)
            {{-- Two-signatory layout (Recommending left, Approved right but slightly lower) --}}
            <table width="100%" cellspacing="0" cellpadding="0" style="margin-top: 10px; border: none;">
                <tr>
                    {{-- Left: Recommending Approval --}}
                    <td width="50%" align="left" valign="top" style="border: none;">
                        <p class="bold" style="margin-bottom: 30px; font-weight: bold;">
                            {{ $signatories['recommending']['label'] }}
                        </p>
                        <div style="margin-top: 25px;">
                            <p class="bold" style="text-transform: uppercase; margin-bottom: 2px;">
                                {{ $signatories['recommending']['name'] }}
                            </p>
                            <p>{{ $signatories['recommending']['position'] }}</p>
                        </div>
                    </td>

                    {{-- Right: Approved (slightly lower, left-aligned inside right cell) --}}
                    <td width="50%" align="left" valign="bottom" style="padding-top: 70px; border: none;">
                        <p class="bold" style="margin-bottom: 30px; font-weight: bold;">
                            {{ $signatories['approved']['label'] }}
                        </p>
                        <div style="margin-top: 25px;">
                            <p class="bold" style="text-transform: uppercase; margin-bottom: 2px;">
                                {{ $signatories['approved']['name'] }}
                            </p>
                            <p>{{ $signatories['approved']['position'] }}</p>
                        </div>
                    </td>
                </tr>
            </table>
        @else
            {{-- Single signatory layout (within province) --}}
            @foreach ($signatories as $key => $signatory)
                <p class="bold" style="{{ $loop->first ? '' : 'margin-top: 60px;' }}">
                    {{ $signatory['label'] }}
                </p>
                <div style="margin-top: 30px;">
                    <p class="bold" style="text-transform: uppercase;">
                        {{ $signatory['name'] }}
                    </p>
                    <p>{{ $signatory['position'] }}</p>
                </div>
            @endforeach
        @endif
    </div>

@endsection
