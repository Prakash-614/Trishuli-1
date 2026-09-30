<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Social & Media Risk Monitoring Report — {{ $date }}</title>
    <style>
        @page {
            margin: 40px 45px 50px 45px;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.5;
            color: #1e293b;
        }

        /* Running Footer */
        footer {
            position: fixed;
            bottom: -32px;
            left: 0;
            right: 0;
            height: 20px;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
            font-size: 8.5px;
            color: #64748b;
        }

        .page-number:after {
            content: counter(page);
        }

        /* Header Document Titles */
        .doc-title {
            font-size: 17px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin: 0;
        }

        .doc-subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
            margin-bottom: 14px;
        }

        /* Scope & Metadata Box */
        .scope-box {
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            padding: 8px 0;
            margin-bottom: 16px;
            font-size: 9.5px;
            line-height: 1.45;
        }

        .scope-row {
            margin-bottom: 3px;
        }

        .scope-label {
            font-weight: bold;
            color: #0f172a;
        }

        /* Section Headings with Solid Blue Bar */
        .section-bar {
            background-color: #f1f5f9;
            border-left: 4px solid #1e3a8a;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-top: 18px;
            margin-bottom: 12px;
            page-break-after: avoid;
        }

        /* KPI Executive Summary Grid */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .kpi-cell {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            text-align: center;
            background: #ffffff;
        }

        .kpi-num {
            font-size: 16px;
            font-weight: bold;
            display: block;
        }

        .kpi-title {
            font-size: 8.5px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
        }

        /* Executive Assessment Box */
        .exec-box {
            border: 1px solid #cbd5e1;
            border-left: 4px solid #1e3a8a;
            background: #f8fafc;
            padding: 10px 14px;
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        /* Finding Card Item (Page-break safe) */
        .finding-card {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .finding-card-head {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 6px 10px;
        }

        .finding-title {
            font-weight: bold;
            font-size: 10.5px;
            color: #0f172a;
            display: inline-block;
            width: 76%;
        }

        .finding-card-body {
            padding: 8px 10px;
        }

        /* Badge Styling */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-right {
            float: right;
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .badge-yellow {
            background: #fef08a;
            color: #854d0e;
            border: 1px solid #fde047;
        }

        .badge-green {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .badge-neutral {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        /* Clean Table */
        .clean-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
            margin-bottom: 15px;
        }

        .clean-table th,
        .clean-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: top;
        }

        .clean-table th {
            background: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
            text-align: left;
        }

        .clean-table tr:nth-child(even) td {
            background: #fafafa;
        }

        /* Link Buttons */
        .btn-link {
            color: #2563eb;
            text-decoration: none;
            font-weight: bold;
            font-size: 9px;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <!-- FOOTER -->
    <footer>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; text-align: left;">
                    SOCIAL & MEDIA RISK MONITORING REPORT — {{ \Carbon\Carbon::parse($date)->format('d F Y') }}
                </td>
                <td style="border: none; text-align: right;">
                    Page <span class="page-number"></span>
                </td>
            </tr>
        </table>
    </footer>

    <!-- DOCUMENT HEADER -->
    <div class="doc-title">Social & Media Risk Monitoring Report</div>
    <div class="doc-subtitle">Daily Risk Assessment & Corporate Reputation Intelligence — Upper Trishuli-1</div>

    <!-- METADATA & SCOPE -->
    <div class="scope-box">
        <div class="scope-row"><span class="scope-label">Reporting Date:</span>
            {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}</div>
        <div class="scope-row"><span class="scope-label">Geographical Scope:</span> Nepal (Rasuwa, Nuwakot,
            Trishuli/Bhotekoshi Corridor)</div>
        <div class="scope-row"><span class="scope-label">Monitored Entities:</span> Upper Trishuli-1 (UT-1),
            Rasuwagadhi, Rasuwa Bhotekoshi, POWERCHINA, SINOHYDRO, Bureau 6, Bureau 7</div>
    </div>

    <!-- EXECUTIVE STATUS KPI BAR -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-cell" style="width: 25%; border-left: 4px solid #1e3a8a;">
                <span class="kpi-title">Overall Risk Status</span>
                <span class="kpi-num"
                    style="color: {{ $redCount > 0 ? '#dc2626' : ($yellowCount > 0 ? '#d97706' : '#16a34a') }};">
                    {{ $highestRisk }}
                </span>
            </td>
            <td class="kpi-cell" style="width: 25%;">
                <span class="kpi-title">Total Mentions</span>
                <span class="kpi-num" style="color: #0f172a;">{{ $mentions->count() }}</span>
            </td>
            <td class="kpi-cell" style="width: 25%;">
                <span class="kpi-title">Critical (Red)</span>
                <span class="kpi-num" style="color: #dc2626;">{{ $redCount }}</span>
            </td>
            <td class="kpi-cell" style="width: 25%;">
                <span class="kpi-title">Elevated / Watch</span>
                <span class="kpi-num" style="color: #d97706;">{{ $yellowCount }}</span>
            </td>
        </tr>
    </table>

    <!-- EXECUTIVE ASSESSMENT SUMMARY -->
    <div class="exec-box">
        <strong style="color: #0f172a; text-transform: uppercase; font-size: 10px;">Executive Assessment & Intelligence
            Summary</strong>
        <p style="margin: 4px 0 0 0; text-align: justify;">
            @if ($redCount > 0)
                <strong>CRITICAL ALERT:</strong> {{ $redCount }} verified high-risk event(s) recorded involving
                direct protests, organized halts, or mainstream exposés. Immediate corporate stakeholder escalation is
                required.
            @elseif($yellowCount > 0)
                <strong>ELEVATED AWARENESS:</strong> Background discussions, humanitarian concerns, and tunnel search
                questions active. No physical obstructions, organized boycott campaigns, or corporate targeting verified
                today.
            @else
                <strong>NORMAL BASELINE:</strong> Public discussions remain routine, non-escalatory, or constructive. No
                verified risk triggers or negative corporate mobilization detected against project contractors.
            @endif
        </p>
    </div>

    <!-- SECTION 1: HIGH & ELEVATED PRIORITY ITEMS -->
    <div class="section-bar">Priority Items & Risk Interpretation</div>

    @php
        $priorityItems = $mentions->filter(fn($m) => !str_starts_with(strtoupper($m->risk_tier ?? ''), 'GREEN'));
    @endphp

    @forelse($priorityItems as $item)
        @php
            $tier = strtoupper($item->risk_tier ?? 'YELLOW');
            $badgeColor = str_starts_with($tier, 'RED') ? 'badge-red' : 'badge-yellow';
        @endphp
        <div class="finding-card">
            <div class="finding-card-head">
                <span class="finding-title">{{ $item->clean_display_title }}</span>
                <span class="badge {{ $badgeColor }} badge-right">{{ $item->risk_tier }}</span>
            </div>
            <div class="finding-card-body">
                <div>
                    <strong>Description:</strong> {{ $item->clean_display_content }}
                </div>
                @if ($item->risk_reason)
                    <div style="margin-top: 5px; color: #334155;">
                        <strong style="color: #0f172a;">Risk Interpretation:</strong> {{ $item->risk_reason }}
                    </div>
                @endif
                <div style="margin-top: 6px; font-size: 8.5px; color: #64748b;">
                    Source: <strong>{{ strtoupper($item->source ?: 'NEWS') }}</strong>
                    @if ($item->reach)
                        | Est. Reach: <strong>{{ number_format($item->reach) }}</strong>
                    @endif
                    @if ($item->url)
                        | <a href="{{ $item->url }}" class="btn-link">View Source Article ↗</a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div
            style="padding: 10px; border: 1px solid #cbd5e1; background: #f8fafc; color: #166534; margin-bottom: 12px;">
            ✓ No critical or elevated risk items recorded in this monitoring period.
        </div>
    @endforelse

    <!-- PAGE BREAK FOR FULL STREAM TABLE -->
    <div class="page-break"></div>

    <!-- SECTION 2: VERIFIED MEDIA REPORTS STREAM -->
    <div class="section-bar">Verified Media Reports & Public Content Stream</div>

    <table class="clean-table">
        <thead>
            <tr>
                <th style="width: 25%;">Headline / Topic</th>
                <th style="width: 50%;">Summary</th>
                <th style="width: 13%;">Sentiment</th>
                <th style="width: 12%; text-align: center;">Source</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($mentions as $m)
                @php
                    $sent = strtolower($m->sentiment ?? 'neutral');
                    $sentBadge =
                        $sent === 'positive' ? 'badge-green' : ($sent === 'negative' ? 'badge-red' : 'badge-neutral');
                @endphp
                <tr>
                    <td>
                        <strong>{{ Str::limit($m->clean_display_title, 70) }}</strong>
                    </td>
                    <td>
                        {{ Str::limit($m->clean_display_content, 140) }}
                    </td>
                    <td>
                        <span class="badge {{ $sentBadge }}">{{ strtoupper($m->sentiment ?: 'NEUTRAL') }}</span>
                    </td>
                    <td style="text-align: center;">
                        @if ($m->url)
                            <a href="{{ $m->url }}" class="btn-link">
                                {{ ucfirst($m->source ?: 'Link') }} ↗
                            </a>
                        @else
                            {{ ucfirst($m->source ?: '—') }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- SECTION 3: QUANTITATIVE SENTIMENT MATRIX -->
    <div class="section-bar">Cumulative Sentiment Breakdown</div>

    @php
        $total = max($mentions->count(), 1);
        $pos = $mentions->filter(fn($m) => strtolower($m->sentiment ?? '') === 'positive')->count();
        $neu = $mentions->filter(fn($m) => in_array(strtolower($m->sentiment ?? ''), ['neutral', '']))->count();
        $neg = $mentions->filter(fn($m) => strtolower($m->sentiment ?? '') === 'negative')->count();
    @endphp

    <table class="clean-table" style="width: 65%; margin-top: 6px; page-break-inside: avoid;">
        <thead>
            <tr>
                <th>Sentiment Classification</th>
                <th style="text-align: center;">Total Items</th>
                <th style="text-align: right;">Share</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge badge-green">POSITIVE</span> Routine progress, assistance, official reassurance
                </td>
                <td style="text-align: center;">{{ $pos }}</td>
                <td style="text-align: right;">{{ round(($pos / $total) * 100, 1) }}%</td>
            </tr>
            <tr>
                <td><span class="badge badge-neutral">NEUTRAL</span> General reports, diplomatic statements, weather
                    updates</td>
                <td style="text-align: center;">{{ $neu }}</td>
                <td style="text-align: right;">{{ round(($neu / $total) * 100, 1) }}%</td>
            </tr>
            <tr>
                <td><span class="badge badge-red">NEGATIVE</span> Disaster damage, missing workers, critical commentary
                </td>
                <td style="text-align: center;">{{ $neg }}</td>
                <td style="text-align: right;">{{ round(($neg / $total) * 100, 1) }}%</td>
            </tr>
            <tr style="font-weight: bold; background: #f1f5f9;">
                <td>Total Verified Media Reports</td>
                <td style="text-align: center;">{{ $mentions->count() }}</td>
                <td style="text-align: right;">100.0%</td>
            </tr>
        </tbody>
    </table>

</body>

</html>
