<?php
require_once 'config.php';

session_start();

/*
|--------------------------------------------------------------------------
| CHECK ADMIN ACCESS
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] !== 'admin'
) {
    die("Access denied");
}

/*
|--------------------------------------------------------------------------
| CHECK USER ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id']) ||
    empty($_GET['id'])
) {
    die("User ID missing");
}

$id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| FILTER VALUES
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

$from_date = $_GET['from_date'] ?? '';

$to_date = $_GET['to_date'] ?? '';

/*
|--------------------------------------------------------------------------
| FETCH USER
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE user_id = ?
");

$stmt->execute([$id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

/*
|--------------------------------------------------------------------------
| TRANSACTION QUERY
|--------------------------------------------------------------------------
*/

$query = "
    SELECT *
    FROM transactions
    WHERE user_id = ?
";

$params = [$id];

/*
|--------------------------------------------------------------------------
| SEARCH FILTER
|--------------------------------------------------------------------------
*/

if (!empty($search)) {

    $query .= "
        AND (
            description LIKE ?
            OR amount LIKE ?
        )
    ";

    $params[] = "%$search%";
    $params[] = "%$search%";
}

/*
|--------------------------------------------------------------------------
| FROM DATE FILTER
|--------------------------------------------------------------------------
*/

if (!empty($from_date)) {

    $query .= "
        AND transaction_date >= ?
    ";

    $params[] = $from_date;
}

/*
|--------------------------------------------------------------------------
| TO DATE FILTER
|--------------------------------------------------------------------------
*/

if (!empty($to_date)) {

    $query .= "
        AND transaction_date <= ?
    ";

    $params[] = $to_date;
}

/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

$query .= "
    ORDER BY transaction_date DESC
";

$transactions = $conn->prepare($query);

$transactions->execute($params);

$data = $transactions->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| FILTERED STATISTICS
|--------------------------------------------------------------------------
*/

$filteredIncome = 0;

$filteredExpense = 0;

$filteredTransactions = count($data);

foreach ($data as $transaction) {

    $amount = (float) $transaction['amount'];

    if ($amount > 0) {

        $filteredIncome += $amount;

    } else {

        $filteredExpense += abs($amount);
    }
}

/*
|--------------------------------------------------------------------------
| FILTERED SAVING
|--------------------------------------------------------------------------
*/

$filteredSaving = $filteredIncome - $filteredExpense;

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

    <link rel="stylesheet" href="user_profile.css">

</head>

<body>

<!-- BACK BUTTON -->

<a href="admin_dashboard.php" class="back-btn">

    ← Back to Dashboard

</a>

<!-- Edit User Button -->

<a href="edit_user.php?id=<?= $id ?>" class="back-btn"
 style="padding: 10px 20px;
  background:#1e293b;
  color: #f5f4f4; 
  text-decoration: none;
   border-radius: 5px; 
   margin: 20px; 
   display: inline-block;">

    Edit User

</a>

<!-- PROFILE -->

<div class="profile-box">

    <h2>

        👤 <?= htmlspecialchars($user['name']) ?> Profile

    </h2>

    <div class="profile-grid">

        <div class="profile-item">

            <strong>Name</strong>

            <span>
                <?= htmlspecialchars($user['name']) ?>
            </span>

        </div>

        <div class="profile-item">

            <strong>Email</strong>

            <span>
                <?= htmlspecialchars($user['email']) ?>
            </span>

        </div>

        <div class="profile-item">

            <strong>Role</strong>

            <span>
                <?= ucfirst(htmlspecialchars($user['role'])) ?>
            </span>

        </div>

        <div class="profile-item">

            <strong>Number</strong>

            <span>
                <?php echo htmlspecialchars($user['phone']) ?>
            </span>

        </div>

        <div class="profile-item">

            <strong>Status</strong>

            <?php if($user['is_active']): ?>

                <span class="status-active">

                    Active

                </span>

            <?php else: ?>

                <span class="status-blocked">

                    Blocked

                </span>

            <?php endif; ?>

        </div>

    </div>

</div>

<!-- FILTER -->

<div class="filter-box">

    <form method="GET" class="filter-form">

        <input
            type="hidden"
            name="id"
            value="<?= $id ?>"
        >

        <input
            type="text"
            name="search"
            placeholder="Search description..."
            value="<?= htmlspecialchars($search) ?>"
        >

        <input
            type="date"
            name="from_date"
            value="<?= htmlspecialchars($from_date) ?>"
        >

        <input
            type="date"
            name="to_date"
            value="<?= htmlspecialchars($to_date) ?>"
        >

        <button type="submit">

            🔍 Filter

        </button>

        <a
            href="user_profile.php?id=<?= $id ?>"
            class="clear-btn"
        >

            Clear

        </a>

    </form>

</div>

<!-- CARDS -->

<div class="cards">

    <div class="card">

        <h3>Filtered Income</h3>

        <p class="income-text">

            $<?= number_format($filteredIncome, 2) ?>

        </p>

    </div>

    <div class="card">

        <h3>Filtered Expense</h3>

        <p class="expense-text">

            $<?= number_format($filteredExpense, 2) ?>

        </p>

    </div>

    <div class="card">

        <h3>Filtered Saving</h3>

        <p class="<?= $filteredSaving >= 0 ? 'income-text' : 'expense-text' ?>">

            $<?= number_format($filteredSaving, 2) ?>

        </p>

    </div>

    <div class="card">

        <h3>Total Transactions</h3>

        <p>

            <?= $filteredTransactions ?>

        </p>

    </div>

</div>

<!-- TABLE -->

<h3 class="table-title">

    💰 User Transactions

</h3>

<div class="table-wrapper">

<table>

    <tr>

        <th>Date</th>

        <th>Description</th>

        <th>Amount</th>

    </tr>

    <?php if(count($data) > 0): ?>

        <?php foreach($data as $t): ?>

        <tr>

            <td>

                <?= htmlspecialchars($t['transaction_date']) ?>

            </td>

            <td>

                <?= htmlspecialchars($t['description']) ?>

            </td>

            <td class="<?= $t['amount'] >= 0 ? 'income-text' : 'expense-text' ?>">

                $<?= number_format($t['amount'],2) ?>

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

</body>
</html>