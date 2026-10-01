@extends('layouts.app')
@section('title', 'Executive Dashboard')
@section('header_title', 'Operational & Media Risk Dashboard')

@push('styles')
    <style>
        /* Executive Hero Banner */
        .hero-risk-card {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 24px 28px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .hero-risk-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
        }

        .hero-tier-RED {
            border-color: #fecdd3;
            background: #fff1f2 !important;
        }

        .hero-tier-RED::before {
            background: #f43f5e;
        }

        .hero-tier-YELLOW {
            border-color: #fef08a;
            background: #fefce8 !important;
        }

        .hero-tier-YELLOW::before {
            background: #eab308;
        }

        .hero-tier-GREEN {
            border-color: #bbf7d0;
            background: #f0fdf4 !important;
        }

        .hero-tier-GREEN::before {
            background: #22c55e;
        }

        /* Metric Cards */
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px 20px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            text-decoration: none;
            display: block;
            color: inherit;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .stat-num {
            font-family: var(--font-mono);
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.1;
        }

        /* Priority Attention Cards with tinted backgrounds */
        .attention-card {
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 10px;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #cbd5e1;
        }

        .attention-card.tier-RED {
            background-color: #fff1f2 !important;
            border-color: #fecdd3 !important;
            border-left: 4px solid #f43f5e !important;
        }

        .attention-card.tier-YELLOW {
            background-color: #fefce8 !important;
            border-color: #fef08a !important;
            border-left: 4px solid #eab308 !important;
        }

        .attention-title {
            font-size: 0.88rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.35;
        }

        /* Row Background Colors for Latest Ingested Mentions Table */
        .table-colored-rows tbody tr.tier-GREEN td {
            background-color: #f0fdf4 !important;
        }

        .table-colored-rows tbody tr.tier-GREEN td:first-child {
            border-left: 4px solid #22c55e !important;
        }

        .table-colored-rows tbody tr.tier-YELLOW td {
            background-color: #fefce8 !important;
        }

        .table-colored-rows tbody tr.tier-YELLOW td:first-child {
            border-left: 4px solid #eab308 !important;
        }

        .table-colored-rows tbody tr.tier-RED td {
            background-color: #fff1f2 !important;
        }

        .table-colored-rows tbody tr.tier-RED td:first-child {
            border-left: 4px solid #f43f5e !important;
        }

        .table-colored-rows tbody tr:hover td {
            filter: brightness(0.97);
        }

        /* Badges */
        .tag-source {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: #ffffff;
            color: #475569;
            padding: 2px 7px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }

        .tag-tier {
            font-size: 0.72rem;
            font-weight: 700;
            font-family: var(--font-mono);
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .tag-tier.RED {
            background: #ffe4e6;
            color: #be123c;
            border: 1px solid #fecdd3;
        }

        .tag-tier.YELLOW {
            background: #fef08a;
            color: #854d0e;
            border: 1px solid #fde047;
        }

        .tag-tier.GREEN {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
    </style>
@endpush

@section('content')
    @php
        $overallBase = explode(' ', $overallTier)[0] ?? 'GREEN';
        $totalCount = max($totalMentions, 1);
        $redPct = round(($red / $totalCount) * 100);
        $yellowHighPct = round(($yellowHigh / $totalCount) * 100);
        $yellowPct = round(($yellow / $totalCount) * 100);
        $greenPct = round(($green / $totalCount) * 100);
    @endphp

    <!-- 1. Executive Status Hero Banner -->
    <div class="hero-risk-card hero-tier-{{ $overallBase }} mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="text-uppercase fw-bold text-muted small" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                        Current Project Risk Level
                    </span>
                    <span class="text-muted">·</span>
                    <span class="text-muted small">Updated
                        {{ $lastSync ? \Carbon\Carbon::parse($lastSync)->diffForHumans() : 'recently' }}</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <h1 class="mb-0 fw-bold text-dark" style="font-size: 1.85rem; letter-spacing: -0.02em;">
                        {{ $overallTier }}
                    </h1>
                    <span class="tag-tier {{ $overallBase }}">
                        @if ($overallBase === 'RED')
                            Critical Escalation
                        @elseif(str_contains($overallTier, 'HIGH'))
                            Elevated Alert
                        @elseif($overallBase === 'YELLOW')
                            Active Monitoring
                        @else
                            Normal Operations
                        @endif
                    </span>
                </div>
                <p class="text-muted mb-0 mt-2" style="font-size: 0.9rem; max-width: 650px;">
                    @if ($overallBase === 'RED')
                        Verified protest call or critical reputational exposure active. Immediate 15-minute stakeholder
                        notification protocol applies.
                    @elseif(str_contains($overallTier, 'HIGH'))
                        High-sensitivity items or significant public questions detected. Continued operational watch
                        required.
                    @elseif($overallBase === 'YELLOW')
                        Localized community discussions and minor grievances detected. No verified calls to obstruction.
                    @else
                        Routine reporting, positive institutional reviews, and low-engagement discussions across monitored
                        media.
                    @endif
                </p>
            </div>

            <div class="d-flex align-items-center gap-3 border-start ps-lg-4 border-slate-200">
                <div class="text-center px-2">
                    <div class="text-muted small fw-semibold">Total Tracked</div>
                    <div class="stat-num text-dark">{{ number_format($totalMentions) }}</div>
                </div>
                @if (isset($totalReach) && $totalReach > 0)
                    <div class="text-center px-2 border-start border-slate-200">
                        <div class="text-muted small fw-semibold">Est. Reach</div>
                        <div class="stat-num text-primary">
                            {{ $totalReach > 1000 ? round($totalReach / 1000) . 'k' : $totalReach }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 2. KPI Severity Cards Grid -->
    <div class="row g-3 mb-4">
        <!-- RED -->
        <div class="col-6 col-md-3">
            <a href="{{ route('mentions.report') }}?risk_tier=RED" class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold small text-danger">RED (Critical)</span>
                    <span class="badge bg-danger-subtle text-danger font-mono-num">{{ $redPct }}%</span>
                </div>
                <div class="stat-num text-danger">{{ $red }}</div>
                <div class="text-muted small mt-1" style="font-size:0.75rem;">Protest calls & exposés</div>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-danger" style="width: {{ $redPct }}%"></div>
                </div>
            </a>
        </div>

        <!-- YELLOW - HIGH -->
        <div class="col-6 col-md-3">
            <a href="{{ route('mentions.report') }}?risk_tier=YELLOW" class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold small text-warning" style="color:#d97706 !important;">YELLOW — HIGH</span>
                    <span class="badge bg-warning-subtle text-warning font-mono-num"
                        style="color:#b45309 !important;">{{ $yellowHighPct }}%</span>
                </div>
                <div class="stat-num text-warning" style="color:#d97706 !important;">{{ $yellowHigh }}</div>
                <div class="text-muted small mt-1" style="font-size:0.75rem;">High sensitivity / inquiries</div>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-warning"
                        style="width: {{ $yellowHighPct }}%; background:#f59e0b !important;"></div>
                </div>
            </a>
        </div>

        <!-- YELLOW -->
        <div class="col-6 col-md-3">
            <a href="{{ route('mentions.report') }}?risk_tier=YELLOW" class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold small text-warning" style="color:#eab308 !important;">YELLOW</span>
                    <span class="badge bg-warning-subtle font-mono-num"
                        style="color:#854d0e !important;">{{ $yellowPct }}%</span>
                </div>
                <div class="stat-num" style="color:#ca8a04;">{{ $yellow }}</div>
                <div class="text-muted small mt-1" style="font-size:0.75rem;">Negative comments/rumors</div>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar" style="width: {{ $yellowPct }}%; background:#eab308;"></div>
                </div>
            </a>
        </div>

        <!-- GREEN -->
        <div class="col-6 col-md-3">
            <a href="{{ route('mentions.report') }}?risk_tier=GREEN" class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold small text-success">GREEN (Normal)</span>
                    <span class="badge bg-success-subtle text-success font-mono-num">{{ $greenPct }}%</span>
                </div>
                <div class="stat-num text-success">{{ $green }}</div>
                <div class="text-muted small mt-1" style="font-size:0.75rem;">Progress & positive news</div>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: {{ $greenPct }}%"></div>
                </div>
            </a>
        </div>
    </div>

    <!-- 3. Risk Trend Chart & Priority Triage Rail -->
    <div class="row g-4 mb-4">
        <!-- Trend Line Chart -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">14-Day Trajectory</h6>
                            <small class="text-muted">Daily volume across critical, elevated, and normal mentions</small>
                        </div>
                        <div class="d-flex align-items-center gap-3 small">
                            <span class="d-flex align-items-center gap-1.5"><span
                                    style="width:8px;height:8px;border-radius:50%;background:#e11d48;display:inline-block;"></span>
                                Red</span>
                            <span class="d-flex align-items-center gap-1.5"><span
                                    style="width:8px;height:8px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
                                Yellow</span>
                            <span class="d-flex align-items-center gap-1.5"><span
                                    style="width:8px;height:8px;border-radius:50%;background:#10b981;display:inline-block;"></span>
                                Green</span>
                        </div>
                    </div>
                    <div style="height: 250px; position: relative;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Needs Attention Rail -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #e2e8f0;">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">Priority Attention Queue</h6>
                        <span class="badge bg-danger-subtle text-danger font-mono-num">{{ count($priorityItems) }}
                            active</span>
                    </div>

                    <div class="overflow-y-auto flex-grow-1" style="max-height: 260px;">
                        @forelse($priorityItems as $item)
                            @php $itemBase = explode(' ', $item->risk_tier)[0]; @endphp
                            <div class="attention-card tier-{{ $itemBase }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="tag-tier {{ $itemBase }}"
                                        style="font-size:0.68rem;">{{ $item->risk_tier }}</span>
                                    <span class="text-muted font-mono-num" style="font-size:0.75rem;">
                                        {{ $item->mentioned_at ? \Carbon\Carbon::parse($item->mentioned_at)->timezone('Asia/Kathmandu')->format('M d, H:i') : 'Recent' }}
                                    </span>
                                </div>
                                <div class="attention-title mb-1">
                                    {{ Str::limit($item->title, 75) }}
                                </div>
                                @if ($item->risk_reason)
                                    <div class="text-muted small" style="font-size:0.75rem; line-height: 1.3;">
                                        {{ Str::limit($item->risk_reason, 90) }}
                                    </div>
                                @endif
                                @if ($item->url)
                                    <div class="mt-2 text-end">
                                        <a href="{{ $item->url }}" target="_blank"
                                            class="small text-primary text-decoration-none fw-semibold"
                                            style="font-size:0.75rem;">
                                            View source <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-shield-check text-success" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0 small">No critical or high-risk items requiring attention.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="pt-3 mt-auto border-top text-end">
                        <a href="{{ route('mentions.report') }}?risk_tier=RED"
                            class="small text-dark fw-bold text-decoration-none">
                            Open Full Incident Report →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Media Sources Breakdown & Recent Live Ingestion Stream -->
    @if (isset($recentMentions) && count($recentMentions) > 0)
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0">Latest Ingested Mentions</h6>
                            <a href="{{ route('mentions.report') }}"
                                class="small text-primary text-decoration-none fw-semibold">View All →</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-colored-rows align-middle mb-0"
                                style="font-size: 0.85rem; border-collapse: separate; border-spacing: 0;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 14%; padding: 10px 12px;">Date</th>
                                        <th style="width: 14%; padding: 10px 12px;">Source / Reach</th>
                                        <th style="width: 48%; padding: 10px 12px;">Headline / Excerpt</th>
                                        <th style="width: 16%; padding: 10px 12px;">Risk Tier</th>
                                        <th style="width: 8%; text-align:right; padding: 10px 12px;">Link</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentMentions as $rm)
                                        @php
                                            $rmBase = explode(' ', $rm->risk_tier ?? '')[0] ?? 'GREEN';
                                        @endphp
                                        <tr class="tier-{{ $rmBase }}">
                                            <td class="font-mono-num text-muted" style="padding: 10px 12px;">
                                                <div>
                                                    {{ $rm->mentioned_at ? \Carbon\Carbon::parse($rm->mentioned_at)->timezone('Asia/Kathmandu')->format('Y-m-d') : '—' }}
                                                </div>
                                                <div style="font-size: 0.72rem; color: #94a3b8;">
                                                    {{ $rm->mentioned_at ? \Carbon\Carbon::parse($rm->mentioned_at)->timezone('Asia/Kathmandu')->format('H:i') : '' }}
                                                </div>
                                            </td>
                                            <td style="padding: 10px 12px;">
                                                <span class="tag-source">{{ $rm->source ?? 'news' }}</span>
                                                @if ($rm->reach)
                                                    <div class="text-muted font-mono-num mt-1" style="font-size:0.72rem;">
                                                        {{ number_format($rm->reach) }}</div>
                                                @endif
                                            </td>
                                            <td style="padding: 10px 12px;">
                                                <div class="fw-semibold text-dark">{{ Str::limit($rm->title, 65) }}</div>
                                                <div class="text-muted small" style="font-size: 0.76rem;">
                                                    {{ Str::limit($rm->snippet, 85) }}</div>
                                            </td>
                                            <td style="padding: 10px 12px;">
                                                <span
                                                    class="tag-tier {{ $rmBase }}">{{ $rm->risk_tier ?? 'UNCLASSIFIED' }}</span>
                                            </td>
                                            <td style="text-align: right; padding: 10px 12px;">
                                                @if ($rm->url)
                                                    <a href="{{ $rm->url }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary py-1 px-2"
                                                        style="font-size:0.75rem; border-radius: 5px;">
                                                        Open <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('trendChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($trendLabels) !!},
                    datasets: [{
                            label: 'RED (Critical)',
                            data: {!! json_encode($trendRed) !!},
                            borderColor: '#e11d48',
                            backgroundColor: 'rgba(225, 29, 72, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        },
                        {
                            label: 'YELLOW (Elevated)',
                            data: {!! json_encode($trendYellow) !!},
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        },
                        {
                            label: 'GREEN (Normal)',
                            data: {!! json_encode($trendGreen) !!},
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: {
                                family: 'Plus Jakarta Sans',
                                size: 12,
                                weight: 'bold'
                            },
                            bodyFont: {
                                family: 'JetBrains Mono',
                                size: 12
                            },
                            padding: 10,
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'JetBrains Mono',
                                    size: 11
                                },
                                color: '#94a3b8'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                stepSize: 1,
                                font: {
                                    family: 'JetBrains Mono',
                                    size: 11
                                },
                                color: '#94a3b8'
                            }
                        }
                    }
                }
            });
        }
    </script>
@endpush
