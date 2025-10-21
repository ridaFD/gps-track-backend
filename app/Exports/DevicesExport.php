<?php

namespace App\Exports;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DevicesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $params;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function query()
    {
        $query = Device::query()->with('lastPosition');

        // Apply filters from params
        if (isset($this->params['status'])) {
            $query->where('status', $this->params['status']);
        }

        if (isset($this->params['type'])) {
            $query->where('type', $this->params['type']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'IMEI',
            'Type',
            'Status',
            'Plate Number',
            'Model',
            'Color',
            'Year',
            'Driver Name',
            'Driver Phone',
            'Last Latitude',
            'Last Longitude',
            'Last Speed (km/h)',
            'Last Update',
            'Created At',
        ];
    }

    public function map($device): array
    {
        return [
            $device->id,
            $device->name,
            $device->imei,
            $device->type,
            $device->status,
            $device->plate_number,
            $device->model,
            $device->color,
            $device->year,
            $device->driver_name,
            $device->driver_phone,
            $device->lastPosition?->latitude,
            $device->lastPosition?->longitude,
            $device->lastPosition?->speed,
            $device->lastPosition?->device_time,
            $device->created_at,
        ];
    }
}
