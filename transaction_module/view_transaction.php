<?php

session_start();

include 'db.php';

/* ======================================
   CHECK LOGIN
====================================== */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login/login.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['name'];

/* ======================================
   FILTER VALUES
====================================== */

$search       = $_GET['search'] ?? '';
$type_filter  = $_GET['type'] ?? '';

$from_date = $_GET['from_date']
    ?? date('Y-m-01');

$to_date = $_GET['to_date']
    ?? date('Y-m-t');


$min_amount   = $_GET['min_amount'] ?? '';
$max_amount   = $_GET['max_amount'] ?? '';

/* ======================================
   MAIN QUERY
====================================== */

$query = "

SELECT 
    t.*,
    c.category_name,
    u.name

FROM transactions t

JOIN categories c
ON t.category_id = c.category_id

JOIN users u
ON t.user_id = u.user_id

WHERE t.user_id = '$user_id'

";

/* SEARCH */

if (!empty($search)) {

    $query .= "

    AND (

        u.name LIKE '%$search%' OR
        c.category_name LIKE '%$search%' OR
        t.description LIKE '%$search%'

    )

    ";
}

/* TYPE */

if (!empty($type_filter)) {

    $query .= "
    AND t.type = '$type_filter'
    ";
}

/* DATE FILTER */

if (!empty($from_date)) {

    $query .= "
    AND DATE(t.transaction_date) >= '$from_date'
    ";
}

if (!empty($to_date)) {

    $query .= "
    AND DATE(t.transaction_date) <= '$to_date'
    ";
}

/* MIN AMOUNT */

if (!empty($min_amount)) {

    $query .= "
    AND ABS(t.amount) >= '$min_amount'
    ";
}

/* MAX AMOUNT */

if (!empty($max_amount)) {

    $query .= "
    AND ABS(t.amount) <= '$max_amount'
    ";
}

/* ORDER */

$query .= "
ORDER BY t.transaction_date DESC
";

/* EXECUTE */

$result = $conn->query($query);

/* ======================================
   SUMMARY QUERY
====================================== */

$summary_query = "

SELECT 

COALESCE(SUM(
    CASE 
        WHEN type='income' 
        THEN amount 
        ELSE 0 
    END
),0) AS total_income,

COALESCE(SUM(
    CASE 
        WHEN type='expense' 
        THEN ABS(amount) 
        ELSE 0 
    END
),0) AS total_expense,

COUNT(*) AS total_transactions

FROM transactions

WHERE user_id = '$user_id'

";

/* APPLY FILTERS */

if (!empty($search)) {

    $summary_query .= "

    AND description LIKE '%$search%'

    ";
}

if (!empty($type_filter)) {

    $summary_query .= "
    AND type = '$type_filter'
    ";
}

if (!empty($from_date)) {

    $summary_query .= "
    AND DATE(transaction_date) >= '$from_date'
    ";
}

if (!empty($to_date)) {

    $summary_query .= "
    AND DATE(transaction_date) <= '$to_date'
    ";
}

if (!empty($min_amount)) {

    $summary_query .= "
    AND ABS(amount) >= '$min_amount'
    ";
}

if (!empty($max_amount)) {

    $summary_query .= "
    AND ABS(amount) <= '$max_amount'
    ";
}

/* EXECUTE SUMMARY */

$summary_result = $conn->query($summary_query);

$summary = $summary_result->fetch_assoc();

/* TOTALS */

$total_income = $summary['total_income'];

$total_expense = $summary['total_expense'];

$count = $summary['total_transactions'];

$balance = $total_income - $total_expense;

?>

<!DOCTYPE html>
<html>

<head>

    <title>Transaction Management</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="wrapper">

    <!-- SIDEBAR -->

    <div class="sidebar">

           
        <a href="../dashboard.php">Dashboard</a>

        <a href="../expense_module/view_expense.php">Expenses</a>

        <a href="#">Transactions</a>

        <a href="../report/report.php">Reports</a>

        <a href="../category_management/index.php">Category</a>

        <a href="../profile/show_profile.php">Profile</a>

        <a href="../login/logout.php">Logout</a>

    </div>

    <!-- MAIN -->

    <div class="main">

        <div class="container">

            <h2>Transaction Management</h2>

            <!-- SUMMARY -->

            <div class="summary">

                <div class="total-income" style="padding: 20px; border-radius: 10px;">

                    Total Income <br>

                    $<?= number_format($total_income, 2) ?>

                </div>

                <div class="total-expense" style="padding: 20px; border-radius: 10px;">

                    Total Expense <br>

                    $<?= number_format($total_expense, 2) ?>

                </div>

                <div class="total-balance" style="padding: 20px; border-radius: 10px;">

                    Balance <br>

                    $<?= number_format($balance, 2) ?>

                </div>

                <div class="total-transactions" style="padding: 20px; border-radius: 10px;">

                    Transactions <br>

                    <?= $count ?>

                </div>

            </div>

            <!-- FILTER FORM -->

            <form method="GET" class="filter-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Search..."
                    value="<?= $search ?>"
                >

                <select name="type">

                    <option value="">All</option>

                    <option value="income"
                    <?= $type_filter == 'income'
                    ? 'selected'
                    : '' ?>>
                    Income
                    </option>

                    <option value="expense"
                    <?= $type_filter == 'expense'
                    ? 'selected'
                    : '' ?>>
                    Expense
                    </option>

                </select>

                <label>From:</label>

                <input
                    type="date"
                    name="from_date"
                    value="<?= $from_date ?>"
                >

                <label>To:</label>

                <input
                    type="date"
                    name="to_date"
                    value="<?= $to_date ?>"
                >

                <input
                    type="number"
                    step="0.01"
                    name="min_amount"
                    placeholder="Min Amount"
                    value="<?= $min_amount ?>"
                >

                <input
                    type="number"
                    step="0.01"
                    name="max_amount"
                    placeholder="Max Amount"
                    value="<?= $max_amount ?>"
                >

                <button type="submit">
                    Filter
                </button>

                <a href="view_transaction.php">

                    <button type="button">
                        Reset
                    </button>

                </a>

            </form>

            <!-- TABLE -->

            <table>

                <tr>

                    <th>S.N</th>

                    <th>User</th>

                    <th>Category</th>

                    <th>Amount</th>

                    <th>Type</th>

                    <th>Description</th>

                    <th>Date</th>

                    <th>Action</th>

                </tr>

                <?php $serial = 1; ?>

                <?php while($row = $result->fetch_assoc()) { ?>

                <tr>

                    <td><?= $serial++ ?></td>

                    <td><?= htmlspecialchars($row['name']) ?></td>

                    <td><?= htmlspecialchars($row['category_name']) ?></td>

                    <td>

                        $<?= number_format($row['amount'], 2) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($row['type']) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($row['description']) ?>

                    </td>

                    <td>

                        <?= date(
                            "Y-m-d H:i:s",
                            strtotime($row['transaction_date'])
                        ) ?>

                    </td>

                    <td>

                        <a href="edit_transaction.php?id=<?= $row['transaction_id'] ?>">

                            <button class="edit-btn">
                                Edit
                            </button>

                        </a>

                        <a href="delete_transaction.php?id=<?= $row['transaction_id'] ?>"
                           onclick="return confirm('Delete this transaction?')">

                            <button class="delete-btn">
                                Delete
                            </button>

                        </a>

                    </td>

                </tr>

                <?php } ?>

            </table>

        </div>

    </div>

</div>

</body>

</html>
```
