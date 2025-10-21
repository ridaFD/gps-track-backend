<div class="bg-white rounded shadow p-4">
    <h3 class="mb-3">🚨 Recent Alerts</h3>
    
    @if($recent_alerts->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Severity</th>
                        <th>Device</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_alerts as $alert)
                        <tr>
                            <td>
                                @php
                                    $severityColors = [
                                        'info' => 'info',
                                        'warning' => 'warning',
                                        'high' => 'danger',
                                        'critical' => 'dark'
                                    ];
                                    $color = $severityColors[$alert->severity] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ ucfirst($alert->severity) }}</span>
                            </td>
                            <td>{{ $alert->device->name ?? 'N/A' }}</td>
                            <td><small>{{ str_replace('_', ' ', ucwords($alert->type, '_')) }}</small></td>
                            <td><small>{{ \Illuminate\Support\Str::limit($alert->message, 40) }}</small></td>
                            <td><small>{{ $alert->created_at->diffForHumans() }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3">
            <a href="{{ route('platform.alerts.list') }}" class="btn btn-sm btn-link">View All Alerts →</a>
        </div>
    @else
        <p class="text-muted">No recent alerts</p>
    @endif
</div>

