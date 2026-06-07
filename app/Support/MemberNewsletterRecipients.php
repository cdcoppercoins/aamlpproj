<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class MemberNewsletterRecipients
{
    /**
     * @return Builder<User>
     */
    public static function eligibleQuery(): Builder
    {
        return User::query()
            ->where('is_blocked', false)
            ->whereNull('member_newsletter_opt_out_at')
            ->whereNotNull('email')
            ->where('email', '!=', '');
    }

    public static function eligibleCount(): int
    {
        return self::eligibleQuery()->count();
    }
}
