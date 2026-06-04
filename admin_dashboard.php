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
| BLOCK / ACTIVATE USER
|--------------------------------------------------------------------------
*/

if (isset($_GET['toggle'])) {

    $id = (int) $_GET['toggle'];

    $stmt = $conn->prepare("
        UPDATE users
        SET is_active = NOT is_active
        WHERE user_id = ?
    ");

    $stmt->execute([$id]);

    header("Location: admin_dashboard.php");

    exit();
}

/*
|--------------------------------------------------------------------------
| SEARCH & FILTER
|--------------------------------------------------------------------------
*/

$search = $_GET['search'] ?? '';

$roleFilter = $_GET['role'] ?? '';

$sql = "
    SELECT *
    FROM users
    WHERE 1
";

$params = [];

/*
|--------------------------------------------------------------------------
| SEARCH FILTER
|--------------------------------------------------------------------------
*/

if (!empty($search)) {

    $sql .= "
        AND (
            name LIKE ?
            OR email LIKE ?
        )
    ";

    $params[] = "%$search%";
    $params[] = "%$search%";
}

/*
|--------------------------------------------------------------------------
| ROLE FILTER
|--------------------------------------------------------------------------
*/

if (!empty($roleFilter)) {

    $sql .= "
        AND role = ?
    ";

    $params[] = $roleFilter;
}

/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

$sql .= "
    ORDER BY user_id DESC
";

/*
|--------------------------------------------------------------------------
| FETCH USERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($sql);

$stmt->execute($params);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| DASHBOARD STATS
|--------------------------------------------------------------------------
*/

$totalUsers = count($users);

$activeUsers = 0;

$blockedUsers = 0;

$adminCount = 0;

foreach ($users as $u) {

    if ($u['is_active']) {
        $activeUsers++;
    } else {
        $blockedUsers++;
    }

    if ($u['role'] === 'admin') {
        $adminCount++;
    }
}

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
    <link rel="stylesheet" href="admin.css">

    

</head>

<body>

<div class="container">

    <!-- =========================================
         SIDEBAR
    ========================================= -->

    <div class="sidebar">

        <h2>👑 ADMIN</h2>

        <a href="#">Dashboard</a>
        <a href="add_user.php">Add User</a>
        <a href="login/logout.php">Logout</a>

    </div>

    <!-- =========================================
         MAIN CONTENT
    ========================================= -->

    <div class="main-content">

        <!-- =========================================
             TOPBAR
        ========================================= -->

        <div class="topbar">

            <div>

                <h2>

                    👑 Admin Dashboard

                </h2>

                <p>

                    Welcome,
                    <?= htmlspecialchars($_SESSION['name']) ?>

                </p>

            </div>

            <!-- FILTER -->

            <form method="GET">

                <input
                    type="text"
                    name="search"
                    placeholder="Search user..."
                    value="<?= htmlspecialchars($search) ?>"
                >

                <select name="role">

                    <option value="">

                        All Roles

                    </option>

                    <option
                        value="admin"
                        <?= $roleFilter === 'admin' ? 'selected' : '' ?>
                    >

                        Admin

                    </option>

                    <option
                        value="user"
                        <?= $roleFilter === 'user' ? 'selected' : '' ?>
                    >

                        User

                    </option>

                </select>

                <button type="submit">

                    Filter

                </button>

            </form>

        </div>

        <!-- =========================================
             STAT CARDS
        ========================================= -->

        <div class="cards">

            <div class="card blue">

                <h3>Total Users</h3>

                <p>

                    <?= $totalUsers ?>

                </p>

            </div>

            <div class="card green">

                <h3>Active Users</h3>

                <p>

                    <?= $activeUsers ?>

                </p>

            </div>

            <div class="card red">

                <h3>Blocked Users</h3>

                <p>

                    <?= $blockedUsers ?>

                </p>

            </div>

            <div class="card yellow">

                <h3>Admins</h3>

                <p>

                    <?= $adminCount ?>

                </p>

            </div>

        </div>

        <!-- =========================================
             USERS TABLE
        ========================================= -->

        <h3 style="margin-bottom:15px;">

            👥 All Users

        </h3>

        <table>

            <tr>

                <th>S.N</th>

                <th>Name</th>

                <th>Email</th>

                <th>Number</th>

                <th>Role</th>

                <th>Status</th>

                <th>Actions</th>

            </tr>

            <?php $i = 1; ?>

            <?php foreach($users as $u): ?>

            <tr>

                <td>

                    <?= $i++ ?>

                </td>

                <td>

                    <a
                        class="user-link"
                        href="user_profile.php?id=<?= $u['user_id'] ?>"
                    >

                        <?= htmlspecialchars($u['name']) ?>

                    </a>

                </td>

                <td>

                    <?= htmlspecialchars($u['email']) ?>

                </td>

                <td>

                    <?= htmlspecialchars($u['phone']) ?>

                </td>

                <td>

                    <?= ucfirst(htmlspecialchars($u['role'])) ?>

                </td>

                <td>

                    <?php if($u['is_active']): ?>

                        <span class="status-active">

                            Active

                        </span>

                    <?php else: ?>

                        <span class="status-blocked">

                            Blocked

                        </span>

                    <?php endif; ?>

                </td>

                <td>

                    <!-- VIEW -->

                    <a
                        class="btn view-btn"
                        href="user_profile.php?id=<?= $u['user_id'] ?>"
                    >

                        View

                    </a>

                    <!-- BLOCK / ACTIVATE -->

                    <a
                        class="btn <?= $u['is_active'] ? 'block-btn' : 'active-btn' ?>"
                        href="?toggle=<?= $u['user_id'] ?>"
                    >

                        <?= $u['is_active'] ? 'Block' : 'Activate' ?>

                    </a>

                    <!-- DELETE -->
                <a class="btn block-btn"
                    href="delete_user.php?id=<?= $u['user_id'] ?>"
                    onclick="return confirm('Delete this user?')">
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