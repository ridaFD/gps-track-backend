<?php

namespace App\Orchid\Screens;

use App\Models\Geofence;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Alert as AlertFacade;

class GeofenceEditScreen extends Screen
{
    /**
     * @var Geofence
     */
    public $geofence;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Geofence $geofence): iterable
    {
        return [
            'geofence' => $geofence,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->geofence->exists ? 'Edit Geofence: ' . $this->geofence->name : 'Create New Geofence';
    }

    /**
     * The description of the screen.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Geofence configuration and alerts';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Save')
                ->icon('check')
                ->method('save'),

            Button::make('Delete')
                ->icon('trash')
                ->method('remove')
                ->canSee($this->geofence->exists)
                ->confirm('Are you sure you want to delete this geofence?'),
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
            Layout::rows([
                Input::make('geofence.name')
                    ->title('Geofence Name')
                    ->placeholder('Enter geofence name')
                    ->required(),

                TextArea::make('geofence.description')
                    ->title('Description')
                    ->rows(2)
                    ->placeholder('Brief description of this geofence'),

                Select::make('geofence.type')
                    ->title('Geofence Type')
                    ->options([
                        'circle' => 'Circle',
                        'polygon' => 'Polygon',
                        'rectangle' => 'Rectangle',
                    ])
                    ->required()
                    ->help('Define the shape of the geofence'),

                Input::make('geofence.center_lat')
                    ->title('Center Latitude')
                    ->type('number')
                    ->step('0.0000001')
                    ->placeholder('40.7128')
                    ->help('For circle geofences'),

                Input::make('geofence.center_lng')
                    ->title('Center Longitude')
                    ->type('number')
                    ->step('0.0000001')
                    ->placeholder('-74.0060')
                    ->help('For circle geofences'),

                Input::make('geofence.radius')
                    ->title('Radius (meters)')
                    ->type('number')
                    ->placeholder('500')
                    ->help('For circle geofences'),

                TextArea::make('geofence.coordinates')
                    ->title('Coordinates (JSON)')
                    ->rows(4)
                    ->placeholder('{"type": "Polygon", "coordinates": [[...]]}')
                    ->help('GeoJSON format for polygon/rectangle'),

                Input::make('geofence.color')
                    ->title('Color')
                    ->type('color')
                    ->value('#3B82F6')
                    ->help('Display color on map'),

                CheckBox::make('geofence.active')
                    ->title('Active')
                    ->placeholder('Enable this geofence')
                    ->sendTrueOrFalse(),

                CheckBox::make('geofence.alert_on_enter')
                    ->title('Alert on Entry')
                    ->placeholder('Create alert when device enters this geofence')
                    ->sendTrueOrFalse(),

                CheckBox::make('geofence.alert_on_exit')
                    ->title('Alert on Exit')
                    ->placeholder('Create alert when device exits this geofence')
                    ->sendTrueOrFalse(),
            ]),
        ];
    }

    /**
     * Save the geofence.
     *
     * @param Geofence $geofence
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Geofence $geofence, Request $request)
    {
        $data = $request->get('geofence');
        
        // Parse coordinates if it's a JSON string
        if (isset($data['coordinates']) && is_string($data['coordinates'])) {
            $data['coordinates'] = json_decode($data['coordinates'], true);
        }
        
        $geofence->fill($data)->save();

        AlertFacade::info('Geofence has been saved successfully.');

        return redirect()->route('platform.geofences.list');
    }

    /**
     * Remove the geofence.
     *
     * @param Geofence $geofence
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Geofence $geofence)
    {
        $geofence->delete();

        AlertFacade::info('Geofence has been deleted successfully.');

        return redirect()->route('platform.geofences.list');
    }
}
