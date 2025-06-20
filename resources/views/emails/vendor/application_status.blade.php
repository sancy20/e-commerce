<x-mail::message>
# Vendor Application Status

Hello {{ $user->name }},

Your vendor application has been {{ $status }}.

@if ($status === 'approved')
Congratulations! You are now an approved vendor on {{ config('app.name') }}.
You can now log in and start managing your products and sales from your vendor dashboard.
@else
We regret to inform you that your vendor application has been {{ $status }}.
@endif

@if ($message)
---
**Reason/Notes:**
{{ $message }}
---
@endif

@if ($status === 'approved')
<x-mail::button :url="route('vendor.dashboard')">
Go to Vendor Dashboard
</x-mail::button>
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>