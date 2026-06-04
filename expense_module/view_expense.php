<?php

require_once '../config.php';

session_start();

if(!isset($_SESSION['user_id'])){

    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("

    SELECT
        e.*,
        c.category_name

    FROM expenses e

    JOIN categories c
    ON e.category_id = c.category_id

    WHERE e.user_id = ?

    ORDER BY expense_date DESC

");

$stmt->execute([$user_id]);

$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Budget Tracker</title>
    <link rel="icon" type="image/x-icon" href="../logo/logo.png">


    <link rel="stylesheet" href="expense.css">

</head>

<body>

<div class="wrapper">

<div class="sidebar">

    <h2>
        <?= htmlspecialchars($_SESSION['name']) ?>
    </h2>
    
    <a href="../dashboard.php">Dashboard</a>

    <a href="#">Expenses</a>

    <a href="../transaction_module/view_transaction.php">Transactions</a>

    <a href="../report/report.php">Reports</a>

    <a href="../category_management/index.php">Category</a>

    <a href="../profile/show_profile.php">Profile</a>

    <a href="../login/logout.php">Logout</a>

</div>

<div class="main">

<a href="add_expense.php">Add Expense</a>

<h1>📋 All Expenses</h1>

<table>

<tr>

    <th>SN</th>
    <th>Category</th>
    <th>Amount</th>
    <th>Description</th>
    <th>Date</th>
    <th>Actions</th>

</tr>

<?php $sn = 1; ?>

<?php foreach($expenses as $expense): ?>

<tr>

    <td><?= $sn++ ?></td>

    <td>
        <?= htmlspecialchars($expense['category_name']) ?>
    </td>

    <td>
        <?= number_format($expense['amount'],2) ?>
    </td>

    <td>
        <?= htmlspecialchars($expense['description']) ?>
    </td>

    <td>
        <?= $expense['expense_date'] ?>
    </td>

    <td>

        <a
            href="edit_expense.php?id=<?= $expense['expense_id'] ?>"
            class="edit-btn"
        >

            Edit

        </a>

        <a
            href="delete_expense.php?id=<?= $expense['expense_id'] ?>"
            class="delete-btn"
            onclick="return confirm('Delete expense?')"
        >

            Delete

        </a>

    </td>

</tr>

<?php endforeach; ?>

</table>

</div>

</div>

</body>
</html>