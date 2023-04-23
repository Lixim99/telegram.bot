<?php

namespace App\Handlers;

use App\Helpers\TgHelper;
use DefStudio\Telegraph\Handlers\WebhookHandler as TGWebhookHandler;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Stringable;

class WebhookHandler extends TGWebhookHandler
{
    public function start(): void
    {
        $this->chat->html("Start: {$this->chat->chat_id}")->send();
    }

    public function handleChatMessage(Stringable $text): void
    {
        if (TgHelper::isAdmin($this->chat->id)) {
            $adminChat = TelegraphChat::find($this->chat->id);
            $adminChat->html("Received: $text")->send();
        }
    }
}
