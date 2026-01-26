<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'questions';
    protected $fillable = [
        'jurusan',
        'type',
        'question_text',
        'question_image',
        'points',
        'difficulty',
        'is_published',
        'created_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(QuestionOptions::class);
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_questions')->withPivot('order_no');
    }

    public function attemptAnswers()
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
