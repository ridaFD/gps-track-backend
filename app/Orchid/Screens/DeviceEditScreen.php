<?php

namespace App\Orchid\Screens;

use App\Models\Device;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Alert as AlertFacade;

class DeviceEditScreen extends Screen
{
    /**
     * @var Device
     */
    public $device;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Device $device): iterable
    {
        return [
            'device' => $device,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->device->exists ? 'Edit Device: ' . $this->device->name : 'Create New Device';
    }

    /**
     * The description of the screen.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Device details and configuration';
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
                ->canSee($this->device->exists)
                ->confirm('Are you sure you want to delete this device?'),
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
                Input::make('device.name')
                    ->title('Device Name')
                    ->placeholder('Enter device name')
                    ->required(),

                Input::make('device.imei')
                    ->title('IMEI / Unique Identifier')
                    ->placeholder('Enter device IMEI or unique ID')
                    ->help('Unique identifier for the GPS device'),

                Select::make('device.type')
                    ->title('Device Type')
                    ->options([
                        'car' => 'Car',
                        'truck' => 'Truck',
                        'van' => 'Van',
                        'motorcycle' => 'Motorcycle',
                        'equipment' => 'Equipment',
                        'other' => 'Other',
                    ])
                    ->required(),

                Select::make('device.status')
                    ->title('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'maintenance' => 'Maintenance',
                    ])
                    ->required(),

                Input::make('device.plate_number')
                    ->title('Plate Number')
                    ->placeholder('Enter vehicle plate number'),

                Input::make('device.model')
                    ->title('Model')
                    ->placeholder('Vehicle model'),

                Input::make('device.color')
                    ->title('Color')
                    ->placeholder('Vehicle color'),

                Input::make('device.year')
                    ->title('Year')
                    ->type('number')
                    ->placeholder('Manufacturing year'),

                Input::make('device.driver_name')
                    ->title('Driver Name')
                    ->placeholder('Assigned driver name'),

                Input::make('device.driver_phone')
                    ->title('Driver Phone')
                    ->placeholder('Driver contact number'),

                TextArea::make('device.notes')
                    ->title('Notes')
                    ->rows(3)
                    ->placeholder('Additional notes about this device'),
            ]),
        ];
    }

    /**
     * Save the device.
     *
     * @param Device $device
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Device $device, Request $request)
    {
        $data = $request->get('device');
        
        $device->fill($data)->save();

        AlertFacade::info('Device has been saved successfully.');

        return redirect()->route('platform.devices.list');
    }

    /**
     * Remove the device.
     *
     * @param Device $device
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Device $device)
    {
        $device->delete();

        AlertFacade::info('Device has been deleted successfully.');

        return redirect()->route('platform.devices.list');
    }
}
