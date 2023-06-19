<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Repositories\JivoBotRepository;
use Exception;
use Illuminate\Http\Request;

class JivoBotController extends Controller
{
    private JivoBotRepository $repository;

    public function __construct(Request $request)
    {
        $this->repository = new JivoBotRepository($request->toArray());
    }

    public function catch($token, Request $request)
    {
        try {
            $this->repository->forSlug($token)
                ->setStartMessageFromMessage($request->get('message')['text'])
                ->sendMessage()
                ->inviteAgent();

        } catch (Exception $data) {
            abort($data->getCode(), $data->getMessage());
        }
    }
}
