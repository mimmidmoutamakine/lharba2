<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>{{ $topic->title }} — {{ $teilLabel }}</title>
    <style>
        @page { margin: 10mm 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #1f2937;
            line-height: 1.35;
            margin: 0;
        }
        .header {
            border-bottom: 1.5px solid #F59E0B;
            padding-bottom: 4px;
            margin-bottom: 8px;
            position: relative;
        }
        .module {
            font-size: 7.5pt;
            color: #F59E0B;
            letter-spacing: 0.18em;
            font-weight: 700;
            text-transform: uppercase;
        }
        h1 {
            font-size: 13pt;
            margin: 1px 0 0 0;
            font-weight: 700;
            color: #111827;
            line-height: 1.15;
        }
        .level {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: 700;
            background: #eef2ff;
            color: #F59E0B;
            border: 1px solid #c7d2fe;
            position: absolute;
            top: 2px;
            right: 0;
        }
        .level.b2 { background: #fff7ed; color: #EA580C; border-color: #fed7aa; }
        .arabic { color: #6b7280; font-size: 8pt; margin-top: 1px; }

        h2 {
            font-size: 9.5pt;
            margin: 7px 0 3px 0;
            color: #4338ca;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1px;
        }
        p { margin: 3px 0; }

        /* Compact reading text block */
        .text-block {
            background: #f9fafb;
            border-left: 2px solid #F59E0B;
            padding: 5px 8px;
            margin: 4px 0 6px;
            white-space: pre-line;
            font-size: 9pt;
            line-height: 1.4;
        }

        /* Tight question card */
        .q {
            margin: 3px 0;
            padding: 3px 6px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            page-break-inside: avoid;
        }
        .q .stem { font-weight: 600; font-size: 8.8pt; }
        .num {
            display: inline-block;
            min-width: 18px;
            padding: 0 4px;
            border-radius: 2px;
            background: #e0e7ff;
            color: #4338ca;
            font-weight: 700;
            font-size: 7.8pt;
            text-align: center;
        }
        .q-opts {
            margin: 2px 0 0 22px;
            font-size: 8.5pt;
        }
        .q-opts .opt { display: inline-block; margin-right: 10px; }
        .q-opts .letter {
            display: inline-block;
            width: 12px;
            font-weight: 700;
            color: #6b7280;
        }

        /* Answer key — single compact strip */
        .answer-key {
            margin-top: 8px;
            padding: 5px 8px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 4px;
            page-break-inside: avoid;
        }
        .answer-key .ak-label {
            font-size: 7.5pt;
            color: #047857;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .answer-key .ak-row { font-size: 9pt; line-height: 1.5; }
        .answer-key .ak-row .pair {
            display: inline-block;
            margin: 0 4px 2px 0;
            padding: 1px 5px;
            background: #d1fae5;
            border-radius: 3px;
            font-weight: 600;
            color: #065f46;
        }
        .answer-key .ak-row .ans { color: #047857; font-weight: 700; margin-left: 2px; }

        /* Two-column wrapper for ads/headlines */
        .two-col { -webkit-column-count: 2; column-count: 2; -webkit-column-gap: 8px; column-gap: 8px; }
        .two-col > * { -webkit-column-break-inside: avoid; page-break-inside: avoid; break-inside: avoid; }

        /* Headline list / pool list — tight */
        .compact-list { margin: 0; padding: 0; list-style: none; font-size: 8.5pt; }
        .compact-list li { padding: 1px 0; }
        .compact-list .letter { display: inline-block; width: 14px; font-weight: 700; color: #4338ca; }

        /* Ads (Teil 3) compact */
        .ad {
            margin: 0 0 4px 0;
            padding: 3px 6px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            font-size: 8pt;
            page-break-inside: avoid;
        }
        .ad .id {
            display: inline-block;
            background: #F59E0B;
            color: white;
            border-radius: 2px;
            font-weight: 700;
            padding: 0 4px;
            margin-right: 4px;
            font-size: 8pt;
        }
        .ad .title { font-weight: 700; display: inline; }
        .ad .body { font-size: 7.8pt; color: #4b5563; margin-top: 1px; white-space: pre-line; line-height: 1.3; }

        /* Situations (Teil 3) tight */
        .sit {
            margin: 1px 0;
            padding: 2px 6px;
            border-left: 2px solid #c7d2fe;
            font-size: 8.5pt;
        }
        .sit .num { margin-right: 4px; }

        /* Inline blank pill for SB1/SB2 */
        .blank-inline {
            display: inline-block;
            min-width: 40px;
            padding: 0 3px;
            border-bottom: 1px dashed #9ca3af;
            color: transparent;
            text-align: center;
            font-weight: 700;
            line-height: 1;
        }
        .blank-inline sup { color: #F59E0B; font-weight: 700; font-size: 7pt; }

        /* Word pool box (SB2) */
        .word-pool {
            margin: 4px 0;
            padding: 4px 6px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            font-size: 8.5pt;
        }
        .word-pool .word {
            display: inline-block;
            margin: 1px 4px 1px 0;
            padding: 1px 4px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
        }
        .word-pool .word .id { font-weight: 700; color: #F59E0B; margin-right: 2px; font-size: 7.5pt; }

        /* Explanation block (SB1/SB2) */
        .expl {
            font-size: 7.8pt;
            color: #4b5563;
            line-height: 1.35;
            direction: rtl;
            margin: 1px 0 2px 24px;
        }

        .footer {
            margin-top: 6px;
            font-size: 7pt;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 2px;
        }
    </style>
</head>
<body>

<div class="header">
    <span class="level {{ $topic->level === 'B2' ? 'b2' : '' }}">{{ $topic->level }}</span>
    @php
        // individualTitle (per-Teil heading) is the main title; examTitle shown after a ·.
        $pdfIndividual = is_array($content) ? ($content['individualTitle'] ?? null) : null;
        $pdfHeading    = $pdfIndividual ?: $topic->title;
    @endphp
    <div class="module">Lesen · {{ $teilLabel }}@if($pdfIndividual && $pdfIndividual !== $topic->title) · {{ $topic->title }}@endif</div>
    <h1>{{ $pdfHeading }}@if($topic->title_ar) <span class="arabic">— {{ $topic->title_ar }}</span>@endif</h1>
</div>

@if($teil === 'teil1')
    @php
        $headlines = $content['headlines'] ?? [];
        $texts     = $content['texts'] ?? [];
        $correct   = $content['correctAnswers'] ?? [];
    @endphp

    <h2>Überschriften</h2>
    <ul class="compact-list two-col">
        @foreach($headlines as $h)
        <li><span class="letter">{{ strtoupper($h['id'] ?? '?') }}</span> {{ $h['text'] ?? '' }}</li>
        @endforeach
    </ul>

    <h2>Texte</h2>
    @foreach($texts as $t)
    <div class="q">
        <div class="stem"><span class="num">{{ $t['id'] ?? '?' }}</span></div>
        <div style="white-space: pre-line; font-size: 8.5pt; line-height: 1.35; margin-top: 2px;">{{ $t['content'] ?? '' }}</div>
    </div>
    @endforeach

    <div class="answer-key">
        <div class="ak-label">Lösungen</div>
        <div class="ak-row">
            @foreach($correct as $textId => $headlineId)
            <span class="pair">{{ $textId }} <span class="ans">→ {{ strtoupper($headlineId) }}</span></span>
            @endforeach
        </div>
    </div>

@elseif($teil === 'teil2')
    @php $questions = $content['questions'] ?? []; @endphp

    @if(!empty($content['textTitle']))
    <h2 style="font-size: 10.5pt; color: #111827; border: none; text-transform: none; letter-spacing: 0; margin-top: 4px;">{{ $content['textTitle'] }}</h2>
    @endif
    <div class="text-block">{{ $content['textContent'] ?? '' }}</div>

    <h2>Fragen</h2>
    @foreach($questions as $q)
    <div class="q">
        <div class="stem"><span class="num">{{ $q['id'] ?? '?' }}</span> {{ $q['text'] ?? '' }}</div>
        <div class="q-opts">
            @foreach(($q['options'] ?? []) as $oi => $opt)
            <span class="opt"><span class="letter">{{ ['a','b','c'][$oi] ?? '?' }})</span>{{ $opt }}</span>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="answer-key">
        <div class="ak-label">Lösungen</div>
        <div class="ak-row">
            @foreach($questions as $q)
            <span class="pair">{{ $q['id'] ?? '?' }} <span class="ans">→ {{ ['a','b','c'][$q['correct'] ?? 0] ?? '?' }}</span></span>
            @endforeach
        </div>
    </div>

@elseif($teil === 'teil3')
    @php
        $ads        = $content['ads'] ?? [];
        $situations = $content['situations'] ?? [];
        $correct    = $content['correctAnswers'] ?? [];
    @endphp

    <h2>Situationen</h2>
    <div class="two-col">
        @foreach($situations as $s)
        <div class="sit"><span class="num">{{ $s['id'] ?? '?' }}</span>{{ $s['text'] ?? '' }}</div>
        @endforeach
    </div>

    <h2>Anzeigen</h2>
    <div class="two-col">
        @foreach($ads as $ad)
        <div class="ad">
            <span class="id">{{ strtoupper($ad['id'] ?? '?') }}</span><span class="title">{{ $ad['title'] ?? '—' }}</span>
            <div class="body">{{ $ad['text'] ?? '' }}</div>
        </div>
        @endforeach
    </div>

    <div class="answer-key">
        <div class="ak-label">Lösungen</div>
        <div class="ak-row">
            @foreach($correct as $sitId => $adId)
            <span class="pair">{{ $sitId }} <span class="ans">→ {{ strtoupper($adId) }}</span></span>
            @endforeach
        </div>
    </div>

@elseif($teil === 'sprachbausteine1')
    @php
        $segments = $content['segments'] ?? [];
        $blanks   = array_values(array_filter($segments, fn($s) => is_array($s)));
    @endphp

    <div class="text-block" style="white-space: normal">
        @foreach($segments as $seg)
            @if(is_string($seg))
                <span style="white-space: pre-line">{{ $seg }}</span>
            @elseif(is_array($seg))
                <span class="blank-inline"><sup>{{ $seg['id'] ?? '?' }}</sup>____</span>
            @endif
        @endforeach
    </div>

    <h2>Lückenoptionen</h2>
    <div class="two-col">
        @foreach($blanks as $b)
        <div class="q">
            <span class="num">{{ $b['id'] ?? '?' }}</span>
            @foreach(($b['options'] ?? []) as $oi => $opt)
            <span class="opt" style="margin-right: 6px; font-size: 8pt;"><span class="letter" style="color: #6b7280; font-weight: 700;">{{ chr(97 + $oi) }})</span>{{ $opt }}</span>
            @endforeach
        </div>
        @endforeach
    </div>

    <div class="answer-key">
        <div class="ak-label">Lösungen</div>
        <div class="ak-row">
            @foreach($blanks as $b)
            <span class="pair">{{ $b['id'] ?? '?' }} <span class="ans">→ {{ $b['correct'] ?? '?' }}</span></span>
            @endforeach
        </div>
    </div>

@elseif($teil === 'sprachbausteine2')
    @php
        $segments = $content['segments'] ?? [];
        $blanks   = array_values(array_filter($segments, fn($s) => is_array($s)));
        $pool     = $content['wordPool'] ?? [];
        $poolMap  = collect($pool)->keyBy('id');
    @endphp

    <div class="text-block" style="white-space: normal">
        @foreach($segments as $seg)
            @if(is_string($seg))
                <span style="white-space: pre-line">{{ $seg }}</span>
            @elseif(is_array($seg))
                <span class="blank-inline"><sup>{{ $seg['id'] ?? '?' }}</sup>____</span>
            @endif
        @endforeach
    </div>

    <div class="word-pool">
        @foreach($pool as $w)
        <span class="word"><span class="id">{{ strtoupper($w['id'] ?? '?') }}</span>{{ $w['word'] ?? '' }}</span>
        @endforeach
    </div>

    <div class="answer-key">
        <div class="ak-label">Lösungen</div>
        <div class="ak-row">
            @foreach($blanks as $b)
            @php
                $correctId = $b['correct'] ?? null;
                $word      = $correctId && $poolMap->has($correctId) ? ($poolMap[$correctId]['word'] ?? '') : '';
            @endphp
            <span class="pair">{{ $b['id'] ?? '?' }} <span class="ans">→ {{ strtoupper($correctId ?? '?') }}</span>{{ $word ? ' · ' . $word : '' }}</span>
            @endforeach
        </div>
    </div>
@endif

</body>
</html>
