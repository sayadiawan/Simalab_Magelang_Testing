<?php

namespace Smt\Masterweb\Helpers;

use Smt\Masterweb\Models\User;

/**
 * @deprecated Gunakan NotificationInboxService (unread/read + worklist terpisah).
 */
class RoleNotificationService
{
    public function getForUser(User $user): array
    {
        return app(NotificationInboxService::class)->feed($user);
    }
}
