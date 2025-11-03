<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModemHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'modem_id', 'pelanggan', 'tanggal_pasang', 'tanggal_tarik', 'keterangan'
    ];

    public function modem() {
        return $this->belongsTo(Modem::class);
    }
}
