<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }

    /**
     * Dapatkan data Pegawai dari database pegawai yang terhubung dengan akun user ini.
     */
    public function pegawai(): ?Pegawai
    {
        $cleanName = trim((string) $this->name);
        if ($cleanName !== '') {
            $matched = Pegawai::where('nama', $cleanName)
                ->orWhereRaw('LOWER(TRIM(nama)) = ?', [strtolower($cleanName)])
                ->first()
                ?? Pegawai::where('nama', 'like', "%{$cleanName}%")->first();

            if ($matched) {
                return $matched;
            }
        }

        // Fallback untuk akun umum/testing (seperti "Pengaju" atau "Maurin")
        if ($this->role === 'Operator') {
            return Pegawai::where('jabatan', 'like', '%operator%')->first()
                ?? Pegawai::first();
        }

        return Pegawai::where('jabatan', 'like', '%distribusi%')
            ->orWhere('jabatan', 'like', '%petugas%')
            ->orWhere('nama', 'like', '%pengaju%')
            ->first()
            ?? Pegawai::first();
    }

    public function getPegawaiAttribute(): ?Pegawai
    {
        return $this->pegawai();
    }

    public function isOperator(): bool
    {
        return $this->role === 'Operator';
    }
}
