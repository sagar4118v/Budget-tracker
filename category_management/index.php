<?php

declare(strict_types=1);

require_once '../config.php';

session_start();

/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login/login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| ADD CATEGORY
|--------------------------------------------------------------------------
*/

if (isset($_POST['add'])) {

    $name  = trim($_POST['name']);
    $limit = (float) $_POST['monthly_limit'];

    $stmt = $conn->prepare("

        INSERT INTO categories
        (
            user_id,
            category_name,
            monthly_limit,
            is_deleted
        )

        VALUES
        (
            ?, ?, ?, 0
        )

    ");

    $stmt->execute([
        $user_id,
        $name,
        $limit
    ]);

    header("Location: index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| UPDATE CATEGORY
|--------------------------------------------------------------------------
*/

if (isset($_POST['update'])) {

    $id    = (int) $_POST['id'];
    $name  = trim($_POST['name']);
    $limit = (float) $_POST['monthly_limit'];

    $stmt = $conn->prepare("

        UPDATE categories

        SET
            category_name = ?,
            monthly_limit = ?

        WHERE category_id = ?
        AND user_id = ?

    ");

    $stmt->execute([
        $name,
        $limit,
        $id,
        $user_id
    ]);

    header("Location: index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| DELETE CATEGORY (SOFT DELETE)
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("

        UPDATE categories

        SET is_deleted = 1

        WHERE category_id = ?
        AND user_id = ?

    ");

    $stmt->execute([
        $id,
        $user_id
    ]);

    header("Location: index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| FETCH CATEGORIES
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("

    SELECT *
    FROM categories

    WHERE user_id = ?
    AND is_deleted = 0

    ORDER BY category_id DESC

");

$stmt->execute([$user_id]);

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| TOTAL LIMIT
|--------------------------------------------------------------------------
*/

$totalStmt = $conn->prepare("

    SELECT SUM(monthly_limit)

    FROM categories

    WHERE user_id = ?
    AND is_deleted = 0

");

$totalStmt->execute([$user_id]);

$totalLimit = $totalStmt->fetchColumn() ?: 0;

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
    <link rel="icon" type="image/x-icon" href="../logo/logo.png">


    <link rel="stylesheet" href="../style.css">

    <style>

        .container{
            padding:20px;
        }

        .top-box{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
            flex-wrap:wrap;
            gap:15px;
        }

        .total-card{
            background:#1e293b;
            padding:20px;
            border-radius:10px;
            font-size:20px;
            font-weight:bold;
        }

        .form-box{
            background:#1e293b;
            padding:20px;
            border-radius:10px;
            margin-bottom:25px;
        }

        .form-box form{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }

        .form-box input{
            padding:12px;
            border:none;
            border-radius:8px;
            flex:1;
            min-width:220px;
        }

        .form-box button{
            padding:12px 20px;
            border:none;
            border-radius:8px;
            background:#3b82f6;
            color:white;
            cursor:pointer;
        }

        table{
            width:100%;
            border-collapse:collapse;
            background:#1e293b;
            overflow:hidden;
            border-radius:10px;
        }

        th{
            background:#334155;
            padding:15px;
        }

        td{
            padding:14px;
            text-align:center;
            border-bottom:1px solid #334155;
        }

        .btn{
            padding:8px 14px;
            border:none;
            border-radius:6px;
            color:white;
            cursor:pointer;
            text-decoration:none;
            display:inline-block;
            margin:2px;
        }

        .edit-btn{
            background:#22c55e;
        }

        .delete-btn{
            background:#ef4444;
        }

        @media(max-width:768px){

            table{
                display:block;
                overflow-x:auto;
                white-space:nowrap;
            }

            .form-box form{
                flex-direction:column;
            }
        }

    </style>

</head>

<body>

<div class="wrapper">

    <!-- SIDEBAR -->

    <div class="sidebar">

        <a href="../dashboard.php">Dashboard</a>

        <a href="../expense_module/view_expense.php">Expenses</a>

        <a href="../transaction_module/view_transaction.php">Transactions</a>

        <a href="../report/report.php">Reports</a>

        <a href="#">Category</a>

        <a href="../profile/show_profile.php">Profile</a>

        <a href="../login/logout.php">Logout</a>

    </div>

    <!-- MAIN -->

    <div class="main">

        <div class="container">

            <div class="top-box">

                <h1>
                    📂 Category Management
                </h1>

                <div class="total-card">

                    Total Monthly Budget:
                    <?= number_format((float)$totalLimit, 2) ?>

                </div>

            </div>

            <!-- ADD FORM -->

            <div class="form-box">

                <form method="POST">

                    <input
                        type="text"
                        name="name"
                        placeholder="Category Name"
                        required
                    >

                    <input
                        type="number"
                        step="0.01"
                        name="monthly_limit"
                        placeholder="Monthly Limit"
                        required
                    >

                    <button type="submit" name="add">

                        Add Category

                    </button>

                </form>

            </div>

            <!-- CATEGORY TABLE -->

            <table>

                <tr>

                    <th>SN</th>

                    <th>Category</th>

                    <th>Monthly Limit</th>

                    <th>Actions</th>

                </tr>

                <?php if(count($categories) > 0): ?>

                    <?php $sn = 1; ?>

                    <?php foreach($categories as $category): ?>

                    <tr>

                        <td>
                            <?= $sn++ ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($category['category_name']) ?>
                        </td>

                        <td>

                            <?= number_format(
                                (float)$category['monthly_limit'],
                                2
                            ) ?>

                        </td>

                        <td>

                            <a
                                href="edit_category.php?id=<?= $category['category_id'] ?>"
                                class="btn edit-btn"
                            >

                                Edit

                            </a>

                            <a
                                href="?delete=<?= $category['category_id'] ?>"
                                class="btn delete-btn"
                                onclick="return confirm('Delete category?')"
                            >

                                Delete

                            </a>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="4">

                            No categories found

                        </td>

                    </tr>

                <?php endif; ?>

            </table>

        </div>

    </div>

</div>

</body>
</html>