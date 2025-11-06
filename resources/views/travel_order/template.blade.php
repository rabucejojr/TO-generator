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
            margin-right: 5px;
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
            line-height: 1.2;
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
                LOCAL TRAVEL ORDER No. {{ $travelOrder->travel_order_no ?? '__________' }}
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
    <p class="mt-3 bold" style="padding-bottom: 15px;">Authority to Travel is hereby granted to:</p>
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

    {{-- <pre>
    Fund Source: {{ $fundSource ?? 'null' }}
    Active Fund: {{ $activeFund ?? 'null' }}
    </pre> --}}

    @php
        $mark = 'X';

        // Top-level fund details and source
        $fundSource = strtolower(trim($travelOrder->fund_source ?? ''));
        $fundDetails = $travelOrder->fund_details ?? null;

        // Flexible mapping — handles "general fund", "project", "others", etc.
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
                    {!! strtolower($fundSource) === 'general fund' || strtolower($fundSource) === 'general' ? $mark : '&nbsp;' !!}
                </span>
                General Fund
                @if (!empty($fundDetails) && (strtolower($fundSource) === 'general fund' || strtolower($fundSource) === 'general'))
                    <div style="margin-top: 2px; font-size: 9pt; text-align: center;">
                        ({{ strtoupper($fundDetails) }})
                    </div>
                @endif
            </td>

            {{-- Project Funds --}}
            <td class="no-border fund-header" style="vertical-align: top;">
                <span class="checkbox">
                    {!! strtolower($fundSource) === 'project funds' || strtolower($fundSource) === 'project' ? $mark : '&nbsp;' !!}
                </span>
                Project Funds
                @if (!empty($fundDetails) && (strtolower($fundSource) === 'project funds' || strtolower($fundSource) === 'project'))
                    <div style="margin-top: 2px; font-size: 9pt; text-align: center;">
                        ({{ strtoupper($fundDetails) }})
                    </div>
                @endif
            </td>

            {{-- Others --}}
            <td class="no-border fund-header" style="vertical-align: top;">
                <span class="checkbox">
                    {!! strtolower($fundSource) === 'others' || strtolower($fundSource) === 'other' ? $mark : '&nbsp;' !!}
                </span>
                Others
                @if (!empty($fundDetails) && (strtolower($fundSource) === 'others' || strtolower($fundSource) === 'other'))
                    <div style="margin-top: 2px; font-size: 9pt; text-align: center;">
                        ({{ strtoupper($fundDetails) }})
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
            // Safely read the optional note (text field)
            $transportText = data_get($travelOrder->expenses, 'categories.transportation.public_conveyance_text');
            // NOTE: If you want a checkbox for public conveyance, prefer a boolean key like 'public_conveyance'
            $transportItems = [
                'official_vehicle' => 'Official Vehicle',
                // using 'public_conveyance_text' will only mark X if the note has a non-empty value
                'public_conveyance_text' => 'Public Conveyance (Airplane, Bus, Taxi)',
            ];
        @endphp

        @foreach ($transportItems as $key => $label)
            <tr>
                <td class="no-border subitem">
                    {{ $label }}
                    @if ($key === 'public_conveyance_text' && $transportText)
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

    </table>




    {{-- REMARKS --}}
    <p class="mt-3 bold">
        Remarks / Special Instructions:
        @if (!empty($travelOrder->remarks))
            {{ $travelOrder->remarks }}
        @else
            <span style="display:inline-block; min-width: 450px; border-bottom: 1px solid #000;">&nbsp;</span>
        @endif
    </p>

    <p class="text-justify">
        A report of your travel must be submitted to the Agency Head/Supervising Official within 7 days of completion of
        travel, liquidation of cash advance should be in accordance with Executive Order No. 77, series of 2019: Prescribing
        Rules and Regulations, and Rates of Expenses and Allowances for Official Local and Foreign Travels of Government
        Personnel.
    </p>

    {{-- SIGNATURE --}}
    <div class="signature" style="margin-top: 35px; page-break-inside: avoid;">
        @if ($travelOrder->is_outside_province)
            <table width="100%" class="no-border">
                <tr>
                    <td width="60%" style="vertical-align: top;">
                        <p class="bold">Recommending Approval:</p>
                        <div style="margin-top: 25px;">
                            <p class="bold" style="text-transform: uppercase;">
                                {{ $travelOrder->approved_by ?? 'MR. RICARDO N. VARELA' }}
                            </p>
                            <p>{{ $travelOrder->approved_position ?? 'OIC, PSTO-SDN' }}</p>
                        </div>
                    </td>
                    <td width="40%" style="vertical-align: bottom; padding-top: 60px;">
                        <p class="bold">Approved:</p>
                        <div style="margin-top: 25px;">
                            <p class="bold" style="text-transform: uppercase;">
                                {{ $travelOrder->regional_director ?? 'ENGR. NOEL M. AJOC' }}
                            </p>
                            <p>{{ $travelOrder->regional_position ?? 'Regional Director, DOST Caraga' }}</p>
                        </div>
                    </td>
                </tr>
            </table>
        @else
            <div style="margin-top: 40px;">
                <p class="bold">Approved:</p>
                <div style="margin-top: 25px;">
                    <p class="bold" style="text-transform: uppercase;">
                        {{ $travelOrder->approved_by ?? 'MR. RICARDO N. VARELA' }}
                    </p>
                    <p>{{ $travelOrder->approved_position ?? 'OIC, PSTO-SDN' }}</p>
                </div>
            </div>
        @endif
    </div>

@endsection
