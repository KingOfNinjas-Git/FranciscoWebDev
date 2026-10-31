<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    public const STATUSES = ['new' => 'New', 'replied' => 'Replied', 'won' => 'Won', 'lost' => 'Lost'];

    protected $fillable = [
        'name', 'email', 'project_type', 'description', 'style', 'features', 'budget', 'contact_method', 'deadline',
        'locale', 'status', 'notes', 'mail_sent', 'mail_error', 'reminded_at',
    ];

    protected $casts = [
        'features' => 'array',
        'mail_sent' => 'boolean',
        'reminded_at' => 'datetime',
    ];

    public function replies()
    {
        return $this->hasMany(LeadReply::class)->orderBy('created_at');
    }

    /** Number of leads nobody has answered yet; 0 if the table does not exist yet (before the migration runs). */
    public static function newCount(): int
    {
        try {
            return static::where('status', 'new')->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
