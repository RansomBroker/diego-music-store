<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'avatar_url',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the branches assigned to this user.
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user');
    }

    /**
     * Get the employee profile associated with this user.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Determine whether the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'backoffice') {
            return (bool) ($this->is_active && $this->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin']));
        }

        return false;
    }

    /**
     * Get the avatar URL for Filament and general application.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    /**
     * Get the full avatar URL attribute.
     */
    public function getAvatarFullUrlAttribute(): ?string
    {
        $raw = $this->attributes['avatar_url'] ?? null;
        return $raw ? Storage::url($raw) : null;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->username)) {
                $baseUsername = strstr($user->email, '@', true) ?: strtolower(str_replace(' ', '', $user->name));
                
                // Ensure uniqueness
                $username = $baseUsername;
                $counter = 1;
                while (static::where('username', $username)->exists()) {
                    $username = $baseUsername . $counter;
                    $counter++;
                }
                
                $user->username = $username;
            }
        });

        static::created(function ($user) {
            if (!$user->employee()->exists()) {
                $lastId = Employee::withTrashed()->max('id') ?? 0;
                $nik = 'EMP-' . str_pad((string) ($lastId + 1), 4, '0', STR_PAD_LEFT);

                Employee::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nik' => $nik,
                    'monthly_off_days_quota' => 4,
                    'basic_salary' => 0,
                    'is_active' => $user->is_active ?? true,
                ]);
            }
        });
    }
}
