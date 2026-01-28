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
use OCA\ClubSuiteTraining\Service\EventService;
use OCA\ClubSuiteTraining\Service\TalkRoomService;

class EventController extends Controller {
    private EventService $service;
    private TalkRoomService $talkRoomService;

    public function __construct(
        string $appName,
        IRequest $request,
        EventService $service,
        TalkRoomService $talkRoomService
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
        $this->talkRoomService = $talkRoomService;
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): JSONResponse {
        $events = $this->service->listEvents();
        
        // Enrich with Talk room data
        $enriched = array_map(function($e) {
            $data = is_array($e) ? $e : $e->jsonSerialize();
            $talkRoom = $this->talkRoomService->getTalkRoomForEvent($data['id']);
            if ($talkRoom) {
                $data['talk_room'] = $talkRoom->jsonSerialize();
            }
            return $data;
        }, $events);
        
        return new JSONResponse($enriched);
    }

    #[NoAdminRequired]
    public function create(): JSONResponse {
        $p = $this->request->getParams();
        $e = $this->service->createEvent(
            (int)($p['group_id'] ?? 0),
            $p['date'] ?? '',
            $p['start_time'] ?? '',
            $p['end_time'] ?? '',
            $p['location'] ?? null,
            $p['notes'] ?? null,
            $p['title'] ?? null
        );
        
        // Get Talk room data if available
        $data = ['id' => $e->getId()];
        $talkRoom = $this->talkRoomService->getTalkRoomForEvent($e->getId());
        if ($talkRoom) {
            $data['talk_room'] = $talkRoom->jsonSerialize();
        }
        
        return new JSONResponse($data, 201);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function show(int $id): JSONResponse {
        $e = $this->service->getEvent($id);
        if ($e === null) {
            return new JSONResponse(['message' => 'Not found'], 404);
        }
        
        $data = $e->jsonSerialize();
        
        // Add Talk room data
        $talkRoom = $this->talkRoomService->getTalkRoomForEvent($id);
        if ($talkRoom) {
            $data['talk_room'] = $talkRoom->jsonSerialize();
        }
        
        return new JSONResponse($data);
    }

    #[NoAdminRequired]
    public function update(int $id): JSONResponse {
        $p = $this->request->getParams();
        $updated = $this->service->updateEvent($id, $p);
        
        if (!$updated) {
            return new JSONResponse(['message' => 'Not found'], 404);
        }
        
        $data = $updated->jsonSerialize();
        $talkRoom = $this->talkRoomService->getTalkRoomForEvent($id);
        if ($talkRoom) {
            $data['talk_room'] = $talkRoom->jsonSerialize();
        }
        
        return new JSONResponse($data);
    }
}
