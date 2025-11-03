<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modem extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number', 'status', 'pelanggan_aktif'
    ];

  public function histories()
{
    return $this->hasMany(ModemHistory::class);
}
}