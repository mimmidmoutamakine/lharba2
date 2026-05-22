<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Hören · Richtig · {{ $level }}</title>
<style>
    @page { margin: 18mm 14mm 18mm 14mm; }
    body  { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #1a1a1a; line-height: 1.55; }
    h1    { font-size: 18pt; margin: 0 0 4pt; }
    h2    { font-size: 13pt; margin: 14pt 0 4pt; border-bottom: 1pt solid #b88500; padding-bottom: 2pt; color: #b88500; }
    h3    { font-size: 11pt; margin: 8pt 0 2pt; color: #333; font-weight: bold; }
    .meta { color: #666; font-size: 9.5pt; margin-bottom: 12pt; }
    .group { margin: 4pt 0 8pt; padding-left: 0; }
    .stmt { padding: 3pt 0 3pt 14pt; position: relative; font-size: 10.5pt; }
    .stmt::before { content: "+"; position: absolute; left: 0; top: 3pt; color: #2e8b3d; font-weight: bold; font-family: monospace; }
    .empty { color: #999; font-style: italic; font-size: 10pt; }
    .footer { margin-top: 18pt; text-align: center; color: #aaa; font-size: 8pt; border-top: 0.5pt solid #ddd; padding-top: 4pt; }
</style>
</head>
<body>

<h1>Hören · Richtig-Antworten · {{ $level }}</h1>
<div class="meta">
    تمت معاينة الجمل المعلّمة <strong>Richtig (+)</strong> فقط — لحفظها للامتحان.
    | {{ count($sections) === 1 ? 'Teil ' . $sections[0]['teil'] : 'All Teile' }}
    | تاريخ التحميل: {{ now()->format('Y-m-d') }}
</div>

@foreach($sections as $section)
    <h2>Teil {{ $section['teil'] }}@if($section['subtitle']) · {{ $section['subtitle'] }}@endif</h2>

    @forelse($section['groups'] as $title => $stmts)
        <h3>{{ $title }}</h3>
        <div class="group">
            @foreach($stmts as $text)
                <div class="stmt">{{ $text }}</div>
            @endforeach
        </div>
    @empty
        <div class="empty">— ما كاينش جمل Richtig فهاد الجزء —</div>
    @endforelse
@endforeach

<div class="footer">
    {{ config('app.name') }} · Hören Cheat-Sheet · {{ $level }}
</div>

</body>
</html>
