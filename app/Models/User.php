<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'package_id', 'emails_used', 'leads_used', 'role', 'two_factor_enabled'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function package() { return $this->belongsTo(Package::class); }
    public function campaigns() { return $this->hasMany(Campaign::class); }
    public function emailsRemaining() { if (!$this->subscription || $this->subscription->status !== 'active') return 0; return $this->subscription->package->email_limit - $this->subscription->emails_used; }
    public function leadsRemaining() { if (!$this->subscription || $this->subscription->status !== 'active') return 0; return $this->subscription->package->lead_limit - $this->subscription->leads_used; }
    public function subscription() { return $this->hasOne(Subscription::class); }
    public function userServices() { return $this->hasMany(UserService::class); }
    public function hasQuota($amount = 1) { return $this->emailsRemaining() >= $amount; }
}
