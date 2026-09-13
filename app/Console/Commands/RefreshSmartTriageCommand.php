<?php

namespace App\Console\Commands;

use App\Models\LayananPegawai;
use App\Services\Intelligence\SmartTriageService;
use Illuminate\Console\Command;

class RefreshSmartTriageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intelligence:refresh-smart-triage {--include-cuti : Include cuti usulan in triage refresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate priority_score, sla_due_at, and sla_risk for layanan usulan.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(SmartTriageService $triageService)
    {
        $query = LayananPegawai::query();

        if (!$this->option('include-cuti')) {
            $query->whereDoesntHave('cutiDetail');
        }

        $total = (clone $query)->count();
        $refreshed = $triageService->refreshForBuilder($query);

        $scopeLabel = $this->option('include-cuti') ? 'semua layanan' : 'layanan non-cuti';
        $this->info('Smart Triage refresh selesai untuk ' . $scopeLabel . ': ' . $refreshed . '/' . $total . ' data.');

        return self::SUCCESS;
    }
}

