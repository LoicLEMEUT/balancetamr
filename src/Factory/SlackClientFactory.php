<?php

namespace App\Factory;

use lygav\slackbot\SlackBot;

class SlackClientFactory
{
    public function createSlackBotClient(string $webHook): SlackBot
    {
        return new SlackBot($webHook);
    }
}
