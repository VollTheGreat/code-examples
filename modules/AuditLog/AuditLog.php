<?php

namespace App\AuditLog;

use App\Users\User;
use OwenIt\Auditing\Models\Audit;

/**
 * AuditLog Model Class
 */
class AuditLog extends Audit
{
    public function scopeWhereHasAndWithUsers($query)
    {
        $query->whereHasMorph('user', User::class)
            ->with('user');
    }

    /**
     * is used as straigt User relation because eloquent can not perform whereHasMorph
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actionUser()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
