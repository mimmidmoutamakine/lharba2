<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    public function accessRequests()
    {
        return $this->hasMany(AccessRequest::class)->latest();
    }

    /** Latest approved access — what the user is currently allowed to see. */
    public function currentAccess(): ?AccessRequest
    {
        return $this->accessRequests()
            ->where('status', AccessRequest::STATUS_APPROVED)
            ->orderByDesc('decided_at')
            ->first();
    }

    /** Latest pending request, if any. */
    public function pendingAccess(): ?AccessRequest
    {
        return $this->accessRequests()
            ->where('status', AccessRequest::STATUS_PENDING)
            ->latest()
            ->first();
    }

    public function hasAnyAccess(): bool
    {
        return $this->is_admin || $this->currentAccess() !== null;
    }

    /**
     * Latest approved access request that the user has NOT yet been welcomed for.
     * The polling endpoint uses this to push the welcome overlay the moment an
     * admin approves the request. Returns null if the welcomed_at column hasn't
     * been migrated yet (graceful degradation in production before migrate runs).
     */
    public function pendingWelcomeRequest(): ?AccessRequest
    {
        try {
            return $this->accessRequests()
                ->where('status', AccessRequest::STATUS_APPROVED)
                ->whereNull('welcomed_at')
                ->orderByDesc('decided_at')
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Language code the user is allowed to see content for. Admins see everything (treated as 'de'). */
    public function contentLanguage(): string
    {
        return $this->is_admin ? 'de' : ($this->currentAccess()?->language ?? 'de');
    }

    /** Level the user is approved for (B1/B2/...). null means "no filter" — used for admins. */
    public function contentLevel(): ?string
    {
        return $this->is_admin ? null : $this->currentAccess()?->level;
    }

    /** True only when the user can actually see published German content (or admin). */
    public function canSeeGermanContent(): bool
    {
        return $this->is_admin || $this->contentLanguage() === 'de';
    }
}
