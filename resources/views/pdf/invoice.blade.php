<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

    <title>{{ $invoice->invoice_number }}</title>

    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 12px;
            color: #222;
        }

        table {
            direction: rtl;
            width: 100%;
        }

        th,
        td {
            direction: rtl;
            text-align: right;
        }

        .ltr {
            direction: ltr;
            text-align: left;
            unicode-bidi: embed;
        }

        .ltr-inline {
            direction: ltr;
            unicode-bidi: embed;
            display: inline-block;
        }

        .numeric {
            direction: ltr;
            text-align: center;
            unicode-bidi: embed;
        }

        .header {
            margin-bottom: 25px;
            border-bottom: 2px solid #222;
            padding-bottom: 15px;
        }

        .header-table {
            width: 100%;
            border: 0;
        }

        .header-table td {
            border: 0;
            padding: 3px;
        }

        .invoice-title {
            font-size: 22px;
            font-weight: bold;
        }

        .invoice-number {
            font-size: 13px;
            margin-top: 5px;
        }

        .text-left {
            text-align: left;
        }

        .section {
            margin-top: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            background: #f3f3f3;
            padding: 8px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            border: 1px solid #ddd;
            padding: 7px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 7px;
        }

        .items-table th {
            background: #f5f5f5;
            font-weight: bold;
        }

        .totals-wrapper {
            width: 100%;
            margin-top: 25px;
        }

        .totals-table {
            width: 45%;
            margin-right: auto;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .total-final {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #222;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .payment-table th,
        .payment-table td {
            border: 1px solid #ddd;
            padding: 7px;
        }

        .footer {
            margin-top: 35px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .muted {
            color: #777;
        }
    </style>
</head>

<body>

@php
    $invoiceable = $invoice->invoiceable;

    $payments = $invoiceable->payments ?? collect();

    $paidAmount = (float) $payments->sum('amount');
    $total = (float) $invoice->total;
    $remainingAmount = max(0, $total - $paidAmount);

    $isPaid = $remainingAmount <= 0;
@endphp


<div class="header">

    <table class="header-table">
        <tr>
            <td>
                <div class="invoice-title">
                    فاتورة
                </div>

                <div class="invoice-number">
                    رقم الفاتورة:
                    <span class="ltr-inline">{{ $invoice->invoice_number }}</span>
                </div>
            </td>

            <td class="text-left">
                <div>
                    تاريخ الإصدار:
                </div>

                <div>
                    <span class="ltr-inline">{{ $invoice->issued_at?->format('Y-m-d H:i') }}</span>
                </div>
            </td>
        </tr>
    </table>

</div>


{{-- Visit Checkout Invoice --}}

@if ($invoiceable instanceof \App\Models\VisitCheckout)

    @php
        $checkout = $invoiceable;
        $visit = $checkout->visit;
    @endphp

    <div class="section">

        <div class="section-title">
            بيانات الزيارة
        </div>

        <table class="info-table">
            <tr>
                <td>
                    رقم الزيارة
                </td>

                <td class="numeric">
                    {{ $visit->id }}
                </td>
            </tr>

            <tr>
                <td>
                    الطفل
                </td>

                <td>
                    {{ $visit->child?->name ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>
                    رقم الهاتف
                </td>

                <td class="ltr">
                    {{ $visit->child?->phone ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>
                    بداية الزيارة
                </td>

                <td class="ltr">
                    {{ $visit->started_at?->format('Y-m-d H:i') ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>
                    نهاية الزيارة
                </td>

                <td class="ltr">
                    {{ $visit->closed_at?->format('Y-m-d H:i') ?? '-' }}
                </td>
            </tr>
        </table>

    </div>


    @if ($visit->activityUsages->isNotEmpty())

        <div class="section">

            <div class="section-title">
                الأنشطة
            </div>

            <table class="items-table">

                <thead>
                <tr>
                    <th>النشاط</th>
                    <th>البداية</th>
                    <th>النهاية</th>
                    <th>المدة</th>
                    <th>القيمة</th>
                </tr>
                </thead>

                <tbody>

                @foreach ($visit->activityUsages as $usage)

                    <tr>
                        <td>
                            {{ $usage->activity?->name ?? '-' }}
                        </td>

                        <td class="numeric">
                            {{ $usage->started_at?->format('H:i') ?? '-' }}
                        </td>

                        <td class="numeric">
                            {{ $usage->ended_at?->format('H:i') ?? '-' }}
                        </td>

                        <td>
                            <span class="ltr-inline">{{ $usage->duration_minutes ?? 0 }}</span>
                            دقيقة
                        </td>

                        <td class="numeric">
                            {{ number_format((float) $usage->final_amount, 2) }}
                        </td>
                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

    @endif


    @if ($visit->cafeOrders->isNotEmpty())

        <div class="section">

            <div class="section-title">
                طلبات الكافيه
            </div>

            @foreach ($visit->cafeOrders as $order)

                <div style="margin-top: 10px;">
                    رقم الطلب:
                    <strong>
                        {{ $order->order_number }}
                    </strong>
                </div>

                <table class="items-table">

                    <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>الإجمالي</th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach ($order->items as $item)

                        <tr>
                            <td>
                                {{ $item->product_name }}
                            </td>

                            <td class="numeric">
                                {{ $item->quantity }}
                            </td>

                            <td class="numeric">
                                {{ number_format((float) $item->unit_price, 2) }}
                            </td>

                            <td class="numeric">
                                {{ number_format((float) $item->total, 2) }}
                            </td>
                        </tr>

                    @endforeach

                    </tbody>

                </table>

            @endforeach

        </div>

    @endif

@endif


{{-- Standalone Activity Usage Invoice --}}

@if ($invoiceable instanceof \App\Models\ActivityUsage)

    @php
        $usage = $invoiceable;
    @endphp

    <div class="section">

        <div class="section-title">
            بيانات استخدام النشاط
        </div>

        <table class="info-table">

            <tr>
                <td>الطفل</td>

                <td>
                    {{ $usage->child?->name ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>رقم الهاتف</td>

                <td class="ltr">
                    {{ $usage->child?->phone ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>النشاط</td>

                <td>
                    {{ $usage->activity?->name ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>وقت البداية</td>

                <td class="ltr">
                    {{ $usage->started_at?->format('Y-m-d H:i') ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>وقت النهاية</td>

                <td class="ltr">
                    {{ $usage->ended_at?->format('Y-m-d H:i') ?? '-' }}
                </td>
            </tr>

            <tr>
                <td>مدة الاستخدام</td>

                <td>
                    <span class="ltr-inline">{{ $usage->duration_minutes ?? 0 }}</span>
                    دقيقة
                </td>
            </tr>

            <tr>
                <td>إجمالي التوقف</td>

                <td>
                    <span class="ltr-inline">{{ $usage->total_paused_minutes ?? 0 }}</span>
                    دقيقة
                </td>
            </tr>

        </table>

    </div>

@endif


{{-- Standalone Cafe Order Invoice --}}

@if ($invoiceable instanceof \App\Models\CafeOrder)

    @php
        $order = $invoiceable;
    @endphp

    <div class="section">

        <div class="section-title">
            بيانات طلب الكافيه
        </div>

        <table class="info-table">
            <tr>
                <td>
                    رقم الطلب
                </td>

                <td class="ltr">
                    {{ $order->order_number }}
                </td>
            </tr>

            <tr>
                <td>
                    تاريخ الطلب
                </td>

                <td class="ltr">
                    {{ $order->created_at?->format('Y-m-d H:i') ?? '-' }}
                </td>
            </tr>
        </table>

    </div>


    <div class="section">

        <div class="section-title">
            المنتجات
        </div>

        <table class="items-table">

            <thead>
            <tr>
                <th>المنتج</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>الإجمالي</th>
            </tr>
            </thead>

            <tbody>

            @foreach ($order->items as $item)

                <tr>
                    <td>
                        {{ $item->product_name }}
                    </td>

                    <td class="numeric">
                        {{ $item->quantity }}
                    </td>

                    <td class="numeric">
                        {{ number_format((float) $item->unit_price, 2) }}
                    </td>

                    <td class="numeric">
                        {{ number_format((float) $item->total, 2) }}
                    </td>
                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

@endif


{{-- Totals --}}

<div class="totals-wrapper">

    <table class="totals-table">

        <tr>
            <td>
                الإجمالي قبل الخصم
            </td>

            <td class="numeric">
                {{ number_format((float) $invoice->subtotal, 2) }}
            </td>
        </tr>

        <tr>
            <td>
                الخصم
            </td>

            <td class="numeric">
                {{ number_format((float) $invoice->discount, 2) }}
            </td>
        </tr>

        <tr class="total-final">
            <td>
                الإجمالي النهائي
            </td>

            <td class="numeric">
                {{ number_format((float) $invoice->total, 2) }}
            </td>
        </tr>

    </table>

</div>


{{-- Payments --}}

<div class="section">

    <div class="section-title">
        الدفع
    </div>

    @if ($payments->isNotEmpty())

        <table class="payment-table">

            <thead>
            <tr>
                <th>القيمة</th>
                <th>التاريخ</th>
                <th>المرجع</th>
            </tr>
            </thead>

            <tbody>

            @foreach ($payments as $payment)

                <tr>
                    <td class="numeric">
                        {{ number_format((float) $payment->amount, 2) }}
                    </td>

                    <td class="ltr">
                        {{ $payment->paid_at?->format('Y-m-d H:i') }}
                    </td>

                    <td class="ltr">
                        {{ $payment->reference ?? '-' }}
                    </td>
                </tr>

            @endforeach

            </tbody>

        </table>

    @endif


    <table class="totals-table" style="margin-top: 15px;">

        <tr>
            <td>
                المدفوع
            </td>

            <td class="numeric">
                {{ number_format($paidAmount, 2) }}
            </td>
        </tr>

        <tr>
            <td>
                المتبقي
            </td>

            <td class="numeric">
                {{ number_format($remainingAmount, 2) }}
            </td>
        </tr>

        <tr>
            <td>
                حالة الدفع
            </td>

            <td>
                {{ $isPaid ? 'مدفوع بالكامل' : 'غير مكتمل' }}
            </td>
        </tr>

    </table>

</div>


@if ($invoice->notes)

    <div class="section">

        <div class="section-title">
            ملاحظات
        </div>

        <div>
            {{ $invoice->notes }}
        </div>

    </div>

@endif


<div class="footer">
    شكرًا لزيارتكم
</div>

</body>
</html>
