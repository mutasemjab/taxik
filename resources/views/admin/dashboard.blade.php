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
    background: #0a0a0a;
    color: #ffffff;
    direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};
}

.dashboard-container {
    background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
    min-height: 100vh;
    padding: 0;
}

.dashboard-header {
    background: linear-gradient(135deg, #000000 0%, #2d2d2d 100%);
    padding: 30px;
    text-align: center;
    border-bottom: 3px solid #ffd700;
    position: relative;
    overflow: hidden;
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%23ffd700" stroke-width="0.5" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    z-index: 1;
}

.dashboard-header .content {
    position: relative;
    z-index: 2;
}

.dashboard-header h1 {
    font-size: 2.8rem;
    font-weight: 700;
    color: #ffd700;
    margin-bottom: 10px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.dashboard-header p {
    font-size: 1.2rem;
    color: #cccccc;
    font-weight: 300;
}

.main-content {
    padding: 30px;
}

.time-filters {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.time-filter {
    padding: 12px 24px;
    border: 2px solid #ffd700;
    border-radius: 30px;
    background: transparent;
    color: #ffd700;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.time-filter.active,
.time-filter:hover {
    background: #ffd700;
    color: #000000;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
}

.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border: 1px solid #333333;
    border-radius: 20px;
    padding: 30px;
    position: relative;
    transition: all 0.3s ease;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ffd700, #ffed4e);
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(255, 215, 0, 0.15);
    border-color: #ffd700;
}

.stat-card.earnings {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d00 100%);
}

.stat-card.users {
    background: linear-gradient(135deg, #1a1a1a 0%, #001a2d 100%);
}

.stat-card.drivers {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d001a 100%);
}

.stat-card.orders {
    background: linear-gradient(135deg, #1a1a1a 0%, #002d1a 100%);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.stat-icon {
    font-size: 2.5rem;
    color: #ffd700;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9rem;
    font-weight: 600;
}

.stat-trend.positive {
    color: #00ff88;
}

.stat-trend.negative {
    color: #ff4757;
}

.stat-title {
    font-size: 1rem;
    color: #cccccc;
    font-weight: 500;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.stat-number {
    font-size: 2.8rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 10px;
    line-height: 1;
}

.stat-subtitle {
    font-size: 0.9rem;
    color: #999999;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

.dashboard-card {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border: 1px solid #333333;
    border-radius: 20px;
    padding: 30px;
    position: relative;
    overflow: hidden;
}

.dashboard-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #ffd700, #ffed4e);
}

.card-title {
    font-size: 1.4rem;
    color: #ffd700;
    font-weight: 600;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.monthly-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
}

.mini-stat {
    text-align: center;
    padding: 20px;
    background: rgba(255, 215, 0, 0.1);
    border-radius: 15px;
    border: 1px solid rgba(255, 215, 0, 0.2);
    transition: all 0.3s ease;
}

.mini-stat:hover {
    background: rgba(255, 215, 0, 0.2);
    transform: scale(1.05);
}

.mini-stat-icon {
    font-size: 2rem;
    color: #ffd700;
    margin-bottom: 10px;
}

.mini-stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 5px;
}

.mini-stat-label {
    font-size: 0.85rem;
    color: #cccccc;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.quick-metrics {
    display: grid;
    gap: 20px;
}

.metric-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    background: rgba(255, 215, 0, 0.05);
    border-radius: 15px;
    border: 1px solid rgba(255, 215, 0, 0.1);
    transition: all 0.3s ease;
}

.metric-item:hover {
    background: rgba(255, 215, 0, 0.1);
    border-color: rgba(255, 215, 0, 0.3);
}

.metric-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.metric-icon {
    font-size: 1.5rem;
    color: #ffd700;
}

.metric-details h4 {
    color: #ffffff;
    font-size: 1rem;
    margin-bottom: 3px;
}

.metric-details p {
    color: #999999;
    font-size: 0.85rem;
}

.metric-value {
    font-size: 1.4rem;
    font-weight: 700;
    color: #ffd700;
}

.earnings-breakdown {
    grid-column: 1 / -1;
}

.earnings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.earnings-card {
    background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
    border: 1px solid #444444;
    border-radius: 18px;
    padding: 25px;
    text-align: center;
    position: relative;
    transition: all 0.3s ease;
}

.earnings-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(255, 215, 0, 0.2);
    border-color: #ffd700;
}

.earnings-icon {
    font-size: 2.2rem;
    color: #ffd700;
    margin-bottom: 15px;
}

.earnings-title {
    font-size: 1rem;
    color: #cccccc;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.earnings-amount {
    font-size: 2rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 8px;
}

.earnings-subtitle {
    font-size: 0.85rem;
    color: #999999;
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
        font-size: 2.2rem;
    }
    
    .main-content {
        padding: 20px;
    }
    
    .time-filters {
        gap: 10px;
    }
    
    .time-filter {
        padding: 10px 18px;
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
    background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.1), transparent);
    animation: loading 2s infinite;
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
        <div class="content">
            <h1>{{ __('dashboard.main_dashboard') }}</h1>
            <p>{{ __('dashboard.comprehensive_statistics') }}</p>
        </div>
    </div>

    <div class="main-content">
        <div class="time-filters">
            <button class="time-filter active" data-period="today">{{ __('dashboard.today') }}</button>
            <button class="time-filter" data-period="week">{{ __('dashboard.this_week') }}</button>
            <button class="time-filter" data-period="month">{{ __('dashboard.this_month') }}</button>
            <button class="time-filter" data-period="year">{{ __('dashboard.this_year') }}</button>
        </div>

        <div class="stats-overview">
            <div class="stat-card earnings">
                <div class="stat-header">
                    <div class="stat-icon">💰</div>
                    <div class="stat-trend positive">
                        <span>↗</span> +{{ $earningsGrowth }}%
                    </div>
                </div>
                <div class="stat-title">{{ __('dashboard.total_earnings_today') }}</div>
                <div class="stat-number">${{ number_format($todayEarnings, 2) }}</div>
                <div class="stat-subtitle">{{ __('dashboard.from_yesterday') }}</div>
            </div>

            <div class="stat-card users">
                <div class="stat-header">
                    <div class="stat-icon">👥</div>
                    <div class="stat-trend positive">
                        <span>↗</span> +{{ $usersGrowth }}%
                    </div>
                </div>
                <div class="stat-title">{{ __('dashboard.total_users') }}</div>
                <div class="stat-number">{{ number_format($usersCount) }}</div>
                <div class="stat-subtitle">{{ __('dashboard.new_users_today') }}: {{ $newUsersToday }}</div>
            </div>

            <div class="stat-card drivers">
                <div class="stat-header">
                    <div class="stat-icon">🚗</div>
                    <div class="stat-trend positive">
                        <span>↗</span> +{{ $driversGrowth }}%
                    </div>
                </div>
                <div class="stat-title">{{ __('dashboard.total_drivers') }}</div>
                <div class="stat-number">{{ number_format($driversCount) }}</div>
                <div class="stat-subtitle">{{ __('dashboard.active_drivers') }}: {{ $activeDriversToday }}</div>
            </div>

            <div class="stat-card orders">
                <div class="stat-header">
                    <div class="stat-icon">📦</div>
                    <div class="stat-trend positive">
                        <span>↗</span> +{{ $ordersGrowth }}%
                    </div>
                </div>
                <div class="stat-title">{{ __('dashboard.orders_today') }}</div>
                <div class="stat-number">{{ number_format($todayOrders) }}</div>
                <div class="stat-subtitle">{{ __('dashboard.completed') }}: {{ $completedOrdersToday }} | {{ __('dashboard.pending') }}: {{ $pendingOrdersToday }}</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-title">
                    <span>📊</span> {{ __('dashboard.monthly_statistics') }}
                </div>
                <div class="monthly-stats">
                    <div class="mini-stat">
                        <div class="mini-stat-icon">💵</div>
                        <div class="mini-stat-number">${{ number_format($monthlyEarnings, 2) }}</div>
                        <div class="mini-stat-label">{{ __('dashboard.monthly_earnings') }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-icon">📋</div>
                        <div class="mini-stat-number">{{ number_format($monthlyOrders) }}</div>
                        <div class="mini-stat-label">{{ __('dashboard.monthly_orders') }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-icon">🎯</div>
                        <div class="mini-stat-number">{{ $completionRate }}%</div>
                        <div class="mini-stat-label">{{ __('dashboard.completion_rate') }}</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-title">
                    <span>⚡</span> {{ __('dashboard.quick_statistics') }}
                </div>
                <div class="quick-metrics">
                  

                    <div class="metric-item">
                        <div class="metric-info">
                            <div class="metric-icon">🏃‍♂️</div>
                            <div class="metric-details">
                                <h4>{{ __('dashboard.active_driver_now') }}</h4>
                                <p>Online Now</p>
                            </div>
                        </div>
                        <div class="metric-value">{{ $activeDriversNow }}</div>
                    </div>

                    <div class="metric-item">
                        <div class="metric-info">
                            <div class="metric-icon">🕐</div>
                            <div class="metric-details">
                                <h4>{{ __('dashboard.average_delivery_time') }}</h4>
                                <p>Minutes</p>
                            </div>
                        </div>
                        <div class="metric-value">{{ $averageOrderTime }}</div>
                    </div>

                    <div class="metric-item">
                        <div class="metric-info">
                            <div class="metric-icon">🚫</div>
                            <div class="metric-details">
                                <h4>{{ __('dashboard.canceled_orders_today') }}</h4>
                                <p>Today</p>
                            </div>
                        </div>
                        <div class="metric-value">{{ $canceledOrdersToday }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-card earnings-breakdown">
            <div class="card-title">
                <span>💎</span> {{ __('dashboard.earnings_details') }}
            </div>
            <div class="earnings-grid">
                <div class="earnings-card">
                    <div class="earnings-icon">🏆</div>
                    <div class="earnings-title">{{ __('dashboard.total_earnings_all') }}</div>
                    <div class="earnings-amount">${{ number_format($totalEarnings, 2) }}</div>
                    <div class="earnings-subtitle">{{ __('dashboard.since_beginning') }}</div>
                </div>

                <div class="earnings-card">
                    <div class="earnings-icon">🏦</div>
                    <div class="earnings-title">{{ __('dashboard.admin_commission_today') }}</div>
                    <div class="earnings-amount">${{ number_format($adminCommissionToday, 2) }}</div>
                    <div class="earnings-subtitle">{{ __('dashboard.from_total_orders') }}</div>
                </div>

                <div class="earnings-card">
                    <div class="earnings-icon">👨‍💼</div>
                    <div class="earnings-title">{{ __('dashboard.drivers_earnings_today') }}</div>
                    <div class="earnings-amount">${{ number_format($driversEarningsToday, 2) }}</div>
                    <div class="earnings-subtitle">{{ __('dashboard.net_drivers_earnings') }}</div>
                </div>

                <div class="earnings-card">
                    <div class="earnings-icon">📊</div>
                    <div class="earnings-title">{{ __('dashboard.average_order_value') }}</div>
                    <div class="earnings-amount">${{ number_format($averageOrderValue, 2) }}</div>
                    <div class="earnings-subtitle">{{ __('dashboard.for_this_month') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced interactive functionality
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
            
            // Simulate data loading (replace with actual AJAX call)
            setTimeout(() => {
                document.querySelectorAll('.stat-card').forEach(card => {
                    card.classList.remove('loading');
                });
                console.log(`Loading data for period: ${period}`);
            }, 1000);
        });
    });

    // Enhanced hover effects
    document.querySelectorAll('.stat-card, .dashboard-card, .earnings-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Add ripple effect to buttons
    document.querySelectorAll('.time-filter').forEach(button => {
        button.addEventListener('click', function(e) {
            let ripple = document.createElement('span');
            ripple.classList.add('ripple');
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});

// Function to update dashboard with real data
function updateDashboardStats(data) {
    // Update the numbers with smooth animation
    Object.keys(data).forEach(key => {
        const element = document.querySelector(`[data-stat="${key}"]`);
        if (element) {
            animateNumber(element, parseInt(element.textContent.replace(/[^0-9]/g, '')), data[key]);
        }
    });
}

// Animate number changes
function animateNumber(element, start, end) {
    const duration = 1000;
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}
</script>
@endsection