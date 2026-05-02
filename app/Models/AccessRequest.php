<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessRequest extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DENIED   = 'denied';

    public const LANGUAGES = [
        'de' => 'Deutsch · ألمانية',
        'fr' => 'Français · فرنسية',
        'es' => 'Español · إسبانية',
        'it' => 'Italiano · إيطالية',
        'en' => 'English · إنجليزية',
    ];

    public const EXAMS_BY_LANGUAGE = [
        'de' => ['Telc', 'Goethe', 'ÖSD', 'ECL'],
        'fr' => ['TCF', 'TEF', 'DELF', 'DALF'],
        'es' => ['DELE', 'SIELE'],
        'it' => ['CILS', 'CELI', 'PLIDA'],
        'en' => ['IELTS', 'Cambridge', 'TOEFL'],
    ];

    public const LEVELS = ['A2', 'B1', 'B2', 'C1', 'C2'];

    protected $fillable = [
        'user_id', 'language', 'exam', 'level',
        'status', 'approved_by', 'decided_at', 'admin_note',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool   { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool  { return $this->status === self::STATUS_APPROVED; }
    public function isDenied(): bool    { return $this->status === self::STATUS_DENIED; }

    public function languageLabel(): string
    {
        return self::LANGUAGES[$this->language] ?? $this->language;
    }
}
