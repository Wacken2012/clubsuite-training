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
use OCA\ClubSuiteTraining\Service\GroupService;

class GroupController extends Controller {
    private GroupService $service;

    public function __construct(string $appName, IRequest $request, GroupService $service) {
        parent::__construct($appName, $request);
        $this->service = $service;
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): JSONResponse {
        return new JSONResponse($this->service->listGroups());
    }

    #[NoAdminRequired]
    public function create(): JSONResponse {
        $p = $this->request->getParams();
        $g = $this->service->createGroup($p['name'] ?? 'Untitled', $p['description'] ?? null);
        return new JSONResponse(['id' => $g->getId()], 201);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function show(int $id): JSONResponse {
        $g = $this->service->getGroup($id);
        if ($g === null) {
            return new JSONResponse(['message' => 'Not found'], 404);
        }
        return new JSONResponse($g);
    }
}
