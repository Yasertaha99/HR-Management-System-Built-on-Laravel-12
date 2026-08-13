<?php

namespace App\Services\Notifications;

use App\Contracts\Notifications\NotificationChannelInterface;
use App\DTOs\Notifications\NotificationData;
use App\Enums\DeliveryStatus;
use Illuminate\Support\Collection;

class NotificationDispatcher
{
    /** @var array<string, NotificationChannelInterface> */
    private array $channels = [];

    public function registerChannel(NotificationChannelInterface $channel): void
    {
        $this->channels[$channel->name()] = $channel;
    }

    /**
     * Dispatch notification data across requested supported channels.
     * @return array<string, DeliveryStatus>
     */
    public function dispatch(NotificationData $data): array
    {
        $results = [];

        foreach ($data->channels as $channelName) {
            if (isset($this->channels[$channelName])) {
                $channel = $this->channels[$channelName];
                if ($channel->supports($data)) {
                    $results[$channelName] = $channel->send($data);
                } else {
                    $results[$channelName] = DeliveryStatus::SKIPPED;
                }
            }
        }

        return $results;
    }
}
