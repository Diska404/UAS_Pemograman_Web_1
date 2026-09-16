<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\{Auth, ReportAnalyticsService, ReportService};

final class ReportController extends Controller
{
    public function index(): void
    {
        $user = Auth::requireUser();
        $service = new ReportService();
        $report = $service->build($_GET);
        if (isset($_GET['format'])) {
            Auth::requireUser(true);
            $service->export($report, (string)$_GET['format']);
            return;
        }
        $analytics = (new ReportAnalyticsService())->summarize($report);
        $this->view('reports/index', ['title' => 'Laporan','report' => $report,'analytics' => $analytics,'options' => ResourceController::options(),'admin' => $user['role'] === 'Admin']);
    }
}
