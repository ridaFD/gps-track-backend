<?php

namespace App\Orchid\Screens;

use App\Models\Geofence;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class GeofenceListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'geofences' => Geofence::filters()
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
        return '🗺️ Geofences';
    }

    /**
     * The description of the screen.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Manage geographical boundaries and zones';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Create New Geofence')
                ->icon('plus')
                ->route('platform.geofences.create'),
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
            Layout::table('geofences', [
                TD::make('id', 'ID')
                    ->sort()
                    ->filter(TD::FILTER_TEXT),
                
                TD::make('name', 'Name')
                    ->sort()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn (Geofence $geofence) => Link::make($geofence->name)
                        ->route('platform.geofences.edit', $geofence)),
                
                TD::make('type', 'Type')
                    ->sort()
                    ->filter(TD::FILTER_SELECT, [
                        'circle' => 'Circle',
                        'polygon' => 'Polygon',
                        'rectangle' => 'Rectangle',
                    ])
                    ->render(fn (Geofence $geofence) => ucfirst($geofence->type)),
                
                TD::make('color', 'Color')
                    ->render(fn (Geofence $geofence) => 
                        "<div style='width: 40px; height: 20px; background-color: {$geofence->color}; border: 1px solid #ddd; border-radius: 3px;'></div>"
                    ),
                
                TD::make('active', 'Status')
                    ->sort()
                    ->filter(TD::FILTER_SELECT, [
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->render(function (Geofence $geofence) {
                        $color = $geofence->active ? 'success' : 'secondary';
                        $text = $geofence->active ? 'Active' : 'Inactive';
                        return "<span class='badge bg-{$color}'>{$text}</span>";
                    }),
                
                TD::make('alert_on_enter', 'Alert on Enter')
                    ->render(fn (Geofence $geofence) => $geofence->alert_on_enter ? '✅' : '—'),
                
                TD::make('alert_on_exit', 'Alert on Exit')
                    ->render(fn (Geofence $geofence) => $geofence->alert_on_exit ? '✅' : '—'),
                
                TD::make('description', 'Description')
                    ->render(fn (Geofence $geofence) => 
                        \Illuminate\Support\Str::limit($geofence->description, 50)
                    ),
                
                TD::make('created_at', 'Created')
                    ->sort()
                    ->render(fn (Geofence $geofence) => $geofence->created_at->format('Y-m-d H:i')),
                
                TD::make('actions', 'Actions')
                    ->alignRight()
                    ->render(fn (Geofence $geofence) => 
                        Link::make('Edit')
                            ->icon('pencil')
                            ->route('platform.geofences.edit', $geofence)
                    ),
            ]),
        ];
    }
}
