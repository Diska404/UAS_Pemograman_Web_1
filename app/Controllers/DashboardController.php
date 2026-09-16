<?php

namespace App\Controllers;

use App\Core\{Controller, Database};
use App\Services\{Auth, DashboardService};

final class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireUser();
        $this->view('dashboard/index', ['title' => 'Dashboard'] + (new DashboardService())->data());
    }

    public function audit(): void
    {
        Auth::requireUser(true);
        $this->view('audit/index', ['title' => 'Audit Log','rows' => Database::query('SELECT a.*,u.name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 2000')->fetchAll()]);
    }
}
