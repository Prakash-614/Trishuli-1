<h2 style="color:#c0392b;">🔴 RED — Critical Risk Alert</h2>

<p><strong>Title:</strong> {{ $mention->title }}</p>
<p><strong>Source:</strong> {{ $mention->source }} | <strong>Reach:</strong> {{ $mention->reach }}</p>
<p><strong>Date:</strong> {{ $mention->mentioned_at }}</p>

<p><strong>Risk Reason:</strong><br>{{ $mention->risk_reason }}</p>

<p><strong>Summary:</strong><br>{{ $mention->content }}</p>

<p><a href="{{ $mention->url }}">Open original source</a></p>

<hr>
<p style="font-size:12px;color:#888;">Automated alert per Online Monitoring protocol — escalate to Project Lead and Sinohydro contact within 15 minutes.</p>