<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadReply extends Model
{
    protected $fillable = ['lead_id', 'subject', 'body', 'mail_error'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
