<?php

namespace App\Http\Controllers;

use App\Notifications\DiscordNotificationQueueElement;
use Illuminate\Support\Facades\Response;

class ErrorController extends Controller
{
    public function report(){
        $datValid = request()->validate([
            'msg' => 'required|string',
            'name' => 'required|string',
            'stack' => 'required|string',
            'comp' => 'required|string',
            'url' => 'required|string',
        ]);
        DiscordNotificationQueueElement::frontendException($datValid['msg'],
                $datValid['name'], $datValid['stack'], $datValid['comp'], $datValid['url']);
        return Response::json([
            "success" => true,
        ]);
    }
}
