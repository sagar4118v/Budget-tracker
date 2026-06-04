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


// UPDATE PROFILE
if(isset($_POST['update'])){

    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("
        UPDATE users
        SET 
            name = ?,
            email = ?,
            phone = ?,
            password = ?
        WHERE user_id = ?
    ");

    $stmt->execute([
        $name,
        $email,
        $phone,
        $password,
        $user_id
    ]);

    $success = "Profile updated successfully!";
    //Redirect to show updated info
    header("Location: show_profile.php");
    exit();
}


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

        label{
            display:block;
            margin-bottom:8px;
            margin-top:15px;
        }

        input{
            width:100%;
            padding:12px;
            border:none;
            border-radius:6px;
            box-sizing:border-box;
            margin-top:5px;
        }

        button{
            width:100%;
            padding:12px;
            margin-top:25px;
            background:#3b82f6;
            color:white;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-size:16px;
        }

        button:hover{
            background:#2563eb;
        }

        .success{
            background:#16a34a;
            padding:12px;
            border-radius:6px;
            margin-bottom:20px;
            text-align:center;
        }

    </style>

</head>

<body>

<div class="wrapper">

    <!-- Sidebar -->
    <div class="sidebar">

        <h2>DASHBOARD</h2>

        <a href="../expense_module/view_expense.php">Expenses</a>


        <a href="../dashboard.php">Dashboard</a>

        <a href="../report/report.php">Reports</a>

        <a href="../category/category.php">Category</a>

        <a href="profile_edit.php">Profile</a>

        <a href="../login/logout.php">Logout</a>

    </div>


    <!-- Main Content -->
    <div class="main">

        <div class="profile-box">

            <h1> User Profile</h1>

            <?php if(isset($success)): ?>

                <div class="success">
                    <?= $success ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <!-- NAME -->
                <label>Full Name</label>

                <input
                    type="text"
                    name="name"
                    value="<?= htmlspecialchars($user['name']) ?>"
                    required
                >


                <!-- EMAIL -->
                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($user['email']) ?>"
                    required
                >


                <!-- PHONE -->
                <label>Phone</label>

                <input
                    type="text"
                    name="phone"
                    value="<?= htmlspecialchars($user['phone']) ?>"
                >


                <!-- PASSWORD -->
                <label>Password</label>

                <input
                    type="text"
                    name="password"
                    value="<?= htmlspecialchars($user['password']) ?>"
                    required
                >


                <!-- BUTTON -->
                <button type="submit" name="update">

                    Update Profile

                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>