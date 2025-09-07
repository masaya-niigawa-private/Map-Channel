<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // 認可/Policyで扱いやすいようにAuthenticatableを継承
// use Illuminate\Notifications\Notifiable; // 通知を使うなら有効化

class User extends Authenticatable
{
    use HasFactory; //, Notifiable;

    /**
     * テーブルカラム
     * - id (PK)
     * - uid (UNIQUE, Firebase UID)
     * - userName (表示名)
     * - email (UNIQUE)
     * - timestamps
     */
    protected $fillable = [
        'uid',
        'userName',
        'email',
    ];

    protected $hidden = [
        // APIで隠したい属性があればここに（例: 'remember_token' など）
    ];

    protected $casts = [
        // 必要に応じて追加
    ];

    /* ===== リレーション ===== */

    // users(1) ──< boards.author_id
    public function boards()
    {
        return $this->hasMany(Board::class, 'author_id');
    }

    // users(1) ──< board_comments.author_id
    public function boardComments()
    {
        return $this->hasMany(BoardComment::class, 'author_id');
    }

    // users(1) ──< board_favorites.user_id
    public function boardFavorites()
    {
        return $this->hasMany(BoardFavorite::class, 'user_id');
    }

    // users(1) ──< board_reports.reporter_id
    public function boardReports()
    {
        return $this->hasMany(BoardReport::class, 'reporter_id');
    }
}
