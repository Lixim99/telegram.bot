<?php

namespace App\Handlers;

use DefStudio\Telegraph\Handlers\WebhookHandler as TGWebhookHandler;

class WebhookHandler extends TGWebhookHandler
{
    public function start(): void
    {
        $this->chat->html("Start: {$this->chat->chat_id}")->send();
    }

    public function dismiss(){
        //...

        $this->reply("Notification dismissed");
    }
}
