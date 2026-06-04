<?php

session_start();

require_once '../config.php';

// CHECK LOGIN
if(!isset($_SESSION['user_id'])){
    header("Location: ../login/login.php");
    exit();
}

// LOGGED IN USER
$user_id = $_SESSION['user_id'];


// FETCH USER DATA
$stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE user_id = ?
");

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Budget Tracker</title>
    <link rel="icon" type="image/x-icon" href="../logo/logo.png">

    <style>

        body{
            margin:0;
            font-family:Arial;
            background:#0f172a;
            color:white;
        }

        .wrapper{
            display:flex;
        }

        .sidebar{
            width:220px;
            background:#1e293b;
            height:100vh;
            padding:20px;
        }

        .sidebar h2{
            margin-bottom:20px;
        }

        .sidebar a{
            display:block;
            color:#cbd5f5;
            padding:12px;
            text-decoration:none;
            border-radius:6px;
            margin-bottom:5px;
        }

        .sidebar a:hover{
            background:#334155;
        }

        .main{
            flex:1;
            padding:40px;
        }

        .profile-box{
            max-width:500px;
            background:#1e293b;
            padding:30px;
            border-radius:10px;
            margin:auto;
        }

        .profile-box h1{
            margin-top:0;
            margin-bottom:25px;
            text-align:center;
        }

        .profile-item{
            margin-bottom:20px;
            padding:15px;
            background:#334155;
            border-radius:8px;
        }

        .label{
            font-size:14px;
            color:#94a3b8;
            margin-bottom:5px;
        }

        .value{
            font-size:18px;
            font-weight:bold;
        }

        .edit-btn{
            display:block;
            text-align:center;
            background:#3b82f6;
            color:white;
            padding:12px;
            border-radius:6px;
            text-decoration:none;
            margin-top:20px;
        }

        .edit-btn:hover{
            background:#2563eb;
        }

        .btn {
    display: inline-block;
    padding: 10px 225px;
    background: #e53935;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn:hover {
    background: #c62828;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.btn:active {
    transform: scale(0.98);
}

.block-btn {
    width: fit-content;
}

    </style>

</head>

<body>

<div class="wrapper">

    <!-- Sidebar -->
    <div class="sidebar">

        <h2>DASHBOARD</h2>

        <a href="../dashboard.php">Dashboard</a>

        <a href="../expense_module/view_expense.php">Expenses</a>

        <a href="../report/report.php">Reports</a>

        <a href="../category/category.php">Category</a>

        <a href="profile.php">Profile</a>

        <a href="../login/logout.php">Logout</a>

    </div>




    <!-- Main -->
    <div class="main">

        <div class="profile-box">
            
        <h1> User Profile</h1>
        <a class="btn block-btn"
            href="delete_user.php?id=<?= $user['user_id'] ?>"
            onclick="return confirm('Delete this user?')">
            Delete
        </a><br><br>


            <div class="profile-item">

                <div class="label">Full Name</div>

                <div class="value">
                    <?= htmlspecialchars($user['name']) ?>
                </div>

            </div>


            <div class="profile-item">

                <div class="label">Email</div>

                <div class="value">
                    <?= htmlspecialchars($user['email']) ?>
                </div>

            </div>


            <div class="profile-item">

                <div class="label">Phone</div>

                <div class="value">
                    <?= htmlspecialchars($user['phone']) ?>
                </div>

            </div>


            <div class="profile-item">

                <div class="label">Role</div>

                <div class="value">
                    <?= htmlspecialchars($user['role']) ?>
                </div>

            </div>


            <a href="edit_profile.php" class="edit-btn">
                Edit Profile
            </a>
          





        </div>

    </div>

</div>

</body>
</html>