@extends('layouts.app')

@section('title', 'Add a Mention')
@section('header_title', 'Manual Mention Intake')

@push('styles')
<style>
    .intake-container {
        max-width: 760px;
    }

    .notice-card {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 20px;
        color: #1e40af;
        font-size: 0.84rem;
        line-height: 1.45;
    }

    .form-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 28px 30px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    .field-label {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }

    .form-control, .form-select {
        font-size: 0.88rem;
        border-color: #cbd5e1;
        border-radius: 8px;
        padding: 9px 12px;
    }

    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .btn-check-link {
        background: #0f172a;
        color: #ffffff;
        border: none;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 9px 16px;
        border-radius: 8px;
        transition: background 0.15s ease;
        white-space: nowrap;
    }

    .btn-check-link:hover {
        background: #1e293b;
        color: #ffffff;
    }

    .status-box {
        font-size: 0.82rem;
        border-radius: 6px;
        padding: 8px 12px;
        margin-top: 10px;
        display: none;
    }
    .status-box.info { background: #f1f5f9; color: #334155; display: block; }
    .status-box.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; display: block; }
    .status-box.warning { background: #fef08a; color: #854d0e; border: 1px solid #fde047; display: block; }
    .status-box.danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecdd3; display: block; }

    .detected-source-badge {
        font-family: var(--font-mono);
        font-size: 0.72rem;
        font-weight: 700;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
        padding: 2px 7px;
        border-radius: 4px;
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
<div class="intake-container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-1" style="font-size: 1.25rem;">Add Mention Manually</h4>
            <p class="text-muted small mb-0">Log external content found on Facebook, Instagram, X/Twitter, or local blogs</p>
        </div>
        <a href="{{ route('mentions.report') }}" class="btn btn-sm btn-outline-secondary" style="font-size: 0.82rem;">
            ← Back to Report
        </a>
    </div>

    <!-- Info banner -->
    <div class="notice-card d-flex items-start gap-2.5">
        <i class="bi bi-info-circle-fill mt-0.5" style="font-size: 1rem;"></i>
        <div>
            Use this form for posts found outside automated feeds. Paste the exact URL below and click <strong>Check Link</strong> to fetch its title and description automatically.
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3 py-2.5 px-3 rounded-3" style="font-size: 0.86rem;">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 py-2.5 px-3 rounded-3" style="font-size: 0.86rem;">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div class="form-card">
        <form id="mentionForm" method="POST" action="{{ route('mentions.store') }}">
            @csrf

            <!-- 1. URL Section -->
            <div class="mb-3">
                <label class="field-label">Post or Article URL</label>
                <div class="d-flex gap-2">
                    <input type="text" id="url" name="url" class="form-control font-monospace"
                        placeholder="https://facebook.com/... or https://instagram.com/p/..." style="font-size: 0.84rem;" required>
                    <button type="button" id="checkBtn" class="btn-check-link d-flex align-items-center gap-1.5">
                        <i class="bi bi-search"></i>
                        <span>Check Link</span>
                    </button>
                </div>
                <div id="status" class="status-box"></div>
            </div>

            <!-- 2. Expandable Fields (Visible after Check Link) -->
            <div id="fields" style="display:none;" class="pt-3 border-top border-slate-200 mt-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-semibold">Review & Edit Details</span>
                    <span id="detectedSourceWrapper" style="display:none;">
                        <span class="text-muted small me-1">Detected:</span>
                        <span id="detectedSource" class="detected-source-badge"></span>
                    </span>
                </div>

                <div class="mb-3">
                    <label class="field-label">Headline / Title</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="Short descriptive title">
                </div>

                <div class="mb-4">
                    <label class="field-label">Content Excerpt / What does it say?</label>
                    <textarea id="snippet" name="snippet" class="form-control" rows="4" placeholder="Paste exact post wording or main summary..."></textarea>
                    <div class="form-text small" style="font-size: 0.75rem; color: #64748b;">
                        The AI classifier will analyze this text to assign the appropriate Risk Tier (Green/Yellow/Red).
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-2">
                    <span class="text-muted small" style="font-size: 0.78rem;">
                        <i class="bi bi-shield-check text-success me-1"></i> Will queue for auto-classification
                    </span>
                    <button type="submit" class="btn btn-primary fw-semibold px-4 py-2 d-flex align-items-center gap-1.5" style="border-radius: 8px; font-size: 0.88rem;">
                        <i class="bi bi-save"></i>
                        <span>Save This Mention</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    document.getElementById('checkBtn').addEventListener('click', async function() {
        const urlInput = document.getElementById('url').value.trim();
        const statusEl = document.getElementById('status');
        const fieldsEl = document.getElementById('fields');
        const checkBtn = document.getElementById('checkBtn');

        if (!urlInput) {
            statusEl.className = 'status-box warning';
            statusEl.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> Please paste a link first.';
            return;
        }

        checkBtn.disabled = true;
        checkBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Checking...';
        statusEl.className = 'status-box info';
        statusEl.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Connecting to URL and fetching metadata...';
        fieldsEl.style.display = 'none';

        try {
            const res = await fetch("{{ route('mentions.preview') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ url: urlInput }),
            });
            const data = await res.json();

            checkBtn.disabled = false;
            checkBtn.innerHTML = '<i class="bi bi-search"></i> Check Link';

            if (data.exists) {
                statusEl.className = 'status-box warning';
                statusEl.innerHTML = '⚠️ <strong>This link is already saved</strong> in the database. No need to add it again.';
                return;
            }

            if (data.source) {
                document.getElementById('detectedSource').textContent = data.source;
                document.getElementById('detectedSourceWrapper').style.display = 'inline-block';
            }

            if (data.title) {
                if (data.title_may_be_just_a_name) {
                    statusEl.className = 'status-box warning';
                    statusEl.innerHTML = '⚠️ Found a title, but it looks like just a profile/page name. Please review and provide a clearer headline below.';
                } else {
                    statusEl.className = 'status-box success';
                    statusEl.innerHTML = '✅ Found details automatically! Please review below and click Save.';
                }
                document.getElementById('title').value = data.title;
                document.getElementById('snippet').value = data.snippet || '';
            } else {
                statusEl.className = 'status-box warning';
                statusEl.innerHTML = '⚠️ Could not fetch details automatically (page private or blocked). Please type the title and description below.';
                document.getElementById('title').value = '';
                document.getElementById('snippet').value = '';
            }

            fieldsEl.style.display = 'block';
        } catch (e) {
            checkBtn.disabled = false;
            checkBtn.innerHTML = '<i class="bi bi-search"></i> Check Link';
            statusEl.className = 'status-box danger';
            statusEl.innerHTML = '❌ Something went wrong while checking the link. Please type details manually.';
            fieldsEl.style.display = 'block';
        }
    });
</script>
@endpush