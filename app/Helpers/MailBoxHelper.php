<?php

namespace App\Helpers;

use App\Mailboxrow;

class MailBoxHelper
{
    public static function getNotificationListQuery(int $userId = 0, $isRead = true, $isUnRead = true, $startDate = null, $endDate = null)
    {
        $readMailBox = Mailboxrow::where(function ($query) use ($userId) {
            if ($userId != 0 && ! empty($userId)) {
                $query->where('read_status', 1);
                $query->whereIn('type', ['merchants', 'investor_payments', 'documents', 'merchant_payments', 'Marketing_Offer']);
            }
        })->where(function ($q) use ($userId) {
            $q->where('user_ids', 'LIKE', '['.$userId.']')->orWhere('user_ids', 'LIKE', '['.$userId.',%]')->orWhere('user_ids', 'LIKE', '[%,'.$userId.']')->orWhere('user_ids', 'LIKE', '[%,'.$userId.',%]');
        });
        if ($startDate != null) {
            $startDate = $startDate.' 00:00:00';
            $readMailBox = $readMailBox->where('created_at', '>=', $startDate);
        }
        if ($endDate != null) {
            $endDate = $endDate.' 23:59:59';
            $readMailBox = $readMailBox->where('created_at', '<=', $endDate);
        }
        $unReadMailBox = Mailboxrow::where(function ($query) use ($userId) {
            if ($userId != 0 && ! empty($userId)) {
                $query->where('read_status', 0);
                $query->whereIn('type', ['merchants', 'investor_payments', 'documents', 'merchant_payments', 'Marketing_Offer']);
            }
        })->where(function ($q) use ($userId) {
            $q->where('user_ids', 'LIKE', '['.$userId.']')->orWhere('user_ids', 'LIKE', '['.$userId.',%]')->orWhere('user_ids', 'LIKE', '[%,'.$userId.']')->orWhere('user_ids', 'LIKE', '[%,'.$userId.',%]');
        });
        if ($startDate != null) {
            $startDate = $startDate.' 00:00:00';
            $unReadMailBox = $unReadMailBox->where('created_at', '>=', $startDate);
        }
        if ($endDate != null) {
            $endDate = $endDate.' 23:59:59';
            $unReadMailBox = $unReadMailBox->where('created_at', '<=', $endDate);
        }
        if ($isRead && $isUnRead) {
            $readMailBox = $readMailBox->union($unReadMailBox)->orderByDesc('created_at');

            return $readMailBox;
        } elseif ($isRead) {
            $readMailBox = $readMailBox->orderByDesc('created_at');

            return $readMailBox;
        } elseif ($isUnRead) {
            $unReadMailBox = $unReadMailBox->orderByDesc('created_at');

            return $unReadMailBox;
        }
    }

    public static function getUnreadCount(int $userId = 0)
    {
        $unreadQuery = self::getNotificationListQuery($userId, false, true);

        return $unreadQuery->count();
    }
}
