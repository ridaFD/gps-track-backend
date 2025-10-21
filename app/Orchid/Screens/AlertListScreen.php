<?php

namespace App\Orchid\Screens;

use App\Models\Alert;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class AlertListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'alerts' => Alert::with(['device', 'geofence'])
                ->filters()
                ->defaultSort('created_at', 'desc')
                ->paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return '🚨 Alerts';
    }

    /**
     * The description of the screen.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'View and manage system alerts';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Mark All as Read')
                ->icon('check')
                ->method('markAllAsRead')
                ->confirm('Mark all alerts as read?'),
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
            Layout::table('alerts', [
                TD::make('id', 'ID')
                    ->sort()
                    ->filter(TD::FILTER_TEXT),
                
                TD::make('read', 'Status')
                    ->sort()
                    ->filter(TD::FILTER_SELECT, [
                        '0' => 'Unread',
                        '1' => 'Read',
                    ])
                    ->render(function (Alert $alert) {
                        $color = $alert->read ? 'secondary' : 'primary';
                        $text = $alert->read ? 'Read' : 'Unread';
                        return "<span class='badge bg-{$color}'>{$text}</span>";
                    }),
                
                TD::make('severity', 'Severity')
                    ->sort()
                    ->filter(TD::FILTER_SELECT, [
                        'info' => 'Info',
                        'warning' => 'Warning',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ])
                    ->render(function (Alert $alert) {
                        $colors = [
                            'info' => 'info',
                            'warning' => 'warning',
                            'high' => 'danger',
                            'critical' => 'dark',
                        ];
                        return "<span class='badge bg-{$colors[$alert->severity]}'>" . ucfirst($alert->severity) . "</span>";
                    }),
                
                TD::make('type', 'Type')
                    ->sort()
                    ->filter(TD::FILTER_SELECT, [
                        'geofence_entry' => 'Geofence Entry',
                        'geofence_exit' => 'Geofence Exit',
                        'speed_limit' => 'Speed Limit',
                        'idle' => 'Idle',
                        'low_battery' => 'Low Battery',
                        'sos' => 'SOS',
                        'power_cut' => 'Power Cut',
                        'movement' => 'Movement',
                        'other' => 'Other',
                    ])
                    ->render(fn (Alert $alert) => str_replace('_', ' ', ucwords($alert->type, '_'))),
                
                TD::make('device.name', 'Device')
                    ->render(fn (Alert $alert) => $alert->device?->name ?? 'N/A'),
                
                TD::make('geofence.name', 'Geofence')
                    ->render(fn (Alert $alert) => $alert->geofence?->name ?? '—'),
                
                TD::make('message', 'Message')
                    ->render(fn (Alert $alert) => 
                        \Illuminate\Support\Str::limit($alert->message, 60)
                    ),
                
                TD::make('created_at', 'Created')
                    ->sort()
                    ->render(fn (Alert $alert) => $alert->created_at->format('Y-m-d H:i:s')),
                
                TD::make('actions', 'Actions')
                    ->alignRight()
                    ->render(function (Alert $alert) {
                        if (!$alert->read) {
                            return Button::make('Mark as Read')
                                ->icon('check')
                                ->method('markAsRead', ['id' => $alert->id]);
                        }
                        return '—';
                    }),
            ]),
        ];
    }

    /**
     * Mark alert as read.
     *
     * @param int $id
     */
    public function markAsRead(int $id)
    {
        Alert::find($id)->update([
            'read' => true,
            'read_at' => now(),
        ]);

        Toast::info('Alert marked as read.');
    }

    /**
     * Mark all alerts as read.
     */
    public function markAllAsRead()
    {
        Alert::where('read', false)->update([
            'read' => true,
            'read_at' => now(),
        ]);

        Toast::info('All alerts marked as read.');
    }
}
