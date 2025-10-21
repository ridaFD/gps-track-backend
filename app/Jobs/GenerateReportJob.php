<?php

namespace App\Jobs;

use App\Exports\DevicesExport;
use App\Exports\TripsExport;
use App\Exports\AlertsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $reportType;
    public $params;
    public $filename;

    /**
     * Create a new job instance.
     */
    public function __construct(string $reportType, array $params = [])
    {
        $this->reportType = $reportType;
        $this->params = $params;
        $this->filename = "reports/{$reportType}_" . now()->format('Y-m-d_His') . ".xlsx";
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $export = match ($this->reportType) {
            'devices' => new DevicesExport($this->params),
            'trips' => new TripsExport($this->params),
            'alerts' => new AlertsExport($this->params),
            default => throw new \InvalidArgumentException("Unknown report type: {$this->reportType}"),
        };

        // Generate and store the report
        Excel::store($export, $this->filename, 'local');

        // TODO: Send notification to user that report is ready
        // TODO: Store report metadata in database with download link
    }

    /**
     * Get the download path for this report
     */
    public function getDownloadPath(): string
    {
        return storage_path("app/{$this->filename}");
    }
}
