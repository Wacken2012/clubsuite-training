<?php
namespace OCA\ClubSuiteTraining\Listener;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

use OCA\ClubSuiteTraining\Events\TrainingCallbackEvent;

class TrainingCallbackEventListener implements IEventListener {
    public function handle(Event $event): void {
        if (!($event instanceof TrainingCallbackEvent)) {
            return;
        }
        $payload = $event->getPayload();
        $event->triggerCallback(['handledBy' => 'Training', 'count' => count($payload)]);
    }
}
