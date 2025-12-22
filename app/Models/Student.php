<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';
    protected $fillable = [
        'user_id',
        'no_peserta',
        'major',
        'phone_number',
        'address'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
