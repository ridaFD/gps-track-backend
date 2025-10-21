<?php

namespace App\Exports;

use App\Models\Position;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TripsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $params;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function query()
    {
        $query = Position::query()->with('device');

        // Apply filters
        if (isset($this->params['device_id'])) {
            $query->where('device_id', $this->params['device_id']);
        }

        if (isset($this->params['from'])) {
            $query->where('device_time', '>=', $this->params['from']);
        }

        if (isset($this->params['to'])) {
            $query->where('device_time', '<=', $this->params['to']);
        }

        return $query->orderBy('device_time', 'desc');
    }

    public function headings(): array
    {
        return [
            'Position ID',
            'Device ID',
            'Device Name',
            'Latitude',
            'Longitude',
            'Altitude (m)',
            'Speed (km/h)',
            'Heading (°)',
            'Satellites',
            'Fuel Level (%)',
            'Ignition',
            'Address',
            'Device Time',
            'Received At',
        ];
    }

    public function map($position): array
    {
        return [
            $position->id,
            $position->device_id,
            $position->device?->name,
            $position->latitude,
            $position->longitude,
            $position->altitude,
            $position->speed,
            $position->heading,
            $position->satellites,
            $position->fuel_level,
            $position->ignition ? 'On' : 'Off',
            $position->address,
            $position->device_time,
            $position->created_at,
        ];
    }
}
