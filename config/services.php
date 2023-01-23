<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/redirect/github/callback',
    ],

//    'facebook' => [
//        'client_id' => env('FACEBOOK_CLIENT_ID'),
//        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
//        'redirect' => env('APP_URL').'/redirect/facebook/callback',
//    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/redirect/google/callback',
    ],

//    'twitter' => [
//        'client_id' => env('TWITTER_CLIENT_ID'),
//        'client_secret' => env('TWITTER_CLIENT_SECRET'),
//        'redirect' => env('APP_URL').'/redirect/twitter/callback',
//    ],

    'discord' => [
        'client_id' => env('DISCORD_CLIENT_ID'),
        'client_secret' => env('DISCORD_CLIENT_SECRET'),
        'redirect' => env('APP_URL').'/redirect/discord/callback',
        
        'token' => env('DISCORD_BOT_TOKEN'),
        'bot_channel' => env('DISCORD_BOT_CHANNEL'),
        'active' => env('DISCORD_BOT'),
    ],

];
