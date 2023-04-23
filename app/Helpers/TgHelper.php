<?php

namespace App\Helpers;

use DefStudio\Telegraph\Models\TelegraphChat;

class TgHelper
{
    public static function isAdmin($chatId)
    {
        return !empty(env('ADMIN_CHAT_ID')) && env('ADMIN_CHAT_ID') == $chatId;
    }

    public static function getAdminChatId()
    {
        return env('ADMIN_CHAT_ID');
    }

    public static function findAdminChatId()
    {
        return TelegraphChat::query()->where('chat_id', TgHelper::getAdminChatId())->first();
    }
}
