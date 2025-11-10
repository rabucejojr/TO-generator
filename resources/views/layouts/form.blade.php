<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'DOST Form')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #1f2937;
            /* gray-800 */
        }

        img.form-logo {
            height: 45px !important;
            width: auto !important;
            display: flex;
        }
    </style>
</head>

<body class="bg-white text-black text-sm">
    <div class="mx-auto p-1">

        <!-- Header (Letterhead with Logo Only) -->
        @php
            $pub = public_path('dost-logo.png');
            $logoSrc = file_exists($pub) ? 'file://' . $pub : asset('dost-logo.png');
        @endphp

        <!-- Header Table -->
        <table width="100%" style="border: none; margin-bottom: 5px;">
            <tr>
                <!-- Left: Logo -->
                <td style="width: 100px; text-align: right; border: none;">
                    <img src="{{ $logoSrc }}" alt="DOST Logo" style="height: 60px; width: auto;">
                </td>

                <!-- Form Header -->
                <td style="text-align: left; border: none; vertical-align: middle;">
                    <p style="margin: 0; font-size: 16px; font-weight: bold; padding-bottom: 4px;">
                        @yield('form_title')
                    </p>

                    <!-- Local Travel Order Info -->
                    <div style="margin-top: 4px;font-size: 12px;">
                        LOCAL TRAVEL ORDER No.
                        {{ $travelOrder->travel_order_no ?: '______________' }}
                        <span style="margin-left: 130px;">
                            @php
                                $filingDate = $travelOrder->filing_date
                                    ? \Carbon\Carbon::parse($travelOrder->filing_date)->format('F j, Y')
                                    : '__________';
                            @endphp
                            Date: {{ $filingDate }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>


        <!-- Form Content -->
        <div>
            @yield('content')
        </div>
    </div>
</body>

</html>
