<?php

declare(strict_types=1);

namespace Optime\Util\Mailer\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;

class RecipientsListener implements EventSubscriberInterface
{
    private array $allowedPatterns = [];
    private array $fallbackRecipients = [];

    private bool $enabled = false;

    /**
     * @param array<string> $allowedPatterns
     * @param array<string> $fallbackRecipients
     */
    public function __construct(string $allowedPatterns = '', string $fallbackRecipients = '')
    {
        $this->allowedPatterns = array_filter(explode(',', $allowedPatterns));
        $this->fallbackRecipients = array_filter(explode(',', $fallbackRecipients));

        $this->enabled = count($this->allowedPatterns) > 0 || count($this->fallbackRecipients) > 0;
    }

    public function onMessage(MessageEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $recipients = $event->getEnvelope()->getRecipients();
        $filteredRecipients = [];
        foreach ($recipients as $recipient) {
            $email = $recipient->getAddress();
            foreach ($this->allowedPatterns as $pattern) {
                // Support for wildcard and regex patterns
                if (preg_match('/^'.str_replace('@', '.*@', preg_quote($pattern, '/')).'$/i',
                        $email) || fnmatch($pattern, $email)) {
                    $filteredRecipients[] = $recipient;
                    break;
                }
            }
        }

        if ($filteredRecipients) {
            $event->getEnvelope()->setRecipients($filteredRecipients);
        } elseif ($this->fallbackRecipients) {
            $event->getEnvelope()->setRecipients(Address::createArray($this->fallbackRecipients));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => ['onMessage', 100],
        ];
    }
}