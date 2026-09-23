<!DOCTYPE html>
<html>

<head>
    <title>Add a Mention</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 700px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h2 {
            color: #2c3e50;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type=text],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        textarea {
            min-height: 100px;
        }

        button {
            margin-top: 20px;
            padding: 12px 24px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #34495e;
        }

        button:disabled {
            background: #aaa;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: #7f8c8d;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }

        .status {
            margin-top: 10px;
            font-size: 13px;
            color: #555;
        }

        .fields {
            display: none;
        }

        .instructions {
            background: #eef;
            padding: 12px;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <h2>Add a Mention Manually</h2>

    <div class="instructions">
        Use this for content the system can't find automatically — usually a Facebook or Instagram post you found
        yourself. Paste the link below and click "Check Link."
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <form id="mentionForm" method="POST" action="{{ route('mentions.store') }}">
        @csrf

        <label>Paste the link here</label>
        <input type="text" id="url" name="url" placeholder="https://facebook.com/...">
        <button type="button" id="checkBtn">Check Link</button>
        <div class="status" id="status"></div>

        <div class="fields" id="fields">
            <label>Title</label>
            <input type="text" id="title" name="title">

            <label>What does it say? (short description)</label>
            <textarea id="snippet" name="snippet"></textarea>

            <button type="submit">Save This Mention</button>
        </div>
    </form>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        document.getElementById('checkBtn').addEventListener('click', async function() {
            const url = document.getElementById('url').value.trim();
            const status = document.getElementById('status');
            const fields = document.getElementById('fields');

            if (!url) {
                status.textContent = 'Please paste a link first.';
                return;
            }

            status.textContent = 'Checking...';
            fields.style.display = 'none';

            try {
                const res = await fetch("{{ route('mentions.preview') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        url
                    }),
                });
                const data = await res.json();

                if (data.exists) {
                    status.textContent = '⚠️ This link is already saved. No need to add it again.';
                    return;
                }

                if (data.title) {
                    status.textContent = data.title_may_be_just_a_name ?
                        '⚠️ Found a title, but it looks like just a profile/page name — check the description below and consider writing a clearer title.' :
                        '✅ Found details automatically. Please review below and save.';
                    document.getElementById('title').value = data.title;
                    document.getElementById('snippet').value = data.snippet || '';
                } else {
                    status.textContent = '⚠️ Could not find details automatically. Please type them in below.';
                    document.getElementById('title').value = '';
                    document.getElementById('snippet').value = '';
                }

                fields.style.display = 'block';
            } catch (e) {
                status.textContent = 'Something went wrong. Please try again.';
            }
        });
    </script>
</body>

</html>
