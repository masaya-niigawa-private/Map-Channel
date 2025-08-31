<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    // 主キーは uid（文字列）
    protected $primaryKey = 'uid';
    public $incrementing = false;
    protected $keyType = 'string';

    // created_at だけ使う（updated_at は無し）
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = ['uid', 'userName', 'email'];
}
