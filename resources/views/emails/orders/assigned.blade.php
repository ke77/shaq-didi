<x-mail::message>
# Order confirmed

Hi {{ $order->personnel->name }}, order {{ $order->id }} has been assigned to you.

**Item:** {{ $order->product_name }}
**Quantity:** {{ $order->amount }}
**Total:** GH₵ {{ number_format($order->total_price / 100, 2) }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>