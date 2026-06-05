<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One reader comment on a /vision engineering doc.
 *
 * Created with approved=false. Only rows with approved=true are ever queried
 * for public display (see VisionDocsController::show + VisionCommentController).
 * The moderation gate is the security boundary: unapproved text never renders
 * to another visitor.
 */
class VisionComment extends Model
{
    protected $fillable = [
        'doc_slug',
        'author_name',
        'author_email',
        'body',
        'approved',
        'ip',
        'user_agent',
        'approved_at',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /** Approved comments for a given doc, oldest first (conversation order). */
    public function scopeApprovedFor($query, string $slug)
    {
        return $query->where('doc_slug', $slug)
            ->where('approved', true)
            ->orderBy('created_at');
    }
}
