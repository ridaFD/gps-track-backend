<?php

namespace App\Orchid\Screens;

use App\Models\Position;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Select;

class PositionListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'positions' => Position::with('device')
                ->filters()
                ->defaultSort('device_time', 'desc')
                ->paginate(50),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return '📍 GPS Positions';
    }

    /**
     * The description of the screen.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'View GPS tracking data and telemetry';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('positions', [
                TD::make('id', 'ID')
                    ->sort()
                    ->filter(TD::FILTER_TEXT),
                
                TD::make('device.name', 'Device')
                    ->sort()
                    ->render(fn (Position $position) => $position->device?->name ?? 'Unknown'),
                
                TD::make('latitude', 'Latitude')
                    ->sort()
                    ->render(fn (Position $position) => number_format($position->latitude, 6)),
                
                TD::make('longitude', 'Longitude')
                    ->sort()
                    ->render(fn (Position $position) => number_format($position->longitude, 6)),
                
                TD::make('coordinates', 'Location')
                    ->render(function (Position $position) {
                        $lat = number_format($position->latitude, 6);
                        $lng = number_format($position->longitude, 6);
                        return "<a href='https://www.google.com/maps?q={$lat},{$lng}' target='_blank'>View on Map 🗺️</a>";
                    }),
                
                TD::make('speed', 'Speed')
                    ->sort()
                    ->render(fn (Position $position) => 
                        $position->speed ? number_format($position->speed, 1) . ' km/h' : '—'
                    ),
                
                TD::make('heading', 'Heading')
                    ->render(fn (Position $position) => 
                        $position->heading !== null ? $position->heading . '°' : '—'
                    ),
                
                TD::make('altitude', 'Altitude')
                    ->render(fn (Position $position) => 
                        $position->altitude ? number_format($position->altitude, 1) . ' m' : '—'
                    ),
                
                TD::make('satellites', 'Satellites')
                    ->render(fn (Position $position) => $position->satellites ?? '—'),
                
                TD::make('fuel_level', 'Fuel')
                    ->render(fn (Position $position) => 
                        $position->fuel_level !== null ? $position->fuel_level . '%' : '—'
                    ),
                
                TD::make('ignition', 'Ignition')
                    ->render(fn (Position $position) => 
                        $position->ignition !== null ? ($position->ignition ? '✅ On' : '❌ Off') : '—'
                    ),
                
                TD::make('device_time', 'Device Time')
                    ->sort()
                    ->render(fn (Position $position) => $position->device_time->format('Y-m-d H:i:s')),
                
                TD::make('created_at', 'Received At')
                    ->sort()
                    ->render(fn (Position $position) => $position->created_at->format('Y-m-d H:i:s')),
            ]),
        ];
    }
}
