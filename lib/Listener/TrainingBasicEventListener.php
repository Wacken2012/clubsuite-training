<?php
namespace OCA\ClubSuiteTraining\Listener;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

use OCA\ClubSuiteTraining\Events\TrainingBasicEvent;

class TrainingBasicEventListener implements IEventListener {
    public function handle(Event $event): void {
        if (!($event instanceof TrainingBasicEvent)) {
            return;
        }
        error_log('TrainingBasicEvent received in Training: ' . $event->getId());
    }
}
