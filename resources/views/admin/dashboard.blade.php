@extends('layouts.admin')
@section('title')
{{ __('dashboard.home') }}
@endsection

@section('css')
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f8f9fa;
    color: #495057;
    direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};
}

.dashboard-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 0;
}

.dashboard-header {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    padding: 30px;
    margin-bottom: 30px;
    border-bottom: 1px solid #e9ecef;
}

.dashboard-header h1 {
    font-size: 2rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 8px;
}

.dashboard-header p {
    font-size: 1rem;
    color: #6c757d;
    font-weight: 400;
}

.main-content {
    padding: 0 30px 30px;
}

.time-filters {
    display: flex;
    gap: 12px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.time-filter {
    padding: 10px 20px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #ffffff;
    color: #495057;
    cursor: pointer;
    transition: all 0.2s ease;
    font-weight: 500;
    font-size: 0.9rem;
}

.time-filter.active {
    background: #007bff;
    color: #ffffff;
    border-color: #007bff;
}

.time-filter:hover:not(.active) {
    background: #f8f9fa;
    border-color: #adb5bd;
}

.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 24px;
    transition: all 0.2s ease;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-card.earnings .stat-icon {
    background: #e7f5ff;
    color: #007bff;
}

.stat-card.users .stat-icon {
    background: #d3f9d8;
    color: #28a745;
}

.stat-card.drivers .stat-icon {
    background: #fff3cd;
    color: #ffc107;
}

.stat-card.orders .stat-icon {
    background: #f8d7da;
    color: #dc3545;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 6px;
}

.stat-trend.positive {
    background: #d3f9d8;
    color: #28a745;
}

.stat-trend.negative {
    background: #f8d7da;
    color: #dc3545;
}

.stat-title {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 8px;
    line-height: 1;
}

.stat-subtitle {
    font-size: 0.875rem;
    color: #6c757d;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.dashboard-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 24px;
}

.card-title {
    font-size: 1.125rem;
    color: #212529;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e9ecef;
}

.monthly-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 16px;
}

.mini-stat {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.mini-stat:hover {
    background: #e9ecef;
}

.mini-stat-icon {
    font-size: 1.75rem;
    margin-bottom: 12px;
}

.mini-stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 4px;
}

.mini-stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.quick-metrics {
    display: grid;
    gap: 12px;
}

.metric-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.metric-item:hover {
    background: #e9ecef;
}

.metric-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.metric-icon {
    font-size: 1.5rem;
}

.metric-details h4 {
    color: #212529;
    font-size: 0.95rem;
    margin-bottom: 2px;
    font-weight: 600;
}

.metric-details p {
    color: #6c757d;
    font-size: 0.8rem;
}

.metric-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #007bff;
}

.earnings-breakdown {
    grid-column: 1 / -1;
}

.earnings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
}

.earnings-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    transition: all 0.2s ease;
}

.earnings-card:hover {
    background: #e9ecef;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.earnings-icon {
    font-size: 2rem;
    margin-bottom: 12px;
}

.earnings-title {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.earnings-amount {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 6px;
}

.earnings-subtitle {
    font-size: 0.8rem;
    color: #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-overview {
        grid-template-columns: 1fr;
    }
    
    .dashboard-header h1 {
        font-size: 1.5rem;
    }
    
    .main-content {
        padding: 0 15px 15px;
    }
    
    .time-filters {
        gap: 8px;
    }
    
    .time-filter {
        padding: 8px 16px;
        font-size: 0.85rem;
    }
}

/* Loading Animation */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(0,123,255,0.1), transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* RTL Adjustments */
[dir="rtl"] .stat-header {
    flex-direction: row-reverse;
}

[dir="rtl"] .metric-info {
    flex-direction: row-reverse;
}

[dir="rtl"] .stat-trend {
    flex-direction: row-reverse;
}

[dir="rtl"] .card-title {
    flex-direction: row-reverse;
}

/* Smooth transitions */
* {
    transition: background-color 0.2s ease, border-color 0.2s ease;
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

::-webkit-scrollbar-track {
    background: #f8f9fa;
}

::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}
</style>
@endsection

@section('contentheaderlink')
<a href="{{ route('admin.dashboard') }}"> {{ __('dashboard.home') }} </a>
@endsection

@section('contentheaderactive')
{{ __('dashboard.view') }}
@endsection

