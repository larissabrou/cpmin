@php
    $status = strtoupper((string) ($status ?? 'PENDING'));
    $class = match ($status) {
        'SUCCESS' => 'bg-success-subtle text-success',
        'FAILED' => 'bg-danger-subtle text-danger',
        'CANCELLED' => 'bg-secondary-subtle text-secondary',
        default => 'bg-warning-subtle text-warning',
    };
@endphp
<span class="badge {{ $class }}">{{ $status }}</span>
