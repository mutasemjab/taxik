<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Not Available</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 500px;
        }

        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 15px;
        }

        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: 600;
            margin-top: 20px;
        }

        .status-completed {
            background: #4CAF50;
            color: white;
        }

        .status-cancelled {
            background: #f44336;
            color: white;
        }

        .status-pending {
            background: #ff9800;
            color: white;
        }

        .order-info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            @if($order->status->value === 'completed')
                ✅
            @elseif(in_array($order->status->value, ['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job']))
                ❌
            @else
                ⏳
            @endif
        </div>

        <h1>Tracking Not Available</h1>

        <p>
            @if($order->status->value === 'pending')
                This order is waiting for a driver to accept. Tracking will be available once a driver accepts your order.
            @elseif($order->status->value === 'completed')
                This order has been completed. Live tracking is no longer available.
            @elseif(in_array($order->status->value, ['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job']))
                This order has been cancelled. Tracking is not available for cancelled orders.
            @else
                Tracking is not available for this order at the moment.
            @endif
        </p>

        <div class="order-info">
            <strong>Order #{{ $order->number }}</strong><br>
            <span class="status-badge 
                @if($order->status->value === 'completed') status-completed
                @elseif(in_array($order->status->value, ['user_cancel_order', 'driver_cancel_order', 'cancel_cron_job'])) status-cancelled
                @else status-pending
                @endif">
                {{ ucwords(str_replace('_', ' ', $order->status->value)) }}
            </span>
        </div>
    </div>
</body>
</html>