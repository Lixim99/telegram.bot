<?php

namespace App\Helpers;

class TgHelper
{
    public static function isAdmin($chatId)
    {
        return !empty(env('ADMIN_CHAT_ID')) && env('ADMIN_CHAT_ID') == $chatId;
    }
}
