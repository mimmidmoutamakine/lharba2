<?php

namespace App\Services;

use App\Models\SchreibenTopic;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey  = (string) config('services.gemini.key');
        $this->model   = (string) config('services.gemini.model', 'gemini-2.0-flash');
        $this->timeout = (int) config('services.gemini.timeout', 60);

        if ($this->apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY is not configured.');
        }
    }

    /**
     * Correct a Schreiben submission and return markdown feedback + parsed metadata.
     *
     * @return array{
     *     markdown:string,
     *     score:int, score_max:int,
     *     raw_score:int, raw_max:int,
     *     thema_verfehlt:bool, situierung_verfehlt:bool,
     *     wortzahl:int, level_label:string
     * }
     */
    public function correctSchreiben(SchreibenTopic $topic, string $studentText): array
    {
        $prompt   = $this->buildCorrectionPrompt($topic, $studentText);
        $markdown = $this->callModel($prompt, ['temperature' => 0.25]);
        return $this->parseFeedback($markdown);
    }

    /**
     * Generate a model B2 email from selected Leitpunkte and student ideas.
     *
     * @param array<int, array{label:string, ideas:string}> $selections
     * @return array{markdown:string, email:string}
     */
    public function generateExample(SchreibenTopic $topic, string $emailType, array $selections): array
    {
        $prompt   = $this->buildGenerationPrompt($topic, $emailType, $selections);
        $markdown = $this->callModel($prompt, ['temperature' => 0.6]);
        return [
            'markdown' => $markdown,
            'email'    => $this->extractEmailSection($markdown),
        ];
    }

    private function callModel(string $prompt, array $generationOverrides = []): string
    {
        $generationConfig = array_merge([
            'temperature'     => 0.25,
            'topP'            => 0.9,
            'maxOutputTokens' => 4096,
        ], $generationOverrides);

        $response = Http::timeout($this->timeout)
            ->withQueryParameters(['key' => $this->apiKey])
            ->acceptJson()
            ->asJson()
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent",
                [
                    'contents' => [[
                        'parts' => [['text' => $prompt]],
                    ]],
                    'generationConfig' => $generationConfig,
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT',        'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH',       'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                    ],
                ]
            );

        if (! $response->successful()) {
            Log::error('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException($this->humanizeError($response->status(), $response->json()));
        }

        $markdown = (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
        if (trim($markdown) === '') {
            $finishReason = data_get($response->json(), 'candidates.0.finishReason');
            Log::warning('Gemini returned empty content', ['finish' => $finishReason]);
            throw new RuntimeException('AI returned an empty response.');
        }

        return $markdown;
    }

    private function buildCorrectionPrompt(SchreibenTopic $topic, string $studentText): string
    {
        $level    = $topic->level === 'B2' ? 'b2' : 'b1';
        $template = $this->loadTemplate("schreiben_{$level}.txt");

        return strtr($template, [
            '{{TASK_TEXT}}'    => $this->formatTaskText($topic),
            '{{LEITPUNKTE}}'   => $this->formatLeitpunkte($topic),
            '{{STUDENT_TEXT}}' => trim($studentText),
        ]);
    }

    private function buildGenerationPrompt(SchreibenTopic $topic, string $emailType, array $selections): string
    {
        $template = $this->loadTemplate('schreiben_generate_b2.txt');

        $selectedLines = '';
        $ideasBlocks   = '';
        foreach ($selections as $sel) {
            $label = trim((string) ($sel['label'] ?? ''));
            $ideas = trim((string) ($sel['ideas'] ?? ''));
            if ($label === '') continue;
            $selectedLines .= '- ' . $label . "\n";
            $ideasBlocks   .= '### ' . $label . "\n" . ($ideas !== '' ? $ideas : '(no idea — invent ONE realistic, close-to-life idea fitting the task)') . "\n\n";
        }

        return strtr($template, [
            '{{TASK_TEXT}}'        => $this->formatTaskText($topic),
            '{{EMAIL_TYPE}}'       => trim($emailType) !== '' ? trim($emailType) : (string) ($topic->type ?? 'Beschwerde'),
            '{{LEITPUNKTE}}'       => $this->formatLeitpunkte($topic),
            '{{SELECTED_POINTS}}'  => trim($selectedLines) ?: '(none specified)',
            '{{STUDENT_IDEAS}}'    => trim($ideasBlocks)   ?: '(none provided — invent realistic ideas)',
        ]);
    }

    private function formatTaskText(SchreibenTopic $topic): string
    {
        return trim((string) $topic->title) . "\n\n" . trim((string) $topic->scenario);
    }

    private function formatLeitpunkte(SchreibenTopic $topic): string
    {
        $points = is_array($topic->points) ? $topic->points : [];
        $out    = '';
        foreach ($points as $i => $p) {
            $out .= 'LP' . ($i + 1) . ': ' . trim((string) $p) . "\n";
        }
        return trim($out) ?: '(keine Leitpunkte angegeben)';
    }

    private function extractEmailSection(string $markdown): string
    {
        if (preg_match('/##\s*1\.\s*Fertige E-Mail\s*\n+(.*?)(?=\n##\s|\z)/su', $markdown, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function loadTemplate(string $filename): string
    {
        $path = resource_path('prompts/' . $filename);
        if (! is_file($path)) {
            throw new RuntimeException("Prompt template not found: {$filename}");
        }
        return file_get_contents($path);
    }

    private function parseFeedback(string $markdown): array
    {
        $rawScore  = 0;
        $rawMax    = 15;
        $score     = 0;
        $scoreMax  = 45;
        $thema     = false;
        $situ      = false;
        $wortzahl  = 0;

        if (preg_match('/Gesamt:\s*(\d+)\s*\/\s*15\s*[→\->]+\s*(\d+)\s*\/\s*45/u', $markdown, $m)) {
            $rawScore = (int) $m[1];
            $score    = (int) $m[2];
        } elseif (preg_match('/(\d+)\s*\/\s*45/u', $markdown, $m)) {
            $score    = (int) $m[1];
            $rawScore = (int) round($score / 3);
        }

        if (preg_match('/Thema verfehlt:\s*(ja|nein)/iu', $markdown, $m)) {
            $thema = strtolower($m[1]) === 'ja';
        }
        if (preg_match('/Situierung verfehlt:\s*(ja|nein)/iu', $markdown, $m)) {
            $situ = strtolower($m[1]) === 'ja';
        }
        if (preg_match('/Wortzahl:\s*ca\.?\s*(\d+)/iu', $markdown, $m)) {
            $wortzahl = (int) $m[1];
        }

        return [
            'markdown'            => $markdown,
            'score'               => max(0, min($scoreMax, $score)),
            'score_max'           => $scoreMax,
            'raw_score'           => max(0, min($rawMax, $rawScore)),
            'raw_max'             => $rawMax,
            'thema_verfehlt'      => $thema,
            'situierung_verfehlt' => $situ,
            'wortzahl'            => $wortzahl,
            'level_label'         => $this->levelLabel($score),
        ];
    }

    private function humanizeError(int $status, ?array $body): string
    {
        $apiMsg = (string) data_get($body, 'error.message', '');

        if ($status === 429) {
            $retry = '';
            foreach ((array) data_get($body, 'error.details', []) as $d) {
                if (str_contains((string) ($d['@type'] ?? ''), 'RetryInfo')) {
                    $retry = (string) ($d['retryDelay'] ?? '');
                    break;
                }
            }
            $suffix = $retry ? " (عاود حاول بعد {$retry})" : ' (عاود حاول بعد دقيقة)';
            return 'تجاوزت حد الاستعمال المجاني ديال Gemini' . $suffix;
        }

        if ($status === 400 && str_contains($apiMsg, 'API key')) {
            return 'مفتاح Gemini غير صحيح. تحقق من GEMINI_API_KEY في .env';
        }

        if ($status === 404) {
            return 'موديل Gemini غير موجود. غير GEMINI_MODEL في .env (مثلا: gemini-2.5-flash-lite)';
        }

        if ($status === 503) {
            return 'سيرفر Gemini مشغول. عاود حاول بعد لحظات.';
        }

        return 'AI service error (' . $status . '). ' . ($apiMsg ?: 'حاول مرة أخرى.');
    }

    private function levelLabel(int $score): string
    {
        return match (true) {
            $score >= 36 => 'B2 niveau erreicht',
            $score >= 25 => 'B2 fast erreicht',
            $score >= 15 => 'unter B2',
            default      => 'weit unter B2',
        };
    }
}
