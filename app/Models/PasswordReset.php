<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    // Laravel secara default pakai timestamps, sedangkan tabel ini biasanya tidak punya created_at dan updated_at
    public $timestamps = false;

    // Nama tabelnya
    protected $table = 'password_resets';

    // Tabel ini biasanya tidak punya primary key
    protected $primaryKey = null;
    public $incrementing = false;

    // Kolom yang bisa diisi
    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];
}
