<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Jivochat\Event\ChatAccepted;
use App\Jivochat\Event\ChatUpdated;
use App\Jivochat\Event\Event;
use App\Jivochat\EventListener;
use App\Jivochat\Log\MySQLLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDO;

class JivoBotController extends Controller
{
    public function catch($token, Request $request)
    {
        if (!$request->get('event') == 'CLIENT_MESSAGE') {
            return;
        }

        $message = $request->get('message');

        if (!empty($message['text'])) {
            $preparedString = Str::of($message['text'])->remove('/start ');

            if ($preparedString->isNotEmpty()) {
                $answer = $preparedString->explode('_')->whenNotEmpty(function ($value) {
                    $numbers = "{$value->get(0)} " . Str::replace('c', ',', $value->get(1)) .
                        ' на ' . Str::replace('c', ',', $value->get(2)) . " {$value->get(3)}.";

                    return [
                        'startText' => 'Вы создали заявку обмена ' . $numbers . ' Наш менеджер свяжется с вами в ближайшее время.',
                    ];
                });
            } else {
                $answer = [
                    'startText' => 'Вы создали заявку обмена. Наш менеджер свяжется с вами в ближайшее время.',
                ];
            }

            $this->sendMessage($request->toArray(), $answer);
        }
    }

    public function sendMessage($chat, $message)
    {
        Log::debug(Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post(
            'https://bot.jivosite.com/webhooks/bPPtN3nsZTY9eHZ/v8JSLZzLwe7se6jua2B=ojoh?PU5g-O6?FqrDtOybJz2UTU-eFrTq0DcAJRzhVtIw5',
            [
                'id' => $chat['id'],
                'client_id' => $chat['client_id'],
                'chat_id' => $chat['chat_id'],
                'message' => [
                    'text' => $message,
                    'type' => 'TEXT',
                    'timestamp' => time(),
                ],
                'event' => 'BOT_MESSAGE',
            ]
        ));
    }
}
