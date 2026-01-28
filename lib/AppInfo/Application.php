<?php
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\AppInfo;

use OCA\ClubSuiteTraining\Privacy\Register;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IContainer;
use OCA\ClubSuiteTraining\Service\CacheService;
use OCA\ClubSuiteTraining\Service\EventService;
use OCA\ClubSuiteTraining\Listener\TrainingBasicEventListener;
use OCA\ClubSuiteTraining\Listener\TrainingCallbackEventListener;
use OCA\ClubSuiteTraining\Listener\TrainingRequestDataEventListener;
use OCA\ClubSuiteTraining\Events\TrainingBasicEvent;
use OCA\ClubSuiteTraining\Events\TrainingCallbackEvent;
use OCA\ClubSuiteTraining\Events\TrainingRequestDataEvent;

if (!\class_exists('OCA\ClubSuiteTraining\AppInfo\Application', false)) {
class Application extends App implements IBootstrap {
    public const APP_ID = 'clubsuite-training';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        $context->registerEventListener(TrainingBasicEvent::class, TrainingBasicEventListener::class);
        $context->registerEventListener(TrainingCallbackEvent::class, TrainingCallbackEventListener::class);
        $context->registerEventListener(TrainingRequestDataEvent::class, TrainingRequestDataEventListener::class);
    }

    public function boot(IBootContext $context): void {
        $context->injectFn(function(\Psr\Container\ContainerInterface $container) {
            if (\interface_exists('\OCP\Privacy\IManager')) {
                $container->get(\OCP\Privacy\IManager::class)->registerProvider(Register::class);
            }
        });
    }
}

}
