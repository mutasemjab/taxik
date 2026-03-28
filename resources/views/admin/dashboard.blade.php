@extends('layouts.admin')

@section('title')
    {{ __('dashboard.home') }}
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dashboard.css') }}">
@endsection

@section('contentheaderlink')
    <a href="{{ route('admin.dashboard') }}">{{ __('dashboard.home') }}</a>
@endsection

@section('contentheaderactive')
    {{ __('dashboard.view') }}
@endsection

@section('content')
<div class="db-wrap">

    {{-- ===== Header ===== --}}
    <div class="db-header">
        <div class="row align-items-center">
            <div class="col">
                <h1>{{ __('dashboard.main_dashboard') }}</h1>
                <p>{{ __('dashboard.comprehensive_statistics') }}</p>
            </div>
            <div class="col-auto db-header-right">
                <div class="live-badge">
                    <span class="dot"></span>
                    {{ __('dashboard.online_now') }}: <strong>{{ $activeDriversNow }}</strong> {{ __('dashboard.drivers') }}
                </div>
                <div class="db-date mt-2">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ \Carbon\Carbon::now()->format('l, d M Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Stat Cards ===== --}}
    <div class="db-stats-row">

        {{-- Earnings Today --}}
        <div class="stat-card c-primary">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-coins"></i></div>
                <span class="stat-badge {{ $earningsGrowth >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ $earningsGrowth >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($earningsGrowth) }}%
                </span>
            </div>
            <div class="stat-label">{{ __('dashboard.total_earnings_today') }}</div>
            <div class="stat-value">JD {{ number_format($todayEarnings, 2) }}</div>
            <div class="stat-sub"><i class="fas fa-clock mr-1"></i>{{ __('dashboard.from_yesterday') }}</div>
        </div>

        {{-- Users --}}
        <div class="stat-card c-success">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-users"></i></div>
                <span class="stat-badge {{ $usersGrowth >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ $usersGrowth >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($usersGrowth) }}%
                </span>
            </div>
            <div class="stat-label">{{ __('dashboard.total_users') }}</div>
            <div class="stat-value">{{ number_format($usersCount) }}</div>
            <div class="stat-sub">
                <i class="fas fa-user-plus mr-1 text-success"></i>
                +{{ $newUsersToday }} {{ __('dashboard.new_users_today') }}
            </div>
        </div>

        {{-- Drivers --}}
        <div class="stat-card c-warning">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-car"></i></div>
                <span class="stat-badge {{ $driversGrowth >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ $driversGrowth >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($driversGrowth) }}%
                </span>
            </div>
            <div class="stat-label">{{ __('dashboard.total_drivers') }}</div>
            <div class="stat-value">{{ number_format($driversCount) }}</div>
            <div class="stat-sub">
                <i class="fas fa-circle mr-1" style="color:#2ecc71;font-size:.6rem;"></i>
                {{ __('dashboard.active_drivers') }}: {{ $activeDriversToday }}
            </div>
        </div>

        {{-- Orders Today --}}
        <div class="stat-card c-danger">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-route"></i></div>
                <span class="stat-badge {{ $ordersGrowth >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ $ordersGrowth >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($ordersGrowth) }}%
                </span>
            </div>
            <div class="stat-label">{{ __('dashboard.orders_today') }}</div>
            <div class="stat-value">{{ number_format($todayOrders) }}</div>
            <div class="stat-sub">
                <i class="fas fa-check-circle mr-1" style="color:#2ecc71;"></i>{{ $completedOrdersToday }}
                &nbsp;|&nbsp;
                <i class="fas fa-clock mr-1" style="color:#f39c12;"></i>{{ $pendingOrdersToday }}
                &nbsp;|&nbsp;
                <i class="fas fa-times-circle mr-1" style="color:#e74c3c;"></i>{{ $canceledOrdersToday }}
            </div>
        </div>

        {{-- Admin Commission Today --}}
        <div class="stat-card c-info">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-percentage"></i></div>
                <span class="stat-badge neu"><i class="fas fa-chart-line"></i></span>
            </div>
            <div class="stat-label">{{ __('dashboard.admin_commission_today') }}</div>
            <div class="stat-value">JD {{ number_format($adminCommissionToday, 2) }}</div>
            <div class="stat-sub">{{ __('dashboard.from_total_orders') }}</div>
        </div>

        {{-- SMS Balance --}}
        <div class="stat-card c-purple sms-card">
            <div class="stat-card-head">
                <div class="stat-card-icon"><i class="fas fa-sms"></i></div>
            </div>
            <div class="stat-label">رسائل OTP المتبقية</div>
            <div class="stat-value" id="sms-balance-value">—</div>
            <div class="stat-sub" id="sms-balance-label">جاري التحميل...</div>
        </div>

    </div>

    {{-- ===== Row: Monthly + Quick Metrics ===== --}}
    <div class="db-two-col">

        {{-- Monthly Statistics --}}
        <div class="db-card">
            <div class="db-card-header">
                <div class="title"><i class="fas fa-chart-bar"></i> {{ __('dashboard.monthly_statistics') }}</div>
                <span style="font-size:.8rem;color:var(--text-muted);">{{ \Carbon\Carbon::now()->format('M Y') }}</span>
            </div>
            <div class="monthly-grid">
                <div class="mini-stat-box">
                    <div class="msb-icon">💵</div>
                    <div class="msb-val">JD{{ number_format($monthlyEarnings, 0) }}</div>
                    <div class="msb-lbl">{{ __('dashboard.monthly_earnings') }}</div>
                </div>
                <div class="mini-stat-box">
                    <div class="msb-icon">📋</div>
                    <div class="msb-val">{{ number_format($monthlyOrders) }}</div>
                    <div class="msb-lbl">{{ __('dashboard.monthly_orders') }}</div>
                </div>
                <div class="mini-stat-box">
                    <div class="msb-icon">✅</div>
                    <div class="msb-val">{{ $completionRate }}%</div>
                    <div class="msb-lbl">{{ __('dashboard.completion_rate') }}</div>
                </div>
            </div>
            {{-- Earnings Breakdown --}}
            <div class="earnings-grid mt-2">
                <div class="earning-item">
                    <div class="ei-icon">🏆</div>
                    <div class="ei-label">{{ __('dashboard.total_earnings_all') }}</div>
                    <div class="ei-value">JD{{ number_format($totalEarnings, 0) }}</div>
                    <div class="ei-sub">{{ __('dashboard.since_beginning') }}</div>
                </div>
                <div class="earning-item">
                    <div class="ei-icon">👨‍✈️</div>
                    <div class="ei-label">{{ __('dashboard.drivers_earnings_today') }}</div>
                    <div class="ei-value">JD{{ number_format($driversEarningsToday, 2) }}</div>
                    <div class="ei-sub">{{ __('dashboard.net_drivers_earnings') }}</div>
                </div>
                <div class="earning-item">
                    <div class="ei-icon">📊</div>
                    <div class="ei-label">{{ __('dashboard.average_order_value') }}</div>
                    <div class="ei-value">JD{{ number_format($averageOrderValue, 2) }}</div>
                    <div class="ei-sub">{{ __('dashboard.for_this_month') }}</div>
                </div>
            </div>
        </div>

        {{-- Quick Metrics --}}
        <div class="db-card">
            <div class="db-card-header">
                <div class="title"><i class="fas fa-tachometer-alt"></i> {{ __('dashboard.quick_statistics') }}</div>
            </div>
            <div class="qm-list">
                <div class="qm-item">
                    <div class="qm-left">
                        <div class="qm-icon">🚗</div>
                        <div>
                            <div class="qm-title">{{ __('dashboard.active_driver_now') }}</div>
                            <div class="qm-sub">{{ __('dashboard.online_now') }}</div>
                        </div>
                    </div>
                    <div class="qm-value">{{ $activeDriversNow }}</div>
                </div>
                <div class="qm-item">
                    <div class="qm-left">
                        <div class="qm-icon">⏱️</div>
                        <div>
                            <div class="qm-title">{{ __('dashboard.average_delivery_time') }}</div>
                            <div class="qm-sub">{{ __('dashboard.minutes') }}</div>
                        </div>
                    </div>
                    <div class="qm-value">{{ $averageOrderTime }}</div>
                </div>
                <div class="qm-item">
                    <div class="qm-left">
                        <div class="qm-icon">❌</div>
                        <div>
                            <div class="qm-title">{{ __('dashboard.canceled_orders_today') }}</div>
                            <div class="qm-sub">{{ __('dashboard.today') }}</div>
                        </div>
                    </div>
                    <div class="qm-value" style="color:#e74c3c;">{{ $canceledOrdersToday }}</div>
                </div>
                <div class="qm-item">
                    <div class="qm-left">
                        <div class="qm-icon">🔄</div>
                        <div>
                            <div class="qm-title">{{ __('dashboard.active_drivers') }} {{ __('dashboard.today') }}</div>
                            <div class="qm-sub">{{ __('messages.Completed') }} {{ __('dashboard.orders') }}</div>
                        </div>
                    </div>
                    <div class="qm-value" style="color:#2ecc71;">{{ $activeDriversToday }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Row: Recent Orders + Top Drivers ===== --}}
    <div class="db-two-col">

        {{-- Recent Orders --}}
        <div class="db-card">
            <div class="db-card-header">
                <div class="title"><i class="fas fa-list-alt"></i> آخر الطلبات</div>
                <a href="{{ route('orders.index') }}">عرض الكل <i class="fas fa-arrow-left fa-xs"></i></a>
            </div>
            @if($recentOrders->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2 d-block" style="color:#d1d5db;"></i>
                    لا توجد طلبات
                </div>
            @else
            <div class="table-responsive">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>المستخدم</th>
                            <th>الحالة</th>
                            <th>السعر</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        @php
                            $sv = is_object($order->status) ? $order->status->value : $order->status;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('orders.show', $order->id) }}" class="order-id">
                                    #{{ $order->number ?? $order->id }}
                                </a>
                            </td>
                            <td>
                                @if($order->user)
                                <div class="user-cell">
                                    <div class="user-avatar">{{ strtoupper(substr($order->user->name ?? 'U', 0, 1)) }}</div>
                                    <div>
                                        <div class="user-name">{{ $order->user->name }}</div>
                                        <div class="user-phone">{{ $order->user->phone }}</div>
                                    </div>
                                </div>
                                @else
                                <span class="text-muted" style="font-size:.8rem;">—</span>
                                @endif
                            </td>
                            <td><span class="s-badge {{ $sv }}">{{ __('messages.' . ucwords(str_replace('_', ' ', $sv))) }}</span></td>
                            <td style="font-weight:700;font-size:.85rem;">JD {{ number_format($order->total_price_after_discount, 2) }}</td>
                            <td style="font-size:.78rem;color:var(--text-muted);">{{ $order->created_at->format('d/m H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Top Drivers Today --}}
        <div class="db-card">
            <div class="db-card-header">
                <div class="title"><i class="fas fa-trophy"></i> أفضل السائقين اليوم</div>
                <a href="{{ route('drivers.index') }}">عرض الكل <i class="fas fa-arrow-left fa-xs"></i></a>
            </div>
            @if($topDriversToday->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-car fa-2x mb-2 d-block" style="color:#d1d5db;"></i>
                    لا يوجد سائقون نشطون اليوم
                </div>
            @else
            <div class="driver-list">
                @foreach($topDriversToday as $i => $driver)
                <div class="driver-item">
                    <div class="driver-rank {{ $i === 0 ? 'r1' : ($i === 1 ? 'r2' : ($i === 2 ? 'r3' : '')) }}">
                        {{ $i + 1 }}
                    </div>
                    @if($driver->photo)
                        <img src="{{ asset('assets/admin/uploads/' . $driver->photo) }}"
                             class="driver-avatar" alt="{{ $driver->name }}">
                    @else
                        <div class="driver-avatar-ph">{{ strtoupper(substr($driver->name, 0, 1)) }}</div>
                    @endif
                    <div class="driver-info">
                        <div class="driver-name">{{ $driver->name }}</div>
                        <div class="driver-phone">{{ $driver->phone }}</div>
                    </div>
                    <div class="driver-trips">
                        <div class="trips-num">{{ $driver->trips_count }}</div>
                        <div class="trips-lbl">{{ __('dashboard.orders') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    fetch('{{ route('admin.sms-balance') }}')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('sms-balance-value').textContent = data.balance;
                document.getElementById('sms-balance-label').textContent = 'رسالة متبقية';
            } else {
                document.getElementById('sms-balance-value').textContent = '—';
                document.getElementById('sms-balance-label').textContent = data.error ?? 'غير متاح';
            }
        })
        .catch(() => {
            document.getElementById('sms-balance-value').textContent = '—';
            document.getElementById('sms-balance-label').textContent = 'تعذر الاتصال';
        });
});
</script>
@endsection
