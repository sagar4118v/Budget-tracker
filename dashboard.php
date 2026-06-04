<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| DASHBOARD PAGE
|--------------------------------------------------------------------------
| This dashboard displays:
|
| 1. Total Income
| 2. Total Expense
| 3. Net Saving / Balance
| 4. Charts & Analytics
| 5. Filtered Transactions
|
| Features:
| - Search Filter
| - Date Range Filter
| - Bar Chart
| - Doughnut Chart
| - Line Chart
| - Session Protection
| - Secure Output Escaping
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| REQUIRED FILES
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

/*
|--------------------------------------------------------------------------
| START SESSION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| PROTECT PAGE
|--------------------------------------------------------------------------
| Redirect user to login page
| if user session does not exist.
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {

    header("Location: login/login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| LOGGED IN USER
|--------------------------------------------------------------------------
*/

$user_id   = (int) $_SESSION['user_id'];
$user_name = $_SESSION['name'] ?? 'User';

/*
|--------------------------------------------------------------------------
| GREETING MESSAGE
|--------------------------------------------------------------------------
*/

$current_hour = (int) date('H');

if ($current_hour < 12) {

    $greeting = "Good Morning";

} elseif ($current_hour < 17) {

    $greeting = "Good Afternoon";

} else {

    $greeting = "Good Evening";
}

/*
|--------------------------------------------------------------------------
| FILTER VALUES
|--------------------------------------------------------------------------
| Default:
| - From Date = First day of current month
| - To Date   = Last day of current month
|--------------------------------------------------------------------------
*/

$filter = [

    'search' => $_GET['search'] ?? '',

    'from_date' => $_GET['from_date']
        ?? date('Y-m-01'),

    'to_date' => $_GET['to_date']
        ?? date('Y-m-t')
];

/*
|--------------------------------------------------------------------------
| FETCH TRANSACTIONS
|--------------------------------------------------------------------------
| false = all records for charts
| true  = limited/filtered records for table
|--------------------------------------------------------------------------
*/

$allTransactions = getTransactions(
    $user_id,
    $filter,
    false
);

$transactions = getTransactions(
    $user_id,
    $filter,
    true
);

/*
|--------------------------------------------------------------------------
| CHART DATA VARIABLES
|--------------------------------------------------------------------------
*/

$dates = [];

$incomeChartData = [];

$expenseChartData = [];

/*
|--------------------------------------------------------------------------
| PREPARE CHART DATA
|--------------------------------------------------------------------------
*/

foreach ($allTransactions as $transaction) {

    $dates[] = $transaction['transaction_date'];

    /*
    |--------------------------------------------------------------------------
    | INCOME
    |--------------------------------------------------------------------------
    */

    if ($transaction['type'] === 'income') {

        $incomeChartData[] = (float)
            $transaction['amount'];

        $expenseChartData[] = 0;
    }

    /*
    |--------------------------------------------------------------------------
    | EXPENSE
    |--------------------------------------------------------------------------
    */

    else {

        $incomeChartData[] = 0;

        $expenseChartData[] = abs(
            (float) $transaction['amount']
        );
    }
}

/*
|--------------------------------------------------------------------------
| TOTAL INCOME
|--------------------------------------------------------------------------
*/

