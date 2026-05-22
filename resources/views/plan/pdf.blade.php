<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>خطة المراجعة · {{ $level ?? 'كل المستويات' }}</title>
<style>
    @page { margin: 16mm 14mm 16mm 14mm; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10.5pt;
        color: #1a1a1a;
        line-height: 1.5;
    }

    /* Header */
    .hdr        { border-bottom: 2pt solid #b88500; padding-bottom: 6pt; margin-bottom: 12pt; }
    .hdr-title  { font-size: 18pt; font-weight: bold; color: #b88500; margin: 0; }
    .hdr-sub    { font-size: 9.5pt; color: #555; margin-top: 2pt; }
    .hdr-meta   { font-size: 9pt; color: #888; margin-top: 4pt; }

    /* Summary stats */
    .summary    { display: table; width: 100%; margin-bottom: 14pt; border: 0.6pt solid #e5d8b3; border-radius: 4pt; padding: 6pt 8pt; background: #fbf6e6; }
    .summary-cell { display: table-cell; text-align: center; padding: 0 4pt; }
    .summary-num  { font-size: 16pt; font-weight: bold; color: #b88500; }
    .summary-lbl  { font-size: 8.5pt; color: #6b5d2c; }

    /* Tip box */
    .tip { padding: 6pt 8pt; background: #f0f7ff; border-right: 3pt solid #4a90e2; margin-bottom: 14pt; font-size: 9.5pt; color: #3a4a5e; }

    /* Week section */
    .week        { page-break-inside: avoid; margin-bottom: 14pt; }
    .week-hdr    { background: #b88500; color: #fff; padding: 5pt 10pt; border-radius: 3pt; font-weight: bold; font-size: 12pt; }
    .week-meta   { font-size: 8.5pt; color: #fff8e1; margin-right: 8pt; font-weight: normal; }

    /* Bucket inside week */
    .bucket      { margin-top: 6pt; }
    .bucket-hdr  { display: block; font-size: 10pt; font-weight: bold; color: #444; padding: 3pt 0 2pt; border-bottom: 0.5pt solid #ddd; margin-bottom: 3pt; }
    .bucket-hdr .tone-dot { display: inline-block; width: 6pt; height: 6pt; border-radius: 50%; margin-left: 4pt; vertical-align: middle; }
    .tone-amber   { background: #f0a500; }
    .tone-orange  { background: #e07b00; }
    .tone-emerald { background: #28a745; }

    /* Checklist items */
    .item { padding: 2pt 0 2pt 16pt; font-size: 9.5pt; line-height: 1.45; position: relative; }
    .check {
        display: inline-block;
        width: 9pt; height: 9pt;
        border: 1pt solid #888;
        border-radius: 1.5pt;
        position: absolute; right: 0; top: 4pt;
    }
    .item-title { color: #222; }
    .item-sub   { color: #888; font-size: 8.5pt; }

    /* Footer */
    .footer { margin-top: 18pt; text-align: center; color: #aaa; font-size: 8pt; border-top: 0.4pt solid #ddd; padding-top: 4pt; }
    .empty { color: #aaa; font-size: 9pt; font-style: italic; padding: 4pt 0; }
</style>
</head>
<body>

<div class="hdr">
    <h1 class="hdr-title">خطة المراجعة · Mein Plan</h1>
    <div class="hdr-sub">
        @if($userName){{ $userName }} · @endif
        المستوى: <strong>{{ $level ?? 'كل المستويات' }}</strong> · {{ $weeks }} {{ $weeks === 2 ? 'أسابيع' : 'أسابيع' }}
    </div>
    <div class="hdr-meta">
        تاريخ الإنشاء: {{ now()->format('Y-m-d') }} ·
        طبع، علّق على الفيدر، وعمر كل صندوق ملي تكمل التمرين ✓
    </div>
</div>

{{-- Summary --}}
<div class="summary">
    <div class="summary-cell">
        <div class="summary-num">{{ $totalItems }}</div>
        <div class="summary-lbl">إجمالي التمارين</div>
    </div>
    <div class="summary-cell">
        <div class="summary-num">{{ $weeks }}</div>
        <div class="summary-lbl">أسابيع</div>
    </div>
    <div class="summary-cell">
        <div class="summary-num">~{{ $perWeekAvg }}</div>
        <div class="summary-lbl">تمرين فالأسبوع</div>
    </div>
    <div class="summary-cell">
        <div class="summary-num">~{{ $perDayAvg }}</div>
        <div class="summary-lbl">تمرين فاليوم</div>
    </div>
</div>

<div class="tip">
    💡 <strong>اقتراح:</strong> خصص ~30-45 دقيقة فاليوم. الترتيب هنا ماشي إلزامي —
    بدّل بين Lesen / Hören / Schreiben باش ما تتعدّ. ركز على الـ Hören نهار اللي عندك الوقت ديال السماع.
</div>

{{-- Weekly sections --}}
@foreach($schedule as $weekNum => $weekBuckets)
@php
    $weekTotal = array_sum(array_map('count', $weekBuckets));
@endphp
<div class="week">
    <div class="week-hdr">
        الأسبوع {{ $weekNum }}
        <span class="week-meta">({{ $weekTotal }} تمرين · ~{{ max(1, (int) ceil($weekTotal / 7)) }} فاليوم)</span>
    </div>

    @forelse($buckets as $bKey => $bucket)
        @php $items = $weekBuckets[$bKey] ?? []; @endphp
        @if(! empty($items))
        <div class="bucket">
            <div class="bucket-hdr">
                <span class="tone-dot tone-{{ $bucket['tone'] }}"></span>
                {{ $bucket['label_de'] }} <span style="color:#888; font-weight:normal;">· {{ $bucket['label_ar'] }}</span>
            </div>
            @foreach($items as $item)
                <div class="item">
                    <span class="check"></span>
                    <span class="item-title">{{ $item['title'] }}</span>
                    @if(! empty($item['sub']) && $item['sub'] !== ($bucket['label_de'] ?? ''))
                        <span class="item-sub"> — {{ $item['sub'] }}</span>
                    @endif
                </div>
            @endforeach
        </div>
        @endif
    @empty
        <div class="empty">— الأسبوع فارغ —</div>
    @endforelse
</div>
@endforeach

<div class="footer">
    {{ config('app.name') }} · خطة شخصية · {{ now()->format('Y-m-d H:i') }}
</div>

</body>
</html>
