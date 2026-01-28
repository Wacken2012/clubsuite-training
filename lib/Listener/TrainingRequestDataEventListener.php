<?php
namespace OCA\ClubSuiteTraining\Listener;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

use OCA\ClubSuiteTraining\Events\TrainingRequestDataEvent;

class TrainingRequestDataEventListener implements IEventListener {
    public function handle(Event $event): void {
        if (!($event instanceof TrainingRequestDataEvent)) {
            return;
        }
        $data = ['app' => 'Training', 'events' => []];
        $event->respond($data);
    }
}
