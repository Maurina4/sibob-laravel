<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_file_asli',
        'nama_file_hash',
        'total_baris',
        'jumlah_sukses',
        'jumlah_gagal',
        'status',
        'error_summary',
    ];

    protected $casts = [
        'error_summary' => 'array',
        'total_baris' => 'integer',
        'jumlah_sukses' => 'integer',
        'jumlah_gagal' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
