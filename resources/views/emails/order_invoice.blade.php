@component('mail::message')
    # Halo {{ $order->shipping->information->name ?? 'Customer' }} 👋

    Terima kasih sudah berbelanja di **{{ config('app.name') }}**!

    **Invoice:** {{ $order->invoice }}

    @component('mail::table')
        | Produk | Varian | Qty | Harga | Total |
        |:-------|:--------|:----:|------:|------:|
        @foreach ($order->items as $item)
            | {{ $item->name }} | {{ $item->variant }} | {{ $item->qty }} | Rp{{ number_format($item->price) }} |
            Rp{{ number_format($item->total) }} |
        @endforeach
    @endcomponent

    **Subtotal:** Rp{{ number_format($order->items->sum('total')) }}
    **Ongkir:** Rp{{ number_format($order->shipping->option->cost) }}
    **Total:** **Rp{{ number_format($order->total) }}**

    ---

    @if ($paymentUrl)
        @component('mail::button', ['url' => $paymentUrl])
            💳 Bayar Sekarang
        @endcomponent
    @endif

    Terima kasih,<br>
    {{ config('app.name') }}
@endcomponent
