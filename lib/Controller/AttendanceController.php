<?php
/**
 * © 2026 Stefan Schulz – Alle Rechte vorbehalten.
 */
declare(strict_types=1);

namespace OCA\ClubSuiteTraining\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCA\ClubSuiteTraining\Service\AttendanceService;

class AttendanceController extends Controller {
    private AttendanceService $service;

    public function __construct(string $appName, IRequest $request, AttendanceService $service) {
        parent::__construct($appName, $request);
        $this->service = $service;
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): JSONResponse {
        $userId = $this->request->getParam('user_id');
        if ($userId) {
            return new JSONResponse($this->service->listByUser($userId));
        }
        $eventId = (int)($this->request->getParam('event_id') ?? 0);
        if ($eventId > 0) {
            return new JSONResponse($this->service->listByEvent($eventId));
        }
        $memberId = (int)($this->request->getParam('member_id') ?? 0);
        if ($memberId > 0) {
            return new JSONResponse($this->service->listByMember($memberId));
        }
        return new JSONResponse([]);
    }

    #[NoAdminRequired]
    public function create(): JSONResponse {
        $p = $this->request->getParams();
        $eventId = (int)($p['event_id'] ?? 0);
        $userId = (string)($p['user_id'] ?? '');
        $memberId = isset($p['member_id']) ? (int)$p['member_id'] : null;

        if ($eventId <= 0 || empty($userId)) {
            return new JSONResponse(['error' => 'Missing event_id or user_id'], 400);
        }

        $a = $this->service->checkIn($eventId, $userId, $memberId);
        return new JSONResponse($a, 201);
    }

    #[NoAdminRequired]
    public function update(int $id): JSONResponse {
        $status = $this->request->getParam('status');
        if (!in_array($status, ['present', 'absent', 'excused'])) {
            return new JSONResponse(['error' => 'Invalid status'], 400);
        }
        $this->service->setStatus($id, $status);
        return new JSONResponse(['status' => 'ok']);
    }
}
