<?php
session_start();

if (isset($_GET['shop_id'])) {
    $shop_id = $_GET['shop_id'];
    $_SESSION['shop_id'] = $shop_id;
} else if (isset($_SESSION['shop_id'])) {
    $shop_id = $_SESSION['shop_id'];
} else {
    die("Shop ID is missing!");
}

$conn = new mysqli("localhost", "root", "", "silk_aura");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Stock quantity
$query = "SELECT item_name, SUM(quantity) as total_quantity FROM selleritem WHERE shop_id = ? GROUP BY item_name";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $shop_id);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
$quantities = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row['item_name'];
    $quantities[] = (int)$row['total_quantity'];
}

// Product prices
$priceQuery = "SELECT item_name, price FROM selleritem WHERE shop_id = ?";
$stmt2 = $conn->prepare($priceQuery);
$stmt2->bind_param("i", $shop_id);
$stmt2->execute();
$priceResult = $stmt2->get_result();

$productsForPriceChart = [];
$prices = [];

while ($row = $priceResult->fetch_assoc()) {
    $productsForPriceChart[] = $row['item_name'];
    $prices[] = (float)$row['price'];
}

// Area chart data: placed orders per day
$placedOrdersQuery = "SELECT DATE(order_date) as order_day, COUNT(*) as total FROM orders WHERE shop_id = ? AND order_status = 'placed' GROUP BY order_day ORDER BY order_day";
$stmt3 = $conn->prepare($placedOrdersQuery);
$stmt3->bind_param("i", $shop_id);
$stmt3->execute();
$placedResult = $stmt3->get_result();

$placedOrdersDates = [];
$placedOrdersCounts = [];

while ($row = $placedResult->fetch_assoc()) {
    $placedOrdersDates[] = $row['order_day'];
    $placedOrdersCounts[] = (int)$row['total'];
}

// Donut chart data: order_status counts (cart, placed, cancelled)
$statusQuery = "SELECT order_status, COUNT(*) as count FROM orders WHERE shop_id = ? GROUP BY order_status";
$stmt4 = $conn->prepare($statusQuery);
$stmt4->bind_param("i", $shop_id);
$stmt4->execute();
$statusResult = $stmt4->get_result();

