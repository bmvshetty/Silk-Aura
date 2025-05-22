<?php
include 'db_config.php';

// Chart 1: Placed Orders per day
$query_orders = "
    SELECT DATE(order_date) AS order_day, COUNT(*) AS total_orders
    FROM placed_orders
    GROUP BY order_day
    ORDER BY order_day
";
$result_orders = mysqli_query($conn, $query_orders);

$data_orders = [["Date", "Number of Orders"]];
while ($row = mysqli_fetch_assoc($result_orders)) {
    $data_orders[] = [$row['order_day'], (int)$row['total_orders']];
}
$data_orders_json = json_encode($data_orders);

// Chart 2: Inventory Stock Levels by product
$query_stock = "
    SELECT item_name, SUM(quantity) AS total_quantity
    FROM selleritem
    GROUP BY item_name
    ORDER BY total_quantity DESC
";
$result_stock = mysqli_query($conn, $query_stock);

$data_stock = [["Product", "Stock Quantity"]];
while ($row = mysqli_fetch_assoc($result_stock)) {
    $data_stock[] = [htmlspecialchars($row['item_name']), (int)$row['total_quantity']];
}
$data_stock_json = json_encode($data_stock);

// Chart 3: Customer Registrations Over Time
$query_customers = "
    SELECT DATE(created_at) AS reg_day, COUNT(*) AS total_customers
    FROM users
    WHERE status = 'active'
    GROUP BY reg_day
    ORDER BY reg_day
";
$result_customers = mysqli_query($conn, $query_customers);

$data_customers = [["Date", "New Customers"]];
while ($row = mysqli_fetch_assoc($result_customers)) {
    $data_customers[] = [$row['reg_day'], (int)$row['total_customers']];
}
$data_customers_json = json_encode($data_customers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Dashboard Charts</title>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
google.charts.load('current', { packages: ['corechart', 'line'] });
google.charts.setOnLoadCallback(drawCharts);

function drawCharts() {
    // Chart 1: Placed Orders Line Chart
    var dataOrders = google.visualization.arrayToDataTable(<?php echo $data_orders_json; ?>);
    var optionsOrders = {
        title: 'Placed Orders Over Time',
        curveType: 'function',
        legend: { position: 'bottom' },
        hAxis: { title: 'Date' },
        vAxis: { title: 'Number of Orders' },
        chartArea: { width: '70%', height: '70%' },
        backgroundColor: '#fff',
        fontName: 'Arial'
    };
    var chartOrders = new google.visualization.LineChart(document.getElementById('orders_chart'));
    chartOrders.draw(dataOrders, optionsOrders);

    // Chart 2: Inventory Stock Levels Vertical Bar Chart
    var dataStock = google.visualization.arrayToDataTable(<?php echo $data_stock_json; ?>);
    var optionsStock = {
        title: 'Inventory Stock Levels',
        legend: { position: 'none' },
        hAxis: {
            title: 'Product',
            slantedText: true,
            slantedTextAngle: 45
        },
        vAxis: { title: 'Quantity' },
        chartArea: { width: '70%', height: '70%' },
        colors: ['#4285F4'],
        backgroundColor: '#fff',
        fontName: 'Arial'
    };
    var chartStock = new google.visualization.ColumnChart(document.getElementById('stock_chart'));
    chartStock.draw(dataStock, optionsStock);

    // Chart 3: Customer Registrations Area Chart
    var dataCustomers = google.visualization.arrayToDataTable(<?php echo $data_customers_json; ?>);
    var optionsCustomers = {
        title: 'Customer Registrations Over Time',
        legend: { position: 'bottom' },
        hAxis: { title: 'Date' },
        vAxis: { title: 'New Customers' },
        chartArea: { width: '70%', height: '70%' },
        backgroundColor: '#fff',
        fontName: 'Arial',
        areaOpacity: 0.4
    };
    var chartCustomers = new google.visualization.AreaChart(document.getElementById('customers_chart'));
    chartCustomers.draw(dataCustomers, optionsCustomers);
}

// Redraw charts on window resize
window.addEventListener('resize', drawCharts);
</script>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px auto;
    max-width: 1000px;
    background: #f9f9f9;
}

.chart-container {
    background: white;
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 40px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

h2 {
    text-align: center;
    color: #333;
    margin-bottom: 10px;
}

#orders_chart, #stock_chart, #customers_chart {
    width: 100%;
    height: 450px;
}
</style>
</head>
<body>

<div class="chart-container">
    <h2>Placed Orders Over Time</h2>
    <div id="orders_chart"></div>
</div>

<div class="chart-container">
    <h2>Inventory Stock Levels (By Product)</h2>
    <div id="stock_chart"></div>
</div>

<div class="chart-container">
    <h2>Customer Registrations Over Time</h2>
    <div id="customers_chart"></div>
</div>

</body>
</html>