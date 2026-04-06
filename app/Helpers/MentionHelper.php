<?php

namespace App\Helpers;

use App\Models\User;

class MentionHelper
{
    /**
     * Convert @username in text to styled blue badges
     */
    public static function renderMentions(string $html): string
    {
        return preg_replace_callback('/@(\w+)/', function ($matches) {
            $username = $matches[1];
            $user = User::where('username', $username)->first();

            if ($user) {
                return '<span style="background-color:rgba(59,130,246,0.15);color:#3b82f6;padding:1px 6px;border-radius:4px;font-weight:500;font-size:0.9em;">@' . e($user->username) . '</span>';
            }

            return $matches[0]; // Return as-is if user not found
        }, $html);
    }
}
