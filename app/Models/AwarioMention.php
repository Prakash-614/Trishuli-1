<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AwarioMention extends Model
{
    protected $guarded = [];

    protected $casts = [
        'raw' => 'array',
        'mentioned_at' => 'datetime',
    ];
    /**
     * Automatically convert UTC database timestamps to Nepal Time (Asia/Kathmandu)
     */
    public function getMentionedAtAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value, 'UTC')->setTimezone('Asia/Kathmandu') : null;
    }

    /**
     * Remove 4-byte emojis (like 🌊, 💔, ⚠️) that turn into ▯▯ in DomPDF.
     */
    protected function removeEmojis(string $text): string
    {
        // Remove 4-byte UTF-8 characters (emojis)
        $clean = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $text);
        // Remove miscellaneous symbols & pictographs
        $clean = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $clean);
        return trim($clean);
    }

    /**
     * Clean up Title: removes emojis, removes "#hashtag" junk, and provides clean English.
     */
    public function getCleanDisplayTitleAttribute(): string
    {
        $raw = $this->removeEmojis($this->title ?? '');

        // Remove trailing hashtags like "#yellowgumba #kathmandu #nepal"
        $noHashtags = trim(preg_replace('/#\w+/u', '', $raw));

        // If title became empty or was just hashtags, use the AI summary
        if (empty($noHashtags) || strlen($noHashtags) < 5) {
            if (!empty($this->content)) {
                return $this->removeEmojis(Str::limit(strip_tags($this->content), 75));
            }
            return ucfirst($this->source ?? 'Social') . ' Update: ' . $this->removeEmojis(Str::limit(strip_tags($this->snippet ?? ''), 60));
        }

        // If title is in Devanagari/Nepali, use the English AI summary instead of broken boxes
        if (preg_match('/[\x{0900}-\x{097F}]/u', $noHashtags)) {
            if (!empty($this->content)) {
                return $this->removeEmojis(Str::limit(strip_tags($this->content), 75));
            }
        }

        return Str::limit($noHashtags, 80);
    }

    /**
     * Clean up Content: removes emojis.
     */
    public function getCleanDisplayContentAttribute(): string
    {
        $text = $this->content ?: $this->snippet ?: 'No excerpt recorded.';
        return $this->removeEmojis(strip_tags($text));
    }
}
