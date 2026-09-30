<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MEDIA & SOCIAL MEDIA MONITORING REPORT</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;600;700&display=swap');

        @page {
            margin: 36px 45px 40px 45px;
        }
        body {
            font-family: 'Noto Sans Devanagari', 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000000;
        }
        h2.title {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        p {
            margin: 0 0 10px 0;
            text-align: justify;
        }

        /* Fixed layout prevents column squishing */
        table.mentions-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 14px;
            page-break-inside: avoid; /* Prevents splitting a mention across two pages */
        }
        table.mentions-table th, table.mentions-table td {
            border: 1px solid #000000;
            padding: 6px 9px;
            vertical-align: top;
            font-size: 9.5pt;
            line-height: 1.35;
            word-wrap: break-word;
        }
        table.mentions-table th {
            font-weight: bold;
            text-align: left;
            background-color: #f8fafc;
        }
        .col-sn { 
            width: 7%; 
            text-align: center; 
            font-weight: bold;
            vertical-align: middle !important;
        }
        .col-part { 
            width: 18%; 
            font-weight: bold; 
        }
        .col-details { 
            width: 75%; 
        }

        .title-text {
            font-weight: bold;
        }
        .link-text {
            color: #0b57d0;
            text-decoration: underline;
            word-break: break-all;
        }

        .mention-wrapper {
            page-break-inside: avoid;
            margin-bottom: 12px;
        }

        h3.section-header {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 18px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        table.summary-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        table.summary-table th, table.summary-table td {
            border: 1px solid #000000;
            padding: 6px 10px;
            font-size: 9.5pt;
        }
        table.summary-table th {
            text-align: left;
            font-weight: bold;
            background-color: #f8fafc;
        }
    </style>
</head>
<body>

    <h2 class="title">MEDIA & SOCIAL MEDIA MONITORING REPORT</h2>

    <p>
        A total of {{ $mentions->count() }} newly published and directly relevant media reports/posts, {{ $timeWindowText }}, concerning the monitored keywords/project/incident were identified and verified through publicly accessible sources. Details are as follows:
    </p>

    <!-- Header Table -->
    <table class="mentions-table" style="margin-bottom: -1px;">
        <thead>
            <tr>
                <th class="col-sn">SN</th>
                <th class="col-part">Particulars</th>
                <th class="col-details">Details</th>
            </tr>
        </thead>
    </table>

    <!-- Each Mention is an unbroken block that will never split across page breaks -->
    @forelse($mentions as $index => $m)
        <div class="mention-wrapper">
            <table class="mentions-table">
                <tbody>
                    <tr>
                        <td rowspan="4" class="col-sn">{{ $index + 1 }}</td>
                        <td class="col-part">Title</td>
                        <td class="col-details title-text">{{ $m->title ?: ($m->snippet ? \Illuminate\Support\Str::limit($m->snippet, 100) : 'UNTITLED REPORT') }}</td>
                    </tr>
                    <tr>
                        <td class="col-part">Content</td>
                        <td class="col-details" style="text-align: justify;">{{ $m->content ?: $m->snippet }}</td>
                    </tr>
                    <tr>
                        <td class="col-part">Sentiment</td>
                        <td class="col-details">{{ ucfirst($m->sentiment ?: 'Neutral') }}</td>
                    </tr>
                    <tr>
                        <td class="col-part">Link</td>
                        <td class="col-details">
                            @if($m->url)
                                <a href="{{ $m->url }}" class="link-text">{{ $m->url }}</a>
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <table class="mentions-table">
            <tbody>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 18px;">No new verified mentions published in this time window.</td>
                </tr>
            </tbody>
        </table>
    @endforelse

    <div style="page-break-inside: avoid;">
        <h3 class="section-header">CUMULATIVE CURRENT-DAY ASSESSMENT</h3>
        <p>
            {{ $asOfText }}, a cumulative total of {{ $mentions->count() }} newly published media reports/posts had been identified in the current monitoring exercise, comprising {{ $positiveCount }} positive report{{ $positiveCount == 1 ? '' : 's' }}, {{ $neutralCount }} neutral report{{ $neutralCount == 1 ? '' : 's' }} and {{ $negativeCount }} negative report{{ $negativeCount == 1 ? '' : 's' }} as illustrated in the below table. Of these, {{ $positiveCount }} were positive, accounting for {{ $positivePct }}%, {{ $neutralCount }} were neutral, accounting for {{ $neutralPct }}% and {{ $negativeCount }} were negative accounting for {{ $negativePct }}%.
        </p>

        <p>
            Dominant themes are the continuing missing-person and body-identification process, the humanitarian and rehabilitation situation, and international community relief support.
        </p>

        <p>
            <em>No new material discussion theme or coordinated company-targeted mobilisation was verified on Facebook, Instagram, TikTok, YouTube, X/Twitter or LinkedIn {{ $monitoringWindowText }}. Publicly indexed social-media activity remained limited; this does not establish absence of posts in private groups, closed accounts, non-indexed comments or platform feeds that are not accessible through open-web monitoring.</em>
        </p>

        <h3 class="section-header">SENTIMENT SUMMARY: TODAY'S VERIFIED REPORTS</h3>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Sentiment</th>
                    <th style="width: 25%; text-align: center;">Number</th>
                    <th style="width: 25%; text-align: center;">Share</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Positive</td>
                    <td style="text-align: center;">{{ $positiveCount }}</td>
                    <td style="text-align: center;">{{ $positivePct }}%</td>
                </tr>
                <tr>
                    <td>Neutral</td>
                    <td style="text-align: center;">{{ $neutralCount }}</td>
                    <td style="text-align: center;">{{ $neutralPct }}%</td>
                </tr>
                <tr>
                    <td>Negative</td>
                    <td style="text-align: center;">{{ $negativeCount }}</td>
                    <td style="text-align: center;">{{ $negativePct }}%</td>
                </tr>
                <tr style="font-weight: bold; background-color: #f8fafc;">
                    <td>Total</td>
                    <td style="text-align: center;">{{ $mentions->count() }}</td>
                    <td style="text-align: center;">100%</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>