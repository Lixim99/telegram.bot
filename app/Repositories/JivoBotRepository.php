<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JivoBotRepository
{
    protected string $id;

    protected string $chat_id;

    protected string $client_id;

    protected int $timestamp;

    protected array $message;

    public const EVENT_MESSAGE = 'CLIENT_MESSAGE';

    public const EVENT_BOT = 'BOT_MESSAGE';

    public const EVENT_AGENT = 'INVITE_AGENT';

    public const JIVO_API_URL = 'https://bot.jivosite.com/webhooks/gJrmTdbmSSDKci2/v8JSLZzLwe7se6jua2B=ojoh?PU5g-O6?FqrDtOybJz2UTU-eFrTq0DcAJRzhVtIw5';

    protected const JIVO_TOKEN = 'v8JSLZzLwe7se6jua2B=ojoh?PU5g-O6?FqrDtOybJz2UTU-eFrTq0DcAJRzhVtIw5';

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->chat_id = $data['chat_id'];
        $this->client_id = $data['client_id'];
        $this->timestamp = $data['message']['timestamp'] + 10;

        if ($data['event'] != JivoBotRepository::EVENT_MESSAGE) {
            throw new \Exception('Not supported event', 404);
        }
    }

    /**
     * @param $slug
     * @return $this
     * @throws \Exception
     */
    public function forSlug($slug)
    {
        if ($slug !== self::JIVO_TOKEN) {
            throw new \Exception('Slug not found', 404);
        }

        return $this;
    }

    /**
     * @param string $text
     * @return $this|false
     * Установить стартовое сообщение
     */
    public function setStartMessageFromMessage(string $text)
    {
        if (!empty($text) && Str::contains($text, '/start')) {
            $preparedString = Str::of($text)->remove('/start');

            if ($preparedString->isNotEmpty()) {
                $this->message = $preparedString->explode('_')->whenNotEmpty(function ($value) {
                    $numbers = "{$value->get(0)} " . Str::replace('c', ',', $value->get(1)) .
                        ' на ' . Str::replace('c', ',', $value->get(2)) . " {$value->get(3)}.";

                    return [
                        'text' => 'Вы создали заявку обмена ' . $numbers . ' Наш менеджер свяжется с вами в ближайшее время.',
                        'type' => 'TEXT',
                        'timestamp' => $this->timestamp,
                    ];
                });
            } else {
                $this->message = [
                    'text' => 'Вы создали заявку обмена. Наш менеджер свяжется с вами в ближайшее время.',
                    'type' => 'TEXT',
                    'timestamp' => $this->timestamp,
                ];
            }

            return $this;
        }

        throw new \Exception('Not start message', 404);
    }

    /**
     * @return JivoBotRepository
     * Отправка сообщения
     */
    public function sendMessage()
    {
        $fields = [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'chat_id' => $this->chat_id,
            'message' => $this->message,
            'event' => self::EVENT_BOT,
        ];

        Log::debug(json_encode($fields));

        Log::debug(Http::withHeaders(['Content-Type' => 'application/json'])->post(self::JIVO_API_URL, $fields));

        return $this;
    }

    public function inviteAgent()
    {
        $fields = [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'chat_id' => $this->chat_id,
            'event' => self::EVENT_AGENT,
        ];

        Log::debug(json_encode($fields));

        Log::debug(Http::withHeaders(['Content-Type' => 'application/json'])->post(self::JIVO_API_URL, $fields));
    }
}
