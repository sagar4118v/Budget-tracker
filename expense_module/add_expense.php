<?php

require_once '../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$success = '';
$error   = '';

/*
|--------------------------------------------------------------------------
| FETCH CATEGORIES
|--------------------------------------------------------------------------
*/

$catStmt = $conn->prepare("

    SELECT *
    FROM categories

    WHERE user_id = ?
    AND is_deleted = 0

");

$catStmt->execute([$user_id]);

$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| ADD EXPENSE
|--------------------------------------------------------------------------
*/

if(isset($_POST['add'])){

    $category_id = (int) $_POST['category_id'];

    $amount      = (float) $_POST['amount'];

    $description = trim($_POST['description']);

    $expense_date = $_POST['expense_date'];

    /*
    |--------------------------------------------------------------------------
    | CATEGORY LIMIT
    |--------------------------------------------------------------------------
    */

    $limitStmt = $conn->prepare("

        SELECT monthly_limit

        FROM categories

        WHERE category_id = ?
        AND user_id = ?

    ");

    $limitStmt->execute([
        $category_id,
        $user_id
    ]);

    $category = $limitStmt->fetch(PDO::FETCH_ASSOC);

    $monthlyLimit = $category['monthly_limit'];

    /*
    |--------------------------------------------------------------------------
    | CURRENT MONTH EXPENSE
    |--------------------------------------------------------------------------
    */

    $spentStmt = $conn->prepare("

        SELECT SUM(amount)

        FROM expenses

        WHERE user_id = ?
        AND category_id = ?
        AND MONTH(expense_date) = MONTH(CURDATE())
        AND YEAR(expense_date) = YEAR(CURDATE())

    ");

    $spentStmt->execute([
        $user_id,
        $category_id
    ]);

    $alreadySpent =
        $spentStmt->fetchColumn() ?: 0;

    $newTotal = $alreadySpent + $amount;

    /*
    |--------------------------------------------------------------------------
    | LIMIT CHECK
    |--------------------------------------------------------------------------
    */

    if($newTotal > $monthlyLimit){

        $error =
            "Budget limit exceeded! Remaining: "
            . number_format(
                $monthlyLimit - $alreadySpent,
                2
            );

    }else{

        /*
        |--------------------------------------------------------------------------
        | INSERT EXPENSE
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("

            INSERT INTO expenses
            (
                user_id,
                category_id,
                amount,
                description,
                expense_date
            )

            VALUES
            (
                ?, ?, ?, ?, ?
            )

        ");

        $stmt->execute([

            $user_id,
            $category_id,
            $amount,
            $description,
            $expense_date
        ]);

        /*
        |--------------------------------------------------------------------------
        | INSERT INTO TRANSACTIONS
        |--------------------------------------------------------------------------
        */

        $transaction = $conn->prepare("

            INSERT INTO transactions
            (
                user_id,
                category_id,
                type,
                amount,
                description,
                transaction_date
            )

            VALUES
            (
                ?, ?, 'expense', ?, ?, ?
            )

        ");

        $transaction->execute([

            $user_id,
            $category_id,
            -$amount,
            $description,
            $expense_date
        ]);

        $success = "Expense added successfully!";
    }
}

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

        <a href="../expense_module/view_expense.php">Expenses</a>

        <a href="../transaction_module/view_transaction.php">Transactions</a>

        <a href="../report/report.php">Reports</a>

        <a href="../category_management/index.php">Category</a>

        <a href="../profile/show_profile.php">Profile</a>

        <a href="../login/logout.php">Logout</a>

    </div>

    <div class="main">

        <div class="form-box">

            <h1>💸 Add Expense</h1>

            <?php if($success): ?>

                <div class="success">

                    <?= $success ?>

                </div>

            <?php endif; ?>

            <?php if($error): ?>

                <div class="error">

                    <?= $error ?>

                </div>

            <?php endif; ?>

            <form method="POST">

                <select
                    name="category_id"
                    required
                >

                    <option value="">
                        Select Category
                    </option>

                    <?php foreach($categories as $cat): ?>

                        <option
                            value="<?= $cat['category_id'] ?>"
                        >

                            <?= htmlspecialchars(
                                $cat['category_name']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <input
                    type="number"
                    step="0.01"
                    name="amount"
                    placeholder="Expense Amount"
                    required
                >

                <input
                    type="text"
                    name="description"
                    placeholder="Description"
                    required
                >

                <input
                    type="date"
                    name="expense_date"
                    required
                >

                <button type="submit" name="add">

                    Add Expense

                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>