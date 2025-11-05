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
            font-size: 9pt;
            line-height: 10pt;
        }

        .fund-header {
            font-size: 8pt;
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
    <p class="mt-3 bold">Authority to Travel is hereby granted to:</p>

    @php
        // Normalize the name/traveler data (handles arrays, JSON strings, and single fields)
        $travelers = $travelOrder->name;

        if (is_string($travelers)) {
            $decoded = json_decode($travelers, true);
            $travelers = is_array($decoded) ? $decoded : [];
        }

        // If name field is empty but position/division_agency exist (old data), fill it
        if (empty($travelers) && (!empty($travelOrder->position) || !empty($travelOrder->division_agency))) {
            $travelers = [
                [
                    'name' => $travelOrder->position ?? 'N/A',
                    'position' => $travelOrder->position ?? null,
                    'division_agency' => $travelOrder->division_agency ?? null,
                ],
            ];
        }

        // Normalize structure
        $normalizedTravelers = collect($travelers)->map(function ($item) use ($travelOrder) {
            if (is_array($item)) {
                return [
                    'name' => $item['name'] ?? ($travelOrder->name ?? null),
                    'position' => $item['position'] ?? ($travelOrder->position ?? null),
                    'division_agency' => $item['division_agency'] ?? ($travelOrder->division_agency ?? null),
                ];
            } elseif (is_string($item)) {
                // Handle simple string names
                return [
                    'name' => $item,
                    'position' => $travelOrder->position ?? null,
                    'division_agency' => $travelOrder->division_agency ?? null,
                ];
            }
            return [
                'name' => $travelOrder->name ?? null,
                'position' => $travelOrder->position ?? null,
                'division_agency' => $travelOrder->division_agency ?? null,
            ];
        });
    @endphp

    <table class="no-border mb-2">
        <tr class="bold center">
            <th class="no-border" width="33%">NAME</th>
            <th class="no-border" width="33%">POSITION</th>
            <th class="no-border" width="34%">DIVISION / AGENCY</th>
        </tr>

        @forelse ($normalizedTravelers as $traveler)
            <tr>
                <td class="no-border center">{{ $traveler['name'] ?: '________________________' }}</td>
                <td class="no-border center">{{ $traveler['position'] ?: '________________________' }}</td>
                <td class="no-border center">{{ $traveler['division_agency'] ?: '________________________' }}</td>
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

    <table class="no-border mb-2">
        <tr class="bold center">
            <th class="no-border" width="33%">Destination</th>
            <th class="no-border" width="33%">Inclusive Date/s of Travel</th>
            <th class="no-border" width="34%">Purpose(s) of the Travel</th>
        </tr>
        <tr class="center">
            <td class="no-border" height="30">{{ $travelOrder->destination ?: '________________' }}</td>
            <td class="no-border">{{ $travelOrder->inclusive_dates ?: '________________' }}</td>
            <td class="no-border" style="text-align: justify;">{{ $travelOrder->purpose ?: '________________' }}</td>
        </tr>
    </table>


    {{-- EXPENSES --}}
    @php
        $exp = $travelOrder->expenses ?? [];
        $fund = $exp['fund_sources'] ?? [];
        $cat = $exp['categories'] ?? [];

        $activeFund = null;
        if (!empty($fund['general_fund'])) {
            $activeFund = 'general';
        } elseif (!empty($fund['project_funds'])) {
            $activeFund = 'project';
        } elseif (!empty($fund['others'])) {
            $activeFund = 'others';
        }

        $mark = 'X';
    @endphp

    <table class="no-border" style="margin-bottom:10px;">
        <tr class="bold center">
            <th class="no-border" width="30%">Travel Expenses to be incurred</th>
            <th class="no-border" colspan="3">Appropriate / Fund to which travel expenses would be charged to:</th>
        </tr>

        {{-- FUND HEADERS --}}
        <tr class="center">
            <td class="no-border">&nbsp;</td>
            <td class="no-border fund-header">
                ( {!! $fund['general_fund'] ?? false ? $mark : '&nbsp;' !!} ) General Fund
            </td>
            <td class="no-border fund-header">
                ( {!! $fund['project_funds'] ?? false ? $mark : '&nbsp;' !!} ) Project Funds
            </td>
            <td class="no-border fund-header">
                ( {!! !empty($fund['others']) ? $mark : '&nbsp;' !!} ) Others:
                {{ $fund['others'] ?? '________________' }}
            </td>
        </tr>

        {{-- ACTUAL --}}
        <tr>
            <td class="no-border"><span class="checkbox"></span> Actual {!! ($cat['actual_enabled'] ?? false) ? $mark : '&nbsp;' !!}</td>
            <td class="no-border" colspan="3">&nbsp;</td>
        </tr>
        @foreach (['accommodation', 'meals_food', 'incidental_expenses'] as $item)
            <tr>
                <td class="no-border subitem">{{ ucfirst(str_replace('_', ' ', $item)) }}</td>
                @foreach (['general', 'project', 'others'] as $fundType)
                    <td class="no-border cell-center">
                        <span class="cell-line">
                            {!! $activeFund === $fundType && ($cat['actual'][$item] ?? false) ? $mark : '&nbsp;' !!}
                        </span>
                    </td>
                @endforeach
            </tr>
        @endforeach

        {{-- PER DIEM --}}
        <tr>
           <td class="no-border"><span class="checkbox"></span> Per Diem {!! ($cat['per_diem_enabled'] ?? false) ? $mark : '&nbsp;' !!}</td>
            <td class="no-border" colspan="3">&nbsp;</td>
        </tr>
        @foreach (['accommodation', 'subsistence', 'incidental_expenses'] as $item)
            <tr>
                <td class="no-border subitem">{{ ucfirst(str_replace('_', ' ', $item)) }}</td>
                @foreach (['general', 'project', 'others'] as $fundType)
                    <td class="no-border cell-center">
                        <span class="cell-line">
                            {!! $activeFund === $fundType && ($cat['per_diem'][$item] ?? false) ? $mark : '&nbsp;' !!}
                        </span>
                    </td>
                @endforeach
            </tr>
        @endforeach

        {{-- TRANSPORTATION --}}
        <tr>
           <td class="no-border"><span class="checkbox"></span> Transportation {!! ($cat['transportation_enabled'] ?? false) ? $mark : '&nbsp;' !!}</td>
            <td class="no-border" colspan="3">&nbsp;</td>
        </tr>
        @foreach (['official_vehicle', 'public_conveyance'] as $t)
            <tr>
                <td class="no-border subitem">
                    {{ $t === 'official_vehicle' ? 'Official Vehicle' : 'Public Conveyance (Airplane, Bus, Taxi)' }}
                </td>
                @foreach (['general', 'project', 'others'] as $fundType)
                    <td class="no-border cell-center">
                        <span class="cell-line">
                            {!! $activeFund === $fundType && ($cat['transportation'][$t] ?? false) ? $mark : '&nbsp;' !!}
                        </span>
                    </td>
                @endforeach
            </tr>
        @endforeach

        {{-- OTHERS --}}
        <tr>
            <td class="no-border"><span class="checkbox"></span> Others</td>
            <td class="no-border" colspan="3">
                <span class="cell-line">{{ $cat['others'] ?? '' }}</span>
            </td>
        </tr>
    </table>

    {{-- REMARKS --}}
    <p class="mt-3 bold">Remarks / Special Instructions: {{ $travelOrder->remarks }}</p>
    <p class="text-justify">
        A report of your travel must be submitted to the Agency Head/Supervising Official within 7 days of completion of
        travel. Liquidation of cash advance should be in accordance with Executive Order No. 77, s. 2019.
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
