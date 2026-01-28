<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Service;

use OCA\ClubSuiteTraining\Db\TalkRoomEntity;
use OCA\ClubSuiteTraining\Db\TalkRoomMapper;
use OCA\ClubSuiteTraining\Db\EventMapper;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IURLGenerator;
use Psr\Log\LoggerInterface;
use DateTime;
use Exception;

/**
 * Service for Nextcloud Talk integration.
 * Handles room creation, participant management, and message posting.
 */
class TalkRoomService {
    private TalkRoomMapper $mapper;
    private EventMapper $eventMapper;
    private IClientService $clientService;
    private IConfig $config;
    private IURLGenerator $urlGenerator;
    private LoggerInterface $logger;

    public function __construct(
        TalkRoomMapper $mapper,
        EventMapper $eventMapper,
        IClientService $clientService,
        IConfig $config,
        IURLGenerator $urlGenerator,
        LoggerInterface $logger
    ) {
        $this->mapper = $mapper;
        $this->eventMapper = $eventMapper;
        $this->clientService = $clientService;
        $this->config = $config;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    /**
     * Create a Talk room for a training event
     * Uses Talk REST API: POST /ocs/v2.php/apps/spreed/api/v4/room
     */
    public function createTalkRoomForEvent(int $eventId): ?TalkRoomEntity {
        try {
            // Check if room already exists
            $existing = $this->mapper->findByEvent($eventId);
            if ($existing !== null) {
                $this->logger->info("Talk room already exists for event {$eventId}");
                return $existing;
            }

            // Get event details
            $event = $this->eventMapper->find($eventId);
            if (!$event) {
                $this->logger->error("Event {$eventId} not found");
                return null;
            }

            // Create room via Talk API
            $roomToken = $this->createTalkRoom($event->getTitle() ?: "Training {$eventId}");
            if (!$roomToken) {
                $this->logger->error("Failed to create Talk room for event {$eventId}");
                return null;
            }

            // Build room URL
            $roomUrl = $this->urlGenerator->linkToAbsolute('', '') . 'apps/spreed/?token=' . $roomToken;

            // Save to database
            $talkRoom = TalkRoomEntity::build([
                'eventId' => $eventId,
                'roomToken' => $roomToken,
                'roomUrl' => $roomUrl,
                'createdAt' => new DateTime(),
            ]);

            $saved = $this->mapper->insert($talkRoom);
            $this->logger->info("Talk room created for event {$eventId}, token: {$roomToken}");
            return $saved;

        } catch (Exception $e) {
            $this->logger->error("Error creating Talk room: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Talk room for an event
     */
    public function getTalkRoomForEvent(int $eventId): ?TalkRoomEntity {
        return $this->mapper->findByEvent($eventId);
    }

    /**
     * Create Talk room via OCS API
     * POST /ocs/v2.php/apps/spreed/api/v4/room
     */
    private function createTalkRoom(string $roomName): ?string {
        try {
            $client = $this->clientService->newClient();
            $instanceUrl = $this->config->getSystemValue('overwrite.cli.url', '');
            
            if (!$instanceUrl) {
                $this->logger->error("Nextcloud instance URL not configured");
                return null;
            }

            $url = rtrim($instanceUrl, '/') . '/ocs/v2.php/apps/spreed/api/v4/room';

            $response = $client->post($url, [
                'headers' => [
                    'OCS-APIRequest' => 'true',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'roomType' => 2,  // 2 = group room
                    'roomName' => $roomName,
                    'comment' => 'Auto-generated room for training event'
                ],
                'auth' => [$this->getAdminUser(), $this->getAdminPassword()]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200 && $statusCode !== 201) {
                $this->logger->error("Talk API returned status {$statusCode}");
                return null;
            }

            $body = json_decode($response->getBody(), true);
            if (!isset($body['ocs']['data']['token'])) {
                $this->logger->error("No token in Talk API response");
                return null;
            }

            return $body['ocs']['data']['token'];

        } catch (Exception $e) {
            $this->logger->error("Error calling Talk API: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add user to Talk room
     * POST /ocs/v2.php/apps/spreed/api/v4/room/{token}/participants
     */
    public function addParticipantToRoom(string $roomToken, string $nextcloudUserId): bool {
        try {
            $client = $this->clientService->newClient();
            $instanceUrl = $this->config->getSystemValue('overwrite.cli.url', '');
            
            if (!$instanceUrl) {
                $this->logger->error("Nextcloud instance URL not configured");
                return false;
            }

            $url = rtrim($instanceUrl, '/') . '/ocs/v2.php/apps/spreed/api/v4/room/' . $roomToken . '/participants';

            $response = $client->post($url, [
                'headers' => [
                    'OCS-APIRequest' => 'true',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'newParticipant' => $nextcloudUserId,
                ],
                'auth' => [$this->getAdminUser(), $this->getAdminPassword()]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200 && $statusCode !== 201) {
                $this->logger->warning("Talk API returned status {$statusCode} when adding participant {$nextcloudUserId} to {$roomToken}");
                return false;
            }

            $this->logger->info("Added participant {$nextcloudUserId} to Talk room {$roomToken}");
            return true;

        } catch (Exception $e) {
            $this->logger->error("Error adding participant to Talk room: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send message to Talk room (optional feature for event updates)
     * POST /ocs/v2.php/apps/spreed/api/v4/room/{token}/message
     */
    public function sendMessageToRoom(string $roomToken, string $message): bool {
        try {
            $client = $this->clientService->newClient();
            $instanceUrl = $this->config->getSystemValue('overwrite.cli.url', '');
            
            if (!$instanceUrl) {
                $this->logger->error("Nextcloud instance URL not configured");
                return false;
            }

            $url = rtrim($instanceUrl, '/') . '/ocs/v2.php/apps/spreed/api/v4/room/' . $roomToken . '/message';

            $response = $client->post($url, [
                'headers' => [
                    'OCS-APIRequest' => 'true',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'message' => $message,
                ],
                'auth' => [$this->getAdminUser(), $this->getAdminPassword()]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200 && $statusCode !== 201) {
                $this->logger->warning("Talk API returned status {$statusCode} when posting message to {$roomToken}");
                return false;
            }

            $this->logger->info("Posted message to Talk room {$roomToken}");
            return true;

        } catch (Exception $e) {
            $this->logger->error("Error posting message to Talk room: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get admin credentials from Nextcloud config
     */
    private function getAdminUser(): string {
        // Use system account or configured admin
        return $this->config->getSystemValue('admin_user', 'admin');
    }

    /**
     * Get admin app password for API calls
     */
    private function getAdminPassword(): string {
        // This should be configured securely in Nextcloud settings
        // For production, use app tokens instead
        return $this->config->getSystemValue('admin_password', '');
    }
}
