<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Service;

use OCA\ClubSuiteTraining\Db\AttendanceMapper;
use OCA\ClubSuiteTraining\Db\AttendanceEntity;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use Psr\Log\LoggerInterface;
use DateTime;
use Exception;

class AttendanceService {
    private AttendanceMapper $mapper;
    private TalkRoomService $talkRoomService;
    private IClientService $clientService;
    private IConfig $config;
    private LoggerInterface $logger;

    public function __construct(
        AttendanceMapper $mapper,
        TalkRoomService $talkRoomService,
        IClientService $clientService,
        IConfig $config,
        LoggerInterface $logger
    ) {
        $this->mapper = $mapper;
        $this->talkRoomService = $talkRoomService;
        $this->clientService = $clientService;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function listByEvent(int $eventId): array {
        return $this->mapper->findByEvent($eventId);
    }

    public function listByUser(string $userId): array {
        return $this->mapper->findWithEventByUser($userId);
    }

    public function listByMember(int $memberId): array {
        return $this->mapper->findByMember($memberId);
    }

    public function checkIn(int $eventId, string $userId, ?int $memberId = null): AttendanceEntity {
        $a = AttendanceEntity::build([
            'eventId' => $eventId,
            'userId' => $userId,
            'status' => 'present',
            'memberId' => $memberId,
            'checkedInAt' => new DateTime(),
        ]);
        $created = $this->mapper->create($a);

        // Invite participant to Talk room
        if ($memberId) {
            $this->inviteParticipantToTalkRoom($eventId, $memberId);
        }

        return $created;
    }

    public function checkOut(int $attendanceId): AttendanceEntity {
        $a = $this->mapper->findById($attendanceId);
        if (!$a) {
            throw new Exception('Attendance record not found');
        }
        $a->setStatus('present');
        $a->setCheckedOutAt(new DateTime());
        return $this->mapper->update($a);
    }

    public function setStatus(int $id, string $status): void {
        $this->mapper->updateStatus($id, $status);
    }

    public function getAttendanceForEvent(int $eventId): array {
        return $this->mapper->findByEvent($eventId);
    }

    public function getAttendanceForMember(int $memberId): array {
        return $this->mapper->findByMember($memberId);
    }

    /**
     * Invite a participant to the Talk room for an event
     */
    private function inviteParticipantToTalkRoom(int $eventId, int $memberId): void {
        try {
            // Get Talk room for the event
            $talkRoom = $this->talkRoomService->getTalkRoomForEvent($eventId);
            if (!$talkRoom) {
                $this->logger->debug("No Talk room found for event {$eventId}");
                return;
            }

            // Resolve member_id → nextcloud_user_id via Core-App
            $nextcloudUserId = $this->resolveNextcloudUserId($memberId);
            if (!$nextcloudUserId) {
                $this->logger->debug("Could not resolve Nextcloud user ID for member {$memberId}");
                return;
            }

            // Add participant to Talk room
            $this->talkRoomService->addParticipantToRoom($talkRoom->getRoomToken(), $nextcloudUserId);

        } catch (Exception $e) {
            $this->logger->error("Error inviting participant to Talk room: " . $e->getMessage());
        }
    }

    /**
     * Resolve member_id to nextcloud_user_id via Core-App API
     * GET /apps/clubsuite-core/members/{id}/talk-user
     */
    private function resolveNextcloudUserId(int $memberId): ?string {
        try {
            $client = $this->clientService->newClient();
            $instanceUrl = $this->config->getSystemValue('overwrite.cli.url', '');
            
            if (!$instanceUrl) {
                $this->logger->error("Nextcloud instance URL not configured");
                return null;
            }

            $url = rtrim($instanceUrl, '/') . '/ocs/v2.php/apps/clubsuite-core/members/' . $memberId . '/talk-user';

            $response = $client->get($url, [
                'headers' => [
                    'OCS-APIRequest' => 'true',
                ],
                'auth' => [$this->getAdminUser(), $this->getAdminPassword()]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $this->logger->debug("Core-App returned status {$statusCode} for member {$memberId}");
                return null;
            }

            $body = json_decode($response->getBody(), true);
            if (!isset($body['ocs']['data']['nextcloud_user_id'])) {
                $this->logger->debug("No nextcloud_user_id in Core-App response for member {$memberId}");
                return null;
            }

            return $body['ocs']['data']['nextcloud_user_id'];

        } catch (Exception $e) {
            $this->logger->error("Error resolving Nextcloud user ID: " . $e->getMessage());
            return null;
        }
    }

    private function getAdminUser(): string {
        return $this->config->getSystemValue('admin_user', 'admin');
    }

    private function getAdminPassword(): string {
        return $this->config->getSystemValue('admin_password', '');
    }
}
