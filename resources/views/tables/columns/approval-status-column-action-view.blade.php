<div>
    @foreach ($data as $a)
        <div class="p-4 bg-gray-50 mb-4 rounded border-1">
            <div class="flex items-center gap-x-3">
                <img src="{{ filament()->getUserAvatarUrl($a->user) }}"
                     class="h-6 w-6 flex-none rounded-full bg-gray-800" alt="Avatar" />
                <h3 class="flex truncate text-sm font-semibold leading-6 text-gray-500">
                    {{ $a->user->name }}
                </h3>
                <time class="flex-none text-xs text-gray-500">{{ $a->created_at->diffForHumans() }} - {{ $a->created_at }}</time>
                <div class="flex gap-x-4 ml-auto">
                @php
                    $statusColors = config('approvals.ui.status_colors', [
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'discarded' => 'danger',
                        'submitted' => 'warning',
                        'returned' => 'warning',
                    ]);
                    $colorMap = [
                        'warning' => 'bg-warning-400 text-yellow-800',
                        'success' => 'bg-success-400 text-green-800',
                        'danger'  => 'bg-danger-400 text-white',
                        'gray'    => 'bg-gray-400 text-gray-800',
                        'info'    => 'bg-info-400 text-blue-800',
                    ];
                    $actionKey = strtolower($a->approval_action);
                    $filamentColor = $statusColors[$actionKey] ?? 'warning';
                    $colorClasses = $colorMap[$filamentColor] ?? $colorMap['warning'];
                @endphp
                <span class="px-3 py-1 rounded-full text-xs {{ $colorClasses }}">
                        {{ __('filament-approvals::approvals.actions.history.' . $a->approval_action ) }}
                    </span>
                </div>
                </div>
            @if($a->comment)
                <div class="mt-3 text-sm">{!! $a->comment !!}</div>
            @endif
        </div>
    @endforeach
</div>
