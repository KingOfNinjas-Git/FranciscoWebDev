<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProjectImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'path',
        'alt',
        'caption',
        'position',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getPublicPathAttribute()
    {
        $p = $this->path;
        if (!$p) return $p;

        // If path points to storage/... or public/..., normalize to public imgs path
        // storage/projects/... -> imgs/projects/... 
        if (Str::startsWith($p, 'storage/projects/')) {
            return str_replace('storage/projects/', 'imgs/projects/', $p);
        }

        if (Str::startsWith($p, 'public/projects/')) {
            return str_replace('public/projects/', 'imgs/projects/', $p);
        }

        if (Str::startsWith($p, 'storage/')) {
            return str_replace('storage/', 'imgs/', $p);
        }

        if (Str::startsWith($p, 'public/')) {
            return ltrim(str_replace('public/', '', $p), '/');
        }

        return $p;
    }
}
