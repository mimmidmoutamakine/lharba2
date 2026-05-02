<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>{{ $topic->title }} — Schreiben {{ $topic->level }}</title>
    <style>
        @page { margin: 12mm 14mm; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1f2937; line-height: 1.45; margin: 0; }
        .header { border-bottom: 1.5px solid #10b981; padding-bottom: 6px; margin-bottom: 10px; position: relative; }
        .module { font-size: 8pt; color: #10b981; letter-spacing: 0.18em; font-weight: 700; text-transform: uppercase; }
        h1 { font-size: 14pt; margin: 2px 0 0 0; font-weight: 700; color: #111827; line-height: 1.15; }
        .arabic { color: #6b7280; font-size: 8.5pt; }
        .level { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 8pt; font-weight: 700; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; position: absolute; top: 2px; right: 0; }
        .level.b2 { background: #fff7ed; color: #EA580C; border-color: #fed7aa; }
        .meta { font-size: 8.5pt; color: #6b7280; margin-top: 2px; }
        h2 { font-size: 10pt; margin: 10px 0 4px 0; color: #047857; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; border-bottom: 1px solid #e5e7eb; padding-bottom: 2px; }
        .scenario { background: #f9fafb; border-left: 3px solid #10b981; padding: 8px 10px; white-space: pre-line; font-size: 10pt; line-height: 1.5; }
        .points { margin: 6px 0 0 0; padding: 8px 10px 8px 28px; border: 1px solid #d1fae5; border-radius: 4px; background: #ecfdf5; }
        .points li { margin: 3px 0; font-size: 10pt; }
        .answer-area { margin-top: 12px; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 10px; min-height: 320px; }
        .answer-area .label { font-size: 8pt; color: #6b7280; letter-spacing: 0.12em; text-transform: uppercase; font-weight: 700; }
        .lines { margin-top: 10px; }
        .lines .line { border-bottom: 1px solid #cbd5e1; height: 22px; }
        .footer { margin-top: 8px; font-size: 7.5pt; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 3px; }
    </style>
</head>
<body>

<div class="header">
    <span class="level {{ $topic->level === 'B2' ? 'b2' : '' }}">{{ $topic->level }}</span>
    <div class="module">Schreiben{{ $topic->type ? ' · ' . $topic->type : '' }}</div>
    <h1>{{ $topic->title }}@if($topic->title_ar) <span class="arabic">— {{ $topic->title_ar }}</span>@endif</h1>
    <div class="meta">Bearbeitungszeit: {{ $topic->minutes }} min · ca. {{ $topic->level === 'B2' ? '150' : '120' }} Wörter</div>
</div>

<h2>Aufgabe</h2>
<div class="scenario">{{ $topic->scenario }}</div>

@if(!empty($topic->points))
<h2>Punkte zu beachten</h2>
<ol class="points">
    @foreach($topic->points as $p)
    <li>{{ $p }}</li>
    @endforeach
</ol>
@endif

<div class="answer-area">
    <div class="label">Ihre Antwort</div>
    <div class="lines">
        @for($i = 0; $i < 16; $i++)
        <div class="line"></div>
        @endfor
    </div>
</div>

<div class="footer">{{ $topic->title }} — Schreiben {{ $topic->level }} · Generated {{ now()->format('Y-m-d') }}</div>

</body>
</html>