$statusCounts = [
    'cart' => 0,
    'placed' => 0,
    'cancelled' => 0
];
while ($row = $statusResult->fetch_assoc()) {
    $status = $row['order_status'];
    $count = (int)$row['count'];
    if (array_key_exists($status, $statusCounts)) {
        $statusCounts[$status] = $count;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Silk Auro Seller Dashboard</title>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f0f0f0;
        margin: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    header {
        background: linear-gradient(to right, #ff7e5f, #feb47b);
        color: white;
        padding: 20px;
        font-size: 28px;
        text-align: center;
        font-weight: bold;
        width: 100%;
    }
    .main-content {
        width: 80%;
        margin-top: 30px;
        padding: 20px;
        background-color: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        min-height: 350px;
    }
    .button-group {
        text-align: center;
        margin-bottom: 30px;
    }
    .dashboard-btn {
        background-color: #ff7e5f;
        color: white;
        border: none;
        padding: 12px 25px;
        margin: 8px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .dashboard-btn:hover,
    .dashboard-btn.active {
        background-color: #e85c43;
    }
    .dashboard-section {
        display: none;
        animation: fadeIn 0.4s ease;
    }
    .chart-container {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        padding: 25px;
        margin-bottom: 30px;
        position: relative;
        height: 300px;
        width: 100%;
        max-width: 800px;
    }
    .chart-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #333;
        text-align: center;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body>

<header>Silk Auro Seller Dashboard</header>

<div class="main-content">
    <div class="button-group">
        <button class="dashboard-btn" onclick="showSection('stocksSection', this)">Stocks</button>
        <button class="dashboard-btn" onclick="showSection('ordersSection', this)">Orders</button>
    </div>

    <div id="stocksSection" class="dashboard-section">
        <div class="chart-container">
            <div class="chart-title">Stock Quantities (Bar Chart)</div>
            <div id="barChart"></div>
        </div>
        <div class="chart-container">
            <div class="chart-title">Price of Products (Bar Chart)</div>
            <div id="priceChart"></div>
        </div>
    </div>

    <div id="ordersSection" class="dashboard-section">
        <div class="chart-container">
            <div class="chart-title">Placed Orders Over Time (Area Chart)</div>
            <div id="areaChart"></div>
        </div>
        <div class="chart-container">
            <div class="chart-title">Order Status Distribution (Donut Chart)</div>
            <div id="donutChart"></div>
        </div>
    </div>
</div>

<script>
    google.charts.load('current', { packages: ['corechart'] });
    
    let chartsDrawn = {
        stocks: false,
        orders: false
    };

    function drawStocksCharts() {
        // Stock Quantities Bar Chart
        var quantityData = google.visualization.arrayToDataTable([
            ['Product', 'Quantity', { role: 'style' }],
            <?php 
                $colors = ['#ff7e5f', '#feb47b', '#a29bfe', '#81ecec', '#00cec9', '#fab1a0', '#e17055', '#74b9ff', '#55efc4'];
                foreach ($products as $key => $product) {
                    $color = $colors[$key % count($colors)];
                    echo "['$product', $quantities[$key], '$color'],";
                }
            ?>
        ]);
        var quantityOptions = {
            legend: 'none',
            chartArea: {width: '70%'},
            hAxis: {title: 'Product'},
            vAxis: {title: 'Quantity', minValue: 0}
        };
        var quantityChart = new google.visualization.ColumnChart(document.getElementById('barChart'));
        quantityChart.draw(quantityData, quantityOptions);

        // Product Prices Bar Chart
        var priceData = google.visualization.arrayToDataTable([
            ['Product', 'Price', { role: 'style' }],
            <?php 
                foreach ($productsForPriceChart as $key => $product) {
                    $color = $colors[$key % count($colors)];
                    echo "['$product', $prices[$key], '$color'],";
                }
            ?>
        ]);
        var priceOptions = {
            legend: 'none',
            chartArea: {width: '70%'},
            hAxis: {title: 'Product'},
            vAxis: {title: 'Price', minValue: 0}
        };
        var priceChart = new google.visualization.ColumnChart(document.getElementById('priceChart'));
        priceChart.draw(priceData, priceOptions);
    }

    function drawOrdersCharts() {
        // Placed Orders Area Chart
        var areaData = new google.visualization.DataTable();
        areaData.addColumn('string', 'Date');
        areaData.addColumn('number', 'Placed Orders');

        areaData.addRows([
            <?php
                foreach ($placedOrdersDates as $key => $date) {
                    echo "['$date', " . $placedOrdersCounts[$key] . "],";
                }
            ?>
        ]);

        var areaOptions = {
            title: '',
            hAxis: {title: 'Date'},
            vAxis: {title: 'Orders', minValue: 0},
            legend: { position: 'none' },
            areaOpacity: 0.3,
            colors: ['#ff7e5f']
        };

        var areaChart = new google.visualization.AreaChart(document.getElementById('areaChart'));
        areaChart.draw(areaData, areaOptions);

        // Donut Chart for order_status counts
        var donutData = google.visualization.arrayToDataTable([
            ['Order Status', 'Count'],
            ['Cart', <?php echo $statusCounts['cart']; ?>],
            ['Placed', <?php echo $statusCounts['placed']; ?>],
            ['Cancelled', <?php echo $statusCounts['cancelled']; ?>]
        ]);

        var donutOptions = {
            pieHole: 0.5,
            colors: ['#ffbe76', '#ff7979', '#badc58'],
            legend: { position: 'right' },
            chartArea: {width: '75%', height: '75%'}
        };

        var donutChart = new google.visualization.PieChart(document.getElementById('donutChart'));
        donutChart.draw(donutData, donutOptions);
    }

    function showSection(sectionId, btn) {
        // Hide all sections
        document.querySelectorAll('.dashboard-section').forEach(sec => sec.style.display = 'none');
        // Remove active class from all buttons
        document.querySelectorAll('.dashboard-btn').forEach(button => button.classList.remove('active'));
        // Show selected section
        document.getElementById(sectionId).style.display = 'block';
        btn.classList.add('active');

        // Draw charts only once per section
        if (sectionId === 'stocksSection' && !chartsDrawn.stocks) {
            google.charts.setOnLoadCallback(drawStocksCharts);
            chartsDrawn.stocks = true;
        }
        if (sectionId === 'ordersSection' && !chartsDrawn.orders) {
            google.charts.setOnLoadCallback(drawOrdersCharts);
            chartsDrawn.orders = true;
        }
    }

    // Show buttons only, no chart on page load
</script>

</body>
</html>