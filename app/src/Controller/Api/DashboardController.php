<?php

namespace App\Controller\Api;

use App\Domain\Dashboard\DashboardStatistics;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/dashboard')]
class DashboardController
{
    #[Route('/counts', methods: ['GET'])]
    #[IsGranted('DASHBOARD_VIEW')]
    public function counts(DashboardStatistics $statistics): Response
    {
        return new JsonResponse($statistics->counts());
    }

    /** ?days=14 (1..90) */
    #[Route('/activity', methods: ['GET'])]
    #[IsGranted('DASHBOARD_VIEW')]
    public function activity(Request $request, DashboardStatistics $statistics): Response
    {
        $days = $request->query->getInt('days', 14);
        return new JsonResponse([
            'days' => max(1, min(DashboardStatistics::MAX_DAYS, $days)),
            'buckets' => $statistics->activity($days),
        ]);
    }
}