@section('content')
<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>{{ __('dashboard.main_dashboard') }}</h1>
        <p>{{ __('dashboard.comprehensive_statistics') }}</p>
    </div>

    <div class="main-content">
 
        <!-- Stats Overview -->
        <div class="stats-overview">
            <!-- Earnings Card -->
            <div class="stat-card earnings">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i> {{ $earningsGrowth }}%
                    </div>
                </div>
                <div class="stat-title">{{ __('dashboard.total_earnings_today') }}</div>
                <div class="stat-number">JD {{ number_format($todayEarnings, 2) }}</div>
                <div class="stat-subtitle">{{ __('dashboard.from_yesterday') }}</div>
            </div>

            <!-- Users Card -->
            <div class="stat-card users">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i> {{ $usersGrowth }}%
                    </div>
                </div>
                <div class="stat-title">{{ __('dashboard.total_users') }}</div>
                <div class="stat-number">{{ number_format($usersCount) }}</div>
                <div class="stat-subtitle">{{ __('dashboard.new_users_today') }}: {{ $newUsersToday }}</div>
            </div>

            <!-- Drivers Card -->
            <div class="stat-card drivers">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i> {{ $driversGrowth }}%
                    </div>
                </div>
                <div class="stat-title">{{ __('dashboard.total_drivers') }}</div>
                <div class="stat-number">{{ number_format($driversCount) }}</div>
                <div class="stat-subtitle">{{ __('dashboard.active_drivers') }}: {{ $activeDriversToday }}</div>
            </div>

            <!-- Orders Card -->
            <div class="stat-card orders">
                <div class="stat-header">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i> {{ $ordersGrowth }}%
                    </div>
                </div>
                <div class="stat-title">{{ __('dashboard.orders_today') }}</div>
                <div class="stat-number">{{ number_format($todayOrders) }}</div>
                <div class="stat-subtitle">
                    <i class="fas fa-check-circle text-success"></i> {{ $completedOrdersToday }} | 
                    <i class="fas fa-clock text-warning"></i> {{ $pendingOrdersToday }}
                </div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Monthly Statistics -->
            <div class="dashboard-card">
                <div class="card-title">
                    <i class="fas fa-chart-bar"></i> {{ __('dashboard.monthly_statistics') }}
                </div>
                <div class="monthly-stats">
                    <div class="mini-stat">
                        <div class="mini-stat-icon">💵</div>
                        <div class="mini-stat-number">JD{{ number_format($monthlyEarnings, 2) }}</div>
                        <div class="mini-stat-label">{{ __('dashboard.monthly_earnings') }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-icon">📋</div>
                        <div class="mini-stat-number">{{ number_format($monthlyOrders) }}</div>
                        <div class="mini-stat-label">{{ __('dashboard.monthly_orders') }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-icon">✅</div>
                        <div class="mini-stat-number">{{ $completionRate }}%</div>
                        <div class="mini-stat-label">{{ __('dashboard.completion_rate') }}</div>
                    </div>
                </div>
            </div>

            <!-- Quick Statistics -->
            <div class="dashboard-card">
                <div class="card-title">
                    <i class="fas fa-tachometer-alt"></i> {{ __('dashboard.quick_statistics') }}
                </div>
                <div class="quick-metrics">
                    <div class="metric-item">
                        <div class="metric-info">
                            <div class="metric-icon">🚗</div>
                            <div class="metric-details">
                                <h4>{{ __('dashboard.active_driver_now') }}</h4>
                                <p>{{ __('dashboard.online_now') }}</p>
                            </div>
                        </div>
                        <div class="metric-value">{{ $activeDriversNow }}</div>
                    </div>

                    <div class="metric-item">
                        <div class="metric-info">
                            <div class="metric-icon">⏱️</div>
                            <div class="metric-details">
                                <h4>{{ __('dashboard.average_delivery_time') }}</h4>
                                <p>{{ __('dashboard.minutes') }}</p>
                            </div>
                        </div>
                        <div class="metric-value">{{ $averageOrderTime }}</div>
                    </div>

                    <div class="metric-item">
                        <div class="metric-info">
                            <div class="metric-icon">❌</div>
                            <div class="metric-details">
                                <h4>{{ __('dashboard.canceled_orders_today') }}</h4>
                                <p>{{ __('dashboard.today') }}</p>
                            </div>
                        </div>
                        <div class="metric-value">{{ $canceledOrdersToday }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings Breakdown -->
        <div class="dashboard-card earnings-breakdown">
            <div class="card-title">
                <i class="fas fa-chart-line"></i> {{ __('dashboard.earnings_details') }}
            </div>
            <div class="earnings-grid">
                <div class="earnings-card">
                    <div class="earnings-icon">🏆</div>
                    <div class="earnings-title">{{ __('dashboard.total_earnings_all') }}</div>
                    <div class="earnings-amount">JD{{ number_format($totalEarnings, 2) }}</div>
                    <div class="earnings-subtitle">{{ __('dashboard.since_beginning') }}</div>
                </div>

                <div class="earnings-card">
                    <div class="earnings-icon">💼</div>
                    <div class="earnings-title">{{ __('dashboard.admin_commission_today') }}</div>
                    <div class="earnings-amount">JD{{ number_format($adminCommissionToday, 2) }}</div>
                    <div class="earnings-subtitle">{{ __('dashboard.from_total_orders') }}</div>
                </div>

                <div class="earnings-card">
                    <div class="earnings-icon">👨‍✈️</div>
                    <div class="earnings-title">{{ __('dashboard.drivers_earnings_today') }}</div>
                    <div class="earnings-amount">JD{{ number_format($driversEarningsToday, 2) }}</div>
                    <div class="earnings-subtitle">{{ __('dashboard.net_drivers_earnings') }}</div>
                </div>

                <div class="earnings-card">
                    <div class="earnings-icon">📊</div>
                    <div class="earnings-title">{{ __('dashboard.average_order_value') }}</div>
                    <div class="earnings-amount">JD{{ number_format($averageOrderValue, 2) }}</div>
                    <div class="earnings-subtitle">{{ __('dashboard.for_this_month') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Time filter functionality
    document.querySelectorAll('.time-filter').forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            document.querySelectorAll('.time-filter').forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const period = this.dataset.period;
            
            // Add loading state
            document.querySelectorAll('.stat-card').forEach(card => {
                card.classList.add('loading');
            });
            
         
        });
    });

    // Add smooth hover effects
    document.querySelectorAll('.stat-card, .dashboard-card, .earnings-card, .metric-item, .mini-stat').forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.2s ease';
        });
    });
});
</script>
@endsection