<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SOCIAL & MEDIA RISK MONITORING REPORT</title>
    <style>
        @page {
            margin: 36px 45px 45px 45px;
        }
        body {
            font-family: 'DejaVu Sans', 'Times New Roman', serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000000;
        }
        .page-number {
            text-align: center;
            font-size: 9pt;
            margin-bottom: 12px;
            color: #333333;
        }
        h2.main-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 14px;
            letter-spacing: 0.4px;
        }
        .header-meta {
            margin-bottom: 14px;
            font-size: 9.5pt;
            line-height: 1.4;
            text-align: justify;
        }
        .header-meta strong {
            font-weight: bold;
        }

        table.bordered-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        table.bordered-table th, table.bordered-table td {
            border: 1px solid #000000;
            padding: 5px 8px;
            vertical-align: top;
            font-size: 9.5pt;
        }
        table.bordered-table th {
            font-weight: bold;
            background-color: #f8fafc;
        }

        .section-title {
            font-size: 10.5pt;
            font-weight: bold;
            margin-top: 14px;
            margin-bottom: 8px;
        }

        .dot-green { color: #16a34a; font-weight: bold; font-size: 11pt; }
        .dot-yellow { color: #ca8a04; font-weight: bold; font-size: 11pt; }
        .dot-red { color: #dc2626; font-weight: bold; font-size: 11pt; }

        .link-text {
            color: #0b57d0;
            text-decoration: underline;
            word-break: break-all;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <h2 class="main-title">SOCIAL & MEDIA RISK MONITORING REPORT</h2>

    <div class="header-meta">
        <strong>Monitoring date:</strong> {{ \Carbon\Carbon::parse($date)->format('d F Y') }}<br>
        <strong>Geographical focus:</strong> Nepal, particularly Rasuwa, Nuwakot, Trishuli/Bhotekoshi corridor<br>
        <strong>Monitoring subjects:</strong> Upper Trishuli-1 Hydropower Project; Upper Trishuli 1; Upper Trishuli-I; UT-1; UT-One; Upper Trisuli One; Rasuwagadhi Hydropower Project; Rasuwagadhi Hydropower; Rasuwa Bhotekhosi Hydropower Project; Rasuwa Bhotekoshi; Rasuwa Bhotekoshi Hydroelectric Project; Nepal; NWEDC; POWERCHINA; Power Construction Corporation of China; Debris flow; Mudslide; Mudflow; Chinese companies; Chinese enterprises; Chinese firms; SINOHYDRO; Sinohydro Corporation; Sinohydro Bureau 7; Sinohydro Bureau No. 7; Sinohydro Bureau Seven; Sinohydro Bureau 6; Sinohydro Bureau No. 6; Sinohydro Bureau Six; missing person; natural disaster; Nepal hydropower accident
    </div>

    <div class="section-title" style="margin-top: 0;">Risk Classification:</div>
    <table class="bordered-table">
        <thead>
            <tr>
                <th style="width: 28%;">Tier</th>
                <th style="width: 72%;">Definition</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="dot-green">●</span> GREEN: Normal</td>
                <td>Routine news, factual reporting, low-engagement/non-escalatory content.</td>
            </tr>
            <tr>
                <td><span class="dot-yellow">●</span> YELLOW: Elevated</td>
                <td>Rising negative comments, emotional family posts, unverified rumours, dissatisfaction or emerging accountability concerns.</td>
            </tr>
            <tr>
                <td><span class="dot-red">●</span> RED – Critical</td>
                <td>Threats/organisation of protests, mainstream-media exposés, aggressive social-media mobilisation or coordinated corporate targeting.</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">1. Executive Assessment:</div>
    <table class="bordered-table">
        <tr>
            <td style="width: 25%; font-weight: bold;">Time</td>
            <td style="width: 75%;">{{ $slotTime }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Date</td>
            <td>{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Overall Daily Risk Tier</td>
            <td>
                @if(str_starts_with($highestRisk, 'RED'))
                    <span class="dot-red">●</span> <strong>{{ $highestRisk }}</strong>
                @elseif(str_starts_with($highestRisk, 'YELLOW'))
                    <span class="dot-yellow">●</span> <strong>{{ $highestRisk }}</strong>
                @else
                    <span class="dot-green">●</span> <strong>{{ $highestRisk }}</strong>
                @endif
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Description</td>
            <td style="text-align: justify;">
                Monitoring incorporates {{ $mentions->count() }} verified items identified in the monitoring stream and extends the review strictly through {{ $slotTime }} Nepal Time. Only material first published on {{ \Carbon\Carbon::parse($date)->format('d F Y') }} between 12:00 AM and {{ $slotTime }} NPT is included. The dominant themes are missing-person and relief operations, infrastructure resilience, and disaster updates. No newly verified coordinated protest, strike, work stoppage or corporate boycott targeting UT-1, NWEDC, POWERCHINA, or SINOHYDRO was identified in accessible open-source monitoring.
            </td>
        </tr>
    </table>

    <div class="section-title">2. Findings Recorded:</div>
    @foreach($mentions as $index => $m)
        <table class="bordered-table" style="margin-bottom: 14px;">
            <thead>
                <tr>
                    <th colspan="2" style="background-color: #f1f5f9; text-transform: uppercase;">
                        {{ $index + 1 }}. {{ $m->title ?: 'UNTITLED REPORT' }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width: 25%; font-weight: bold;">Description</td>
                    <td style="width: 75%; text-align: justify;">{{ $m->content ?: $m->snippet }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Link</td>
                    <td>
                        @if($m->url)
                            <a href="{{ $m->url }}" class="link-text">{{ $m->url }}</a>
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Risk Interpretation</td>
                    <td style="text-align: justify;">{{ $m->risk_reason ?: 'The coverage is humanitarian and recovery-focused; no direct allegation against UT-1, NWEDC, Doosan, POWERCHINA or SINOHYDRO was verified.' }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Risk Classification</td>
                    <td>
                        @php $tier = strtoupper($m->risk_tier ?? 'GREEN'); @endphp
                        @if(str_contains($tier, 'RED'))
                            <span class="dot-red">●</span> {{ $m->risk_tier }}
                        @elseif(str_contains($tier, 'YELLOW'))
                            <span class="dot-yellow">●</span> {{ $m->risk_tier }}
                        @else
                            <span class="dot-green">●</span> {{ $m->risk_tier ?: 'GREEN: Normal' }}
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <div class="page-break"></div>

    <div class="section-title">3. Risk Matrix</div>
    <table class="bordered-table">
        <thead>
            <tr>
                <th style="width: 25%;">Monitoring Category</th>
                <th style="width: 55%;">Assessment</th>
                <th style="width: 20%; text-align: center;">Risk</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Routine factual reporting</td>
                <td>Active reporting on search, rescue, recovery, and flood reconstruction.</td>
                <td style="text-align: center;"><span class="dot-green">●</span> / <span class="dot-yellow">●</span></td>
            </tr>
            <tr>
                <td>Humanitarian reporting</td>
                <td>Missing persons search and family assistance continue under active public interest.</td>
                <td style="text-align: center;"><span class="dot-yellow">●</span> / WATCH</td>
            </tr>
            <tr>
                <td>UT-1 Project</td>
                <td>No direct allegation or project work halt verified; background monitoring active.</td>
                <td style="text-align: center;"><span class="dot-green">●</span> WATCH</td>
            </tr>
            <tr>
                <td>NWEDC</td>
                <td>No newly verified wrongdoing allegation identified in the monitoring window.</td>
                <td style="text-align: center;"><span class="dot-green">●</span> WATCH</td>
            </tr>
            <tr>
                <td>POWERCHINA / SINOHYDRO</td>
                <td>Contractor operations stable; no physical obstruction or coordinated protest call.</td>
                <td style="text-align: center;"><span class="dot-green">●</span> WATCH</td>
            </tr>
            <tr>
                <td>Protest / Mobilisation</td>
                <td>No company-targeted strike, demonstration, or physical boycott identified.</td>
                <td style="text-align: center;"><span class="dot-green">●</span> WATCH</td>
            </tr>
            <tr style="font-weight: bold; background-color: #f8fafc;">
                <td>Overall Daily Status</td>
                <td>
                    {{ $highestRisk }} — Normal to elevated humanitarian sensitivity; contractors on continued active watch.
                </td>
                <td style="text-align: center;">
                    @if(str_starts_with($highestRisk, 'RED'))
                        <span class="dot-red">●</span>
                    @elseif(str_starts_with($highestRisk, 'YELLOW'))
                        <span class="dot-yellow">●</span>
                    @else
                        <span class="dot-green">●</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>