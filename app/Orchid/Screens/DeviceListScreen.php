<?php

namespace App\Orchid\Screens;

use App\Models\Device;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class DeviceListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'devices' => Device::with('lastPosition')
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate(15),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return '🚗 Devices';
    }

    /**
     * The description of the screen.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Manage all GPS tracking devices';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Create New Device')
                ->icon('plus')
                ->route('platform.devices.edit'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('devices', [
                TD::make('id', 'ID')
                    ->sort()
                    ->filter(TD::FILTER_TEXT),
                
                TD::make('name', 'Device Name')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn (Device $device) => Link::make($device->name)
                        ->route('platform.devices.edit', $device)),
                
                TD::make('imei', 'IMEI')
                    ->sort()
                    ->filter(TD::FILTER_TEXT),
                
                TD::make('type', 'Type')
                    ->sort()
                    ->filter(TD::FILTER_SELECT, [
                        'car' => 'Car',
                        'truck' => 'Truck',
                        'van' => 'Van',
                        'motorcycle' => 'Motorcycle',
                        'equipment' => 'Equipment',
                        'other' => 'Other',
                    ])
                    ->render(fn (Device $device) => ucfirst($device->type)),
                
                TD::make('status', 'Status')
                    ->sort()
                    ->filter(TD::FILTER_SELECT, [
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'maintenance' => 'Maintenance',
                    ])
                    ->render(function (Device $device) {
                        $colors = [
                            'active' => 'success',
                            'inactive' => 'secondary',
                            'maintenance' => 'warning',
                        ];
                        return "<span class='badge bg-{$colors[$device->status]}'>" . ucfirst($device->status) . "</span>";
                    }),
                
                TD::make('plate_number', 'Plate Number')
                    ->filter(TD::FILTER_TEXT),
                
                TD::make('driver_name', 'Driver')
                    ->filter(TD::FILTER_TEXT),
                
                TD::make('last_position', 'Last Position')
                    ->render(function (Device $device) {
                        if ($device->lastPosition) {
                            $pos = $device->lastPosition;
                            return "{$pos->latitude}, {$pos->longitude}<br><small>{$pos->device_time}</small>";
                        }
                        return '<span class="text-muted">No data</span>';
                    }),
                
                TD::make('created_at', 'Created')
                    ->sort()
                    ->render(fn (Device $device) => $device->created_at->format('Y-m-d H:i')),
                
                TD::make('actions', 'Actions')
                    ->alignRight()
                    ->render(fn (Device $device) => 
                        Link::make('Edit')
                            ->icon('pencil')
                            ->route('platform.devices.edit', $device)
                    ),
            ]),
        ];
    }
}
