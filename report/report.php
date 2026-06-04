<?php
session_start();
require_once 'db.php';


//protecting pages
if (!isset($_SESSION['user_id'])) {
    header("Location: login/login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');


// Get data
$stmt = $pdo->prepare("SELECT SUM(amount) FROM transactions WHERE user_id=? AND type='income' AND transaction_date BETWEEN ? AND ?");
$stmt->execute([$user_id, $from_date, $to_date]);
$income = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT SUM(ABS(amount)) FROM transactions WHERE user_id=? AND type='expense' AND transaction_date BETWEEN ? AND ?");
$stmt->execute([$user_id, $from_date, $to_date]);
$expense = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT c.category_name, SUM(ABS(t.amount)) as spent FROM transactions t JOIN categories c ON t.category_id = c.category_id WHERE t.user_id=? AND t.type='expense' AND t.transaction_date BETWEEN ? AND ? GROUP BY c.category_name");
$stmt->execute([$user_id, $from_date, $to_date]);
$categories = $stmt->fetchAll();

// Get trend data
$date1 = new DateTime($from_date);
$date2 = new DateTime($to_date);
$days_diff = $date1->diff($date2)->days;

if($days_diff <= 31) {
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(transaction_date, '%b %d') as period,
            SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN type='expense' THEN ABS(amount) ELSE 0 END) as expense
        FROM transactions
        WHERE user_id=? AND transaction_date BETWEEN ? AND ?
        GROUP BY transaction_date
        ORDER BY transaction_date
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(transaction_date, '%b %Y') as period,
            SUM(CASE WHEN type='income' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN type='expense' THEN ABS(amount) ELSE 0 END) as expense
        FROM transactions
        WHERE user_id=? AND transaction_date BETWEEN ? AND ?
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY transaction_date
    ");
}
$stmt->execute([$user_id, $from_date, $to_date]);
$trend = $stmt->fetchAll();

// Handle CSV download
if(isset($_GET['download']) && $_GET['download'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="report_'.date('Y-m-d').'.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Budget Report']);
    fputcsv($out, ['Period:', $from_date, 'to', $to_date]);
    fputcsv($out, []);
    fputcsv($out, ['Summary']);
    fputcsv($out, ['Total Income', '$' . number_format($income, 2)]);
    fputcsv($out, ['Total Expense', '$' . number_format($expense, 2)]);
    fputcsv($out, ['Net Balance', '$' . number_format($income - $expense, 2)]);
    fputcsv($out, []);
    fputcsv($out, ['Expenses by Category']);
    fputcsv($out, ['Category', 'Amount']);
    foreach($categories as $cat) {
        fputcsv($out, [$cat['category_name'], '$' . number_format($cat['spent'], 2)]);
    }
    fclose($out);
    exit;
}

// Get user name
$stmt = $pdo->prepare("SELECT name FROM users WHERE user_id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Budget Tracker</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="icon" type="image/x-icon" href="../logo/logo.png">
</head>
<body>
    
    <div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2 style="color: white;">
            <?= htmlspecialchars($user['name']) ?>
        </h2>        
        <a href="../dashboard.php">Dashboard</a>

        <a href="../expense_module/view_expense.php">Expenses</a>

        <a href="../transaction_module/view_transaction.php">Transactions</a>

        <a href="../report/report.php">Reports</a>

        <a href="../category_management/index.php">Category</a>

        <a href="../profile/show_profile.php">Profile</a>

        <a href="../login/logout.php">Logout</a>
    </div>
    

    <div class="container" id="reportContent">

    <div class="heading_filter">
    
    
    <form method="GET" class="filter-form" id="filterForm">
        
        <input type="date" name="from_date" value="<?=$from_date?>">
        <input type="date" name="to_date" value="<?=$to_date?>">
        <button type="submit">Show</button>
        
        <div class="dropdown">
            <button type="button" class="dropbtn"> Download ▼</button>
            <div class="dropdown-content">
                <a href="#" onclick="downloadPDF()"> Download as PDF</a>
                <a href="?<?=$_SERVER['QUERY_STRING']?>&download=csv"> Download as CSV</a>
            </div>
        </div>
    </form></div>
        
    <div class="cards">
        <div class="card green">
            <h3>Income</h3>
            <div class="big">$<?=number_format($income,2)?></div>
        </div>
        <div class="card red">
            <h3>Expense</h3>
            <div class="big">$<?=number_format($expense,2)?></div>
        </div>
        <div class="card blue">
            <h3>Balance</h3>
            <div class="big">$<?=number_format($income-$expense,2)?></div>
        </div>
    </div>
    
    <div class="two-col">
        <div class="col">
            <h3> Expenses by Category</h3>
            <table>
                <?php foreach($categories as $cat): ?>
                <tr>
                    <td><?=$cat['category_name']?></td>
                    <td class="right">$<?=number_format($cat['spent'],2)?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total">
                    <td><strong>Total</strong></td>
                    <td class="right"><strong>$<?=number_format($expense,2)?></strong></td>
                </tr>
            </table>
        </div>
        
        <div class="col">
            <h3> Budget vs Actual</h3>
            <table>
                <tr>
                    <th>Category</th>
                    <th>Budget</th>
                    <th>Actual</th>
                    <th>Status</th>
                </tr>
                <?php 
                $budgets = ['Food & Dining'=>500,'Transportation'=>300,'Entertainment'=>200,'Healthcare'=>400,'Shopping'=>600,'Utilities'=>250];
                foreach($categories as $cat):
                    $budget = isset($budgets[$cat['category_name']]) ? $budgets[$cat['category_name']] : 0;
                    $over = $cat['spent'] > $budget;
                ?>
                <tr>
                    <td><?=$cat['category_name']?></td>
                    <td>$<?=number_format($budget,2)?></td>
                    <td>$<?=number_format($cat['spent'],2)?></td>
                    <td class="<?=$over?'over':'good'?>"><?=$over?' Over':' Good'?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    
    <!-- TRENDLINE FOR SELECTED DATE RANGE -->
    <div class="trend-box">
        <h3> Trend (<?=date('M d, Y', strtotime($from_date))?> - <?=date('M d, Y', strtotime($to_date))?>)</h3>
        <?php if(count($trend) > 0): ?>
            <canvas id="trendChart" height="100"></canvas>
        <?php else: ?>
            <p style="text-align:center; padding:20px; ">No data available for this period</p>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        <?=$user['name']?> | <?=date('M d, Y', strtotime($from_date))?> - <?=date('M d, Y', strtotime($to_date))?>
    </div>
</div>
 </div>
</div>

<script>
// Trendline Chart
var ctx = document.getElementById('trendChart').getContext('2d');
var trendChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?=json_encode(array_column($trend, 'period'))?>,
        datasets: [{
            label: 'Income',
            data: <?=json_encode(array_column($trend, 'income'))?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40,167,69,0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }, {
            label: 'Expense',
            data: <?=json_encode(array_column($trend, 'expense'))?>,
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220,53,69,0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': $' + context.raw.toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: function(value) { return '$' + value; } }
            }
        }
    }
});

// Download as PDF
function downloadPDF() {
    var element = document.getElementById('reportContent');
    var opt = {
        margin: [0.5, 0.5, 0.5, 0.5],
        filename: 'budget_report_<?=date('Y-m-d')?>.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(element).save();
}
</script>

<style>
/* Dropdown styles */
.dropdown {
    position: relative;
    display: inline-block;
}

.dropbtn {
    background: #28a745;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.dropbtn:hover {
    background: #218838;
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    border-radius: 5px;
}

.dropdown-content a {
    color: black;
    padding: 10px 15px;
    text-decoration: none;
    display: block;
    border-radius: 5px;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.dropdown:hover .dropdown-content {
    display: block;
}

@media print {
    .filter-form button, .dropdown, .dropbtn {
        display: none;
    }
}
</style>

</body>
</html>