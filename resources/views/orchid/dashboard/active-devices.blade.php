<div class="bg-white rounded shadow p-4">
    <h3 class="mb-3">🚗 Active Devices</h3>
    
    @if($active_devices->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Device</th>
                        <th>Type</th>
                        <th>Last Position</th>
                        <th>Speed</th>
                        <th>Last Update</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($active_devices as $device)
                        <tr>
                            <td>
                                <strong>{{ $device->name }}</strong>
                                @if($device->plate_number)
                                    <br><small class="text-muted">{{ $device->plate_number }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($device->type) }}</span>
                            </td>
                            <td>
                                @if($device->lastPosition)
                                    <small>
                                        {{ number_format($device->lastPosition->latitude, 4) }}, 
                                        {{ number_format($device->lastPosition->longitude, 4) }}
                                    </small>
                                @else
                                    <span class="text-muted">No data</span>
                                @endif
                            </td>
                            <td>
                                @if($device->lastPosition && $device->lastPosition->speed)
                                    <span class="badge bg-success">{{ number_format($device->lastPosition->speed, 1) }} km/h</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    @if($device->lastPosition)
                                        {{ $device->lastPosition->device_time->diffForHumans() }}
                                    @else
                                        —
                                    @endif
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('platform.devices.list') }}" class="btn btn-sm btn-link">View All Devices →</a>
        </div>
    @else
        <p class="text-muted">No active devices</p>
    @endif
</div>

