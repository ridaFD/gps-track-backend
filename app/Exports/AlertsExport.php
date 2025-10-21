<?php

namespace App\Exports;

use App\Models\Alert;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AlertsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $params;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function query()
    {
        $query = Alert::query()->with(['device', 'geofence']);

        // Apply filters
        if (isset($this->params['device_id'])) {
            $query->where('device_id', $this->params['device_id']);
        }

        if (isset($this->params['severity'])) {
            $query->where('severity', $this->params['severity']);
        }

        if (isset($this->params['type'])) {
            $query->where('type', $this->params['type']);
        }

        if (isset($this->params['from'])) {
            $query->where('created_at', '>=', $this->params['from']);
        }

        if (isset($this->params['to'])) {
            $query->where('created_at', '<=', $this->params['to']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Alert ID',
            'Device ID',
            'Device Name',
            'Geofence',
            'Type',
            'Severity',
            'Message',
            'Read',
            'Read At',
            'Created At',
        ];
    }

    public function map($alert): array
    {
        return [
            $alert->id,
            $alert->device_id,
            $alert->device?->name,
            $alert->geofence?->name,
            str_replace('_', ' ', ucwords($alert->type, '_')),
            ucfirst($alert->severity),
            $alert->message,
            $alert->read ? 'Yes' : 'No',
            $alert->read_at,
            $alert->created_at,
        ];
    }
}
