<?php


namespace App\Notification;

use App\Factory\SlackClientFactory;
use lygav\slackbot\SlackBot;

class SlackNotificationService implements InternalNotificationInterface
{
    /**
     * @var SlackClientFactory
     */
    private $clientFactory;

    public function __construct(SlackClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    public function sendMessageLoginAsInfo(string $webHook, string $message, ?array $attachments): void
    {
        $slackClient = $this->clientFactory->createSlackBotClient($webHook);

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $color = (!empty($attachment['color'])) ? $attachment['color'] : 'blue';

                $attachment = $slackClient->buildAttachment('')
                    ->setText($attachment['text'])
                    ->setColor($color);
                $slackClient->attach($attachment);
            }
        }

        $slackClient->text($message)->send();
    }
}
