<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Awario Mentions Report</title>
    <!-- Modern Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        /* ==========================================================================
           Awario Mentions Report - High Density (Minimized Gaps) CSS
           ========================================================================== */
        :root {
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;

            --bg-page: #f8fafc;
            --surface: #ffffff;
            --surface-hover: #f1f5f9;
            --border: #e2e8f0;
            --border-subtle: #edf2f7;

            --text-main: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --text-light: #94a3b8;

            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: #eff6ff;
            --primary-border: #bfdbfe;

            --tier-green-bg: #f0fdf4;
            --tier-green-border: #22c55e;
            --tier-green-badge: #15803d;
            --tier-green-badge-bg: #dcfce7;

            --tier-yellow-bg: #fefce8;
            --tier-yellow-border: #eab308;
            --tier-yellow-badge: #854d0e;
            --tier-yellow-badge-bg: #fef08a;

            --tier-red-bg: #fff1f2;
            --tier-red-border: #f43f5e;
            --tier-red-badge: #be123c;
            --tier-red-badge-bg: #ffe4e6;

            --radius-sm: 4px;
            --radius-md: 6px;
            --radius-lg: 8px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-page);
            color: var(--text-body);
            padding: 12px 16px 28px;
            font-size: 12px;
            line-height: 1.35;
            -webkit-font-smoothing: antialiased;
        }

        /* Page Heading */
        h2 {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        h2::before {
            content: "";
            display: inline-block;
            width: 5px;
            height: 16px;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border-radius: 3px;
        }

        /* Filter Toolbar */
        .filters {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 8px 12px;
            margin-bottom: 8px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .filters .filter-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .filters label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }

        .filters select,
        .filters input[type="date"],
        .filters input[type="text"] {
            font-family: var(--font-sans);
            padding: 3px 8px;
            font-size: 12px;
            height: 29px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: #fff;
            color: var(--text-main);
            outline: none;
            transition: border-color 0.15s ease;
        }

        .filters select:focus,
        .filters input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
        }

        .filters button,
        .filters a {
            padding: 3px 10px;
            font-size: 12px;
            height: 29px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }

        .filters button[type="submit"] {
            background: var(--primary);
            color: #ffffff;
        }

        .filters button[type="submit"]:hover {
            background: var(--primary-hover);
        }

        .filters a {
            background: #f1f5f9;
            color: var(--text-body);
            border: 1px solid var(--border);
        }

        .filters a:hover {
            background: #e2e8f0;
            color: var(--text-main);
        }

        /* Results Counter */
        .results-count {
            margin: 4px 0 6px 2px;
            color: var(--text-muted);
            font-size: 11.5px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .results-count::before {
            content: "";
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--primary);
        }

        /* Table Structure */
        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            table-layout: fixed;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        th,
        td {
            border-bottom: 1px solid var(--border);
            border-right: 1px solid var(--border-subtle);
            padding: 6px 9px;
            text-align: left;
            vertical-align: top;
            font-size: 12px;
            line-height: 1.35;
        }

        th:last-child,
        td:last-child {
            border-right: none;
        }

        tr:last-child td {
            border-bottom: none;
        }

        th {
            background: #f8fafc;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Column Width Distribution */
        .col-id {
            width: 5%;
            font-family: monospace;
            font-weight: 600;
            color: var(--text-light);
        }

        .col-date {
            width: 9%;
            font-size: 11px;
            color: var(--text-muted);
            font-family: monospace;
            white-space: nowrap;
        }

        .col-title {
            width: 19%;
            font-weight: 600;
            color: var(--text-main);
        }

        .col-meta {
            width: 12%;
        }

        .col-risk {
            width: 25%;
        }

        .col-summary {
            width: 25%;
        }

        .col-link {
            width: 5%;
            text-align: center;
        }

        /* Row Tier Styles */
        tr.tier-GREEN {
            background-color: var(--tier-green-bg);
        }

        tr.tier-GREEN td:first-child {
            border-left: 3px solid var(--tier-green-border);
        }

        tr.tier-GREEN:hover {
            background-color: #e8f9ed;
        }

        tr.tier-YELLOW {
            background-color: var(--tier-yellow-bg);
        }

        tr.tier-YELLOW td:first-child {
            border-left: 3px solid var(--tier-yellow-border);
        }

        tr.tier-YELLOW:hover {
            background-color: #fef9c3;
        }

        tr.tier-RED {
            background-color: var(--tier-red-bg);
        }

        tr.tier-RED td:first-child {
            border-left: 3px solid var(--tier-red-border);
        }

        tr.tier-RED:hover {
            background-color: #fee2e2;
        }

        /* Two-Line Clamping for High Screen Density */
        /* --- Text Clamping --- */
        .clamp {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            cursor: pointer;
            line-height: 1.35;
            transition: color 0.15s ease;
            position: relative;
        }

        .clamp.expanded {
            -webkit-line-clamp: unset;
            display: block;
        }

        .expand-hint {
            font-size: 10.5px;
            color: var(--primary);
            font-weight: 600;
            cursor: pointer;
            margin-top: 1px;
            display: none;
            user-select: none;
        }

        .expand-hint:hover {
            text-decoration: underline;
        }

        /* Compact Metadata & Badges */
        .meta-line {
            margin-bottom: 2px;
            font-size: 11.5px;
            line-height: 1.25;
        }

        .meta-line:last-child {
            margin-bottom: 0;
        }

        .meta-label {
            font-weight: 700;
            color: var(--text-muted);
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            margin-right: 3px;
        }

        .source-badge {
            display: inline-block;
            padding: 0px 5px;
            border-radius: var(--radius-sm);
            background: #ffffff;
            border: 1px solid var(--border);
            font-weight: 600;
            font-size: 10.5px;
            color: var(--text-body);
        }

        .reach-badge {
            font-weight: 600;
            font-family: monospace;
            color: var(--text-main);
            font-size: 11px;
        }

        .col-risk strong {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 2px;
        }

        tr.tier-GREEN .col-risk strong {
            background-color: var(--tier-green-badge-bg);
            color: var(--tier-green-badge);
            border: 1px solid #bbf7d0;
        }

        tr.tier-YELLOW .col-risk strong {
            background-color: var(--tier-yellow-badge-bg);
            color: var(--tier-yellow-badge);
            border: 1px solid #fde047;
        }

        tr.tier-RED .col-risk strong {
            background-color: var(--tier-red-badge-bg);
            color: var(--tier-red-badge);
            border: 1px solid #fecdd3;
        }

        .sentiment-pill {
            display: inline-block;
            padding: 0px 5px;
            border-radius: 8px;
            font-size: 9.5px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .sentiment-positive {
            background-color: #dcfce7;
            color: #15803d;
        }

        .sentiment-negative {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .sentiment-neutral {
            background-color: #f1f5f9;
            color: #475569;
        }

        /* Action Link Button */
        .col-link a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 2px 7px;
            height: 24px;
            border-radius: var(--radius-sm);
            background-color: var(--primary-light);
            color: var(--primary);
            border: 1px solid var(--primary-border);
            text-decoration: none;
            font-weight: 600;
            font-size: 11px;
            transition: all 0.15s ease;
        }

        .col-link a:hover {
            background-color: var(--primary);
            color: #ffffff;
        }

        /* Laravel Pagination Links */
        .pagination {
            margin: 8px 0 0;
            display: flex;
            justify-content: flex-end;
        }

        .pagination nav {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .pagination svg {
            width: 14px;
            height: 14px;
        }

        .pagination a,
        .pagination span[aria-current="page"] span,
        .pagination span.relative {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 28px;
            padding: 0 8px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            text-decoration: none;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--text-body);
        }

        .pagination a:hover {
            background: var(--surface-hover);
            border-color: #cbd5e1;
        }

        .pagination span[aria-current="page"] span {
            background: var(--primary) !important;
            color: #fff !important;
            border-color: var(--primary) !important;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <h2>Awario Mentions Report</h2>

    <form method="GET" action="{{ route('mentions.report') }}" class="filters">
        <div class="filter-group">
            <label for="filter-date-from">From:</label>
            <input type="date" id="filter-date-from" name="date_from" value="{{ request('date_from') }}">
        </div>

        <div class="filter-group">
            <label for="filter-date-to">To:</label>
            <input type="date" id="filter-date-to" name="date_to" value="{{ request('date_to') }}">
        </div>

        <div class="filter-group">
            <label for="filter-tier">Tier:</label>
            <select id="filter-tier" name="risk_tier">
                <option value="">All Tiers</option>
                @foreach ($tiers as $tier)
                    <option value="{{ $tier }}" {{ request('risk_tier') == $tier ? 'selected' : '' }}>
                        {{ $tier }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label for="filter-source">Source:</label>
            <select id="filter-source" name="source">
                <option value="">All Sources</option>
                @foreach ($sources as $source)
                    <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                        {{ $source }}
                    </option>
                @endforeach
            </select>
        </div>

        <label>Reach:
            <select name="min_reach">
                <option value="">All</option>
                <option value="1" {{ request('min_reach') == '1' ? 'selected' : '' }}>&gt; 0 (Has Reach)</option>
                <option value="100" {{ request('min_reach') == '100' ? 'selected' : '' }}>100+</option>
                <option value="500" {{ request('min_reach') == '500' ? 'selected' : '' }}>500+</option>
                <option value="1000" {{ request('min_reach') == '1000' ? 'selected' : '' }}>1,000+</option>
                <option value="5000" {{ request('min_reach') == '5000' ? 'selected' : '' }}>5,000+</option>
                <option value="10000" {{ request('min_reach') == '10000' ? 'selected' : '' }}>10,000+</option>
            </select>
        </label>

        <div class="filter-group">
            <label for="filter-per-page">Per Page:</label>
            <select id="filter-per-page" name="per_page">
                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            </select>
        </div>

        <button type="submit">Filter</button>
        <a href="{{ route('mentions.report') }}">Reset</a>
    </form>

    <div class="results-count">
        Showing {{ $mentions->firstItem() ?? 0 }}–{{ $mentions->lastItem() ?? 0 }} of {{ $mentions->total() }}
        mentions
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-id">ID</th>
                <th class="col-date">Date</th>
                <th class="col-title">Title</th>
                <th class="col-meta">Source / Reach</th>
                <th class="col-risk">Risk Tier / Reason</th>
                <th class="col-summary">Summary (incl. Sentiment)</th>
                <th class="col-link">Link</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($mentions as $m)
                @php
                    $baseTier = explode(' ', $m->risk_tier ?? '')[0] ?? '';
                @endphp
                <tr class="tier-{{ $baseTier }}">
                    <td class="col-id">#{{ $m->id }}</td>
                    <td class="col-date">{{ $m->mentioned_at?->format('Y-m-d H:i') }}</td>

                    <td class="col-title">
                        <div class="clamp" onclick="toggleClamp(this)">{{ $m->title }}</div>
                        <div class="expand-hint" onclick="toggleClamp(this.previousElementSibling)">show more</div>
                    </td>

                    <td class="col-meta">
                        <div class="meta-line"><span class="meta-label">Source:</span> <span
                                class="source-badge">{{ $m->source }}</span></div>
                        <div class="meta-line"><span class="meta-label">Reach:</span> <span
                                class="reach-badge">{{ is_numeric($m->reach) ? number_format($m->reach) : $m->reach ?? '—' }}</span>
                        </div>
                    </td>

                    <td class="col-risk">
                        <div class="meta-line"><strong>{{ $m->risk_tier ?? '—' }}</strong></div>
                        <div class="clamp" onclick="toggleClamp(this)">{{ $m->risk_reason }}</div>
                        <div class="expand-hint" onclick="toggleClamp(this.previousElementSibling)">show more</div>
                    </td>

                    <td class="col-summary">
                        <div class="meta-line"><span class="meta-label">Sentiment:</span>
                            <span class="sentiment-pill sentiment-{{ strtolower($m->sentiment ?? 'neutral') }}">
                                {{ $m->sentiment ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="clamp" onclick="toggleClamp(this)">{{ $m->content }}</div>
                        <div class="expand-hint" onclick="toggleClamp(this.previousElementSibling)">show more</div>
                    </td>

                    <td class="col-link">
                        <a href="{{ $m->url }}" target="_blank" rel="noopener noreferrer">
                            Open
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round" style="margin-left: 2px;">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                <polyline points="15 3 21 3 21 9"></polyline>
                                <line x1="10" y1="14" x2="21" y2="3"></line>
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"
                        style="text-align: center; padding: 32px 16px; color: var(--text-muted); font-size: 13px;">
                        No mentions found matching the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination">
        {{ $mentions->appends(request()->query())->links() }}
    </div>

    <script>
        function toggleClamp(el) {
            if (!el) return;
            var isExpanded = el.classList.toggle('expanded');
            var hint = el.nextElementSibling;
            if (hint && hint.classList.contains('expand-hint')) {
                hint.textContent = isExpanded ? 'show less' : 'show more';
            }
        }

        // Only show "show more" if the content is clamped and overflowing
        document.querySelectorAll('.clamp').forEach(function(el) {
            if (el.scrollHeight > (el.clientHeight + 2)) {
                var hint = el.nextElementSibling;
                if (hint && hint.classList.contains('expand-hint')) {
                    hint.style.display = 'inline-block';
                }
            }
        });
    </script>
</body>

</html>
