<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'username',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if user is super admin (cannot be deleted)
     */
    public function isSuperAdmin()
    {
        // Ganti dengan username admin inti Anda
        return $this->username === 'admin' && $this->role === 'admin';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user can be deleted
     */
    public function canBeDeleted()
    {
        // Super admin tidak dapat dihapus
        if ($this->isSuperAdmin()) {
            return false;
        }

        // User tidak dapat menghapus akunnya sendiri
        if ($this->id === auth()->id()) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can be deleted by current authenticated user
     */
    /**
     * Check if user can be deleted by current authenticated user
     *
     * @return bool
     */
    public function canBeDeletedByCurrentUser()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        // Super admin tidak dapat dihapus
        if ($this->isSuperAdmin()) {
            return false;
        }

        // User tidak dapat menghapus akunnya sendiri
        if ($this->id === $currentUser->id) {
            return false;
        }

        // Hanya admin yang bisa menghapus user lain
        if (! $currentUser->isAdmin()) {
            return false;
        }

        // Jika yang akan dihapus adalah admin, pastikan masih ada admin lain
        if ($this->isAdmin()) {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return false;
            }
        }

        return true;
    }
}
