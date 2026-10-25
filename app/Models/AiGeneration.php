<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiGeneration extends Model
{
    protected $fillable = [
        'kind', 'status', 'params', 'result', 'error', 'model',
        'input_tokens', 'output_tokens', 'context_stats',
    ];

    protected $casts = [
        'params' => 'array',
        'result' => 'array',
        'context_stats' => 'array',
    ];

    // A job that never finished (server restarted, PHP killed) is reported as failed instead of spinning forever
    public function isStale(): bool
    {
        return in_array($this->status, ['pending', 'running'], true) && $this->updated_at->lt(now()->subMinutes(6));
    }
}