$incomeStatement = $conn->prepare("

    SELECT SUM(amount)

    FROM transactions

    WHERE user_id = ?
    AND type = 'income'

    AND transaction_date
    BETWEEN ? AND ?

");

$incomeStatement->execute([

    $user_id,

    $filter['from_date'],

    $filter['to_date']
]);

$totalIncome = $incomeStatement->fetchColumn() ?: 0;

/*
|--------------------------------------------------------------------------
| TOTAL EXPENSE
|--------------------------------------------------------------------------
*/

$expenseStatement = $conn->prepare("

    SELECT SUM(ABS(amount))

    FROM transactions

    WHERE user_id = ?
    AND type = 'expense'

    AND transaction_date
    BETWEEN ? AND ?

");

$expenseStatement->execute([

    $user_id,

    $filter['from_date'],

    $filter['to_date']
]);

$totalExpense = $expenseStatement->fetchColumn() ?: 0;

/*
|--------------------------------------------------------------------------
| NET BALANCE / SAVING
|--------------------------------------------------------------------------
*/

$totalBalance = $totalIncome - $totalExpense;

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Budget Tracker</title>
    <link rel="icon" type="image/x-icon" href="logo/logo.png">


    <!-- Chart JS -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- CSS -->

    <link rel="stylesheet" href="style.css">

    <!-- Favicon -->


</head>

<body>

<div class="wrapper">

    <!-- ======================================
         SIDEBAR
    ======================================= -->

    <div class="sidebar">

        <h2>
            <?= htmlspecialchars($user_name) ?>
        </h2>

        <a href="dashboard.php">Dashboard</a>

        <a href="expense_module/view_expense.php">Expenses</a>

        <a href="transaction_module/view_transaction.php">Transactions</a>

        <a href="report/report.php">Reports</a>

        <a href="category_management/index.php">Category</a>

        <a href="profile/show_profile.php">Profile</a>

        <a href="login/logout.php">Logout</a>

    </div>

    <!-- ======================================
         MAIN CONTENT
    ======================================= -->

    <div class="main">

        <!-- ======================================
             NAVBAR
        ======================================= -->

        <div class="navbar">
            <img src="logo/logo.png" alt="Logo" style="width: 100px; ">
            <h1>Budget Tracker</h1>

            <h2>

                💰 <?= $greeting ?>

            </h2>

            <!-- FILTER FORM -->

            <form method="GET" class="search-form">

                <!-- SEARCH -->

                <input
                    type="text"
                    name="search"
                    placeholder="Search..."
                    value="<?= htmlspecialchars($filter['search']) ?>"
                >

                <!-- FROM DATE -->

                <input
                    type="date"
                    name="from_date"
                    value="<?= htmlspecialchars($filter['from_date']) ?>"
                >

                <!-- TO DATE -->

                <input
                    type="date"
                    name="to_date"
                    value="<?= htmlspecialchars($filter['to_date']) ?>"
                >

                <!-- BUTTON -->

                <button type="submit">

                    Filter

                </button>

                <!-- CLEAR -->

                <a href="dashboard.php" class="clear">

                    Clear

                </a>

            </form>

        </div>

        <!-- ======================================
             STATISTICS CARDS
        ======================================= -->

        <div class="cards">

            <div class="card c1">
                Saving:
                <?= number_format((float)$totalBalance, 2) ?>
            </div>
            <div class="card c2">
                Income:
                <?= number_format((float)$totalIncome, 2) ?>
            </div>
            <div class="card c3">
                Expense:
                <?= number_format((float)$totalExpense, 2) ?>
            </div>
        </div>

        <!-- ======================================
             CHART SECTION
        ======================================= -->

        <div class="charts">

            <!-- BAR CHART -->

            <div class="chart-box">

                <canvas id="barChart"></canvas>

            </div>

            <!-- PIE CHART -->

            <div class="chart-box">

                <canvas id="pieChart"></canvas>

            </div>

            <!-- LINE CHART -->

            <div class="chart-box full">

                <canvas id="lineChart"></canvas>

            </div>

        </div>

        <!-- ======================================
             TRANSACTION TABLE
        ======================================= -->

        <table>

            <tr>

                <th>Date</th>

                <th>Description</th>

                <th>Amount</th>

            </tr>

            <?php if (!empty($transactions)): ?>

                <?php foreach ($transactions as $transaction): ?>

                    <tr>

                        <td>

                            <?= htmlspecialchars(
                                $transaction['transaction_date']
                            ) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $transaction['description']
                            ) ?>

                        </td>

                        <td class="<?= $transaction['amount'] > 0
                            ? 'income'
                            : 'expense'
                        ?>">

                            <?= number_format(
                                (float) $transaction['amount'],
                                2
                            ) ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="3">

                        No transactions found

                    </td>

                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>

<!-- ======================================
     JAVASCRIPT CHARTS
====================================== -->

<script>

/*
|--------------------------------------------------------------------------
| CHART VARIABLES
|--------------------------------------------------------------------------
*/

const labels =
    <?= json_encode($dates) ?>;

const incomeData =
    <?= json_encode($incomeChartData) ?>;

const expenseData =
    <?= json_encode($expenseChartData) ?>;

/*
|--------------------------------------------------------------------------
| BAR CHART
|--------------------------------------------------------------------------
*/

new Chart(

    document.getElementById('barChart'),

    {

        type: 'bar',

        data: {

            labels: labels,

            datasets: [

                {
                    label: 'Income',

                    data: incomeData,

                    backgroundColor: '#22c55e'
                },

                {
                    label: 'Expense',

                    data: expenseData,

                    backgroundColor: '#ef4444'
                }
            ]
        }
    }
);

/*
|--------------------------------------------------------------------------
| DOUGHNUT CHART
|--------------------------------------------------------------------------
*/

new Chart(

    document.getElementById('pieChart'),

    {

        type: 'doughnut',

        data: {

            labels: ['Income', 'Expense'],

            datasets: [

                {

                    data: [

                        <?= $totalIncome ?>,

                        <?= $totalExpense ?>

                    ],

                    backgroundColor: [

                        '#22c55e',

                        '#ef4444'
                    ]
                }
            ]
        }
    }
);

/*
|--------------------------------------------------------------------------
| LINE CHART
|--------------------------------------------------------------------------
*/

new Chart(

    document.getElementById('lineChart'),

    {

        type: 'line',

        data: {

            labels: labels,

            datasets: [

                {

                    label: 'Financial Trend',

                    data: incomeData.map(

                        (value, index) =>

                        value - expenseData[index]
                    ),

                    borderColor: '#3b82f6',

                    fill: false,

                    tension: 0.4
                }
            ]
        }
    }
);

</script>

</body>
</html>