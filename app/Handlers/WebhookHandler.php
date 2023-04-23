<?php

namespace App\Handlers;

use DefStudio\Telegraph\Handlers\WebhookHandler as TGWebhookHandler;
use Illuminate\Support\Stringable;

class WebhookHandler extends TGWebhookHandler
{
    public function start(): void
    {
        $this->chat->html("Start: {$this->chat->chat_id}")->send();
    }

    public function handleChatMessage(Stringable $text): void
    {
        $this->chat->html("Received: $text")->send();
    }
}
