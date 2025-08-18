<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;
    // 一括代入の許可フィールド（必要最小限）
    protected $fillable = [
        'spot_id',
        'photo_path',
    ];

    // 必要ならリレーション（任意）
    public function spot()
    {
        return $this->belongsTo(Spot::class);
    }

}
