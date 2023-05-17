<?php

namespace App\Handlers;

use App\Helpers\TgHelper;
use DefStudio\Telegraph\Handlers\WebhookHandler as TGWebhookHandler;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class WebhookHandler extends TGWebhookHandler
{
    public function start(): void
    {
        $preparedString = Str::of($this->data->get('text'))->remove('/start');

        if ($preparedString->isNotEmpty()) {
            $answer = $preparedString->explode('_')->whenNotEmpty(function ($value) {
                $numbers = "{$value->get(0)} " . Str::replace('c', ',', $value->get(1)) .
                    ' на ' . Str::replace('c', ',', $value->get(2)) . " {$value->get(3)}.";

                return [
                    'startText' => ' Я хочу обменять ' . $numbers,
                    'adminText' => 'Запрос на обмен валюты по курсу - ' . $numbers
                ];
            });
        } else {
            $answer = [
                'startText' => 'Вы создали заявку обмена.',
                'adminText' => 'Пользователь пришел по ссылке без указания валют'
            ];
        }

        /** Send to user */
        $this->chat->html($answer['startText'])->send();

        /** Send to admin */
        $adminChat = TgHelper::findAdminChatId();

        if (!TgHelper::isAdmin($this->chat->chat_id)) {
            $adminChat->forwardMessage($this->chat->chat_id, $this->messageId)->send();
            $adminChat->html($answer['adminText'])->send();
        }
    }

    public function handleChatMessage(Stringable $text): void
    {
        /**
         * @var TelegraphChat $adminChat
         */
        $adminChat = TgHelper::findAdminChatId();

        /** Forward to admin */
        if (!TgHelper::isAdmin($this->chat->chat_id)) {
            $adminChat->forwardMessage($this->chat->chat_id, $this->messageId)->send();
        } else {
            $forwardUserId = data_get($this->request->toArray(), 'message.reply_to_message.forward_from.id');

            if (isset($forwardUserId)) {
                $userChat = TelegraphChat::query()->where('chat_id', $forwardUserId)->first();

                isset($userChat) ? $userChat->forwardMessage($adminChat, $this->messageId)->send()
                    : $adminChat->html('Пользователя не существует в базе данных')->send();
            } else {
                $adminChat
                    ->html('Для ответа клиенту используйте функцию "поделиться" на сообщении клиента')
                    ->photo(storage_path('app/public/files/instraction.png'))
                    ->send();
            }
        }
    }
}
