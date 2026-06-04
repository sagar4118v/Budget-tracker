
<?php


require_once 'config.php';

session_start();

$success = '';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);

    $email = trim($_POST['email']);

    $phone = trim($_POST['phone']);

    $password = trim($_POST['password']);

    $role = $_POST['role'];

    /*
    |-----------------------------------------
    | CHECK EMAIL EXISTS
    |-----------------------------------------
    */

    $check = $conn->prepare("
        SELECT user_id 
        FROM users 
        WHERE email = ?
    ");

    $check->execute([$email]);

    if ($check->rowCount() > 0) {

        $error = "Email already exists!";

    } else {

       

        /*
        |-----------------------------------------
        | INSERT USER
        |-----------------------------------------
        */

        $stmt = $conn->prepare("
            INSERT INTO users
            (
                name,
                email,
                phone,
                password,
                role,
                is_active
            )

            VALUES
            (
                ?, ?, ?, ?, ?, 1
            )
        ");

        $stmt->execute([

            $name,
            $email,
            $phone,
            $password,
            $role
        ]);

        $_SESSION['success'] =
            "Account created successfully! Please login.";

        header("Location: admin_dashboard.php");

        exit();
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Budget Tracker</title>
    <link rel="icon" type="image/x-icon" href="logo/logo.png">

    <style>

        body{
            margin:0;
            font-family:Arial;
            background:#0f172a;
        }

        .register-box{
            width:400px;
            margin:60px auto;
            background:#1e293b;
            padding:30px;
            border-radius:10px;
            color:white;
        }

        h2{
            text-align:center;
            margin-bottom:25px;
        }

        input,
        select{

            width:100%;

            padding:12px;

            margin-top:10px;

            margin-bottom:20px;

            border:none;

            border-radius:6px;

            box-sizing:border-box;
        }

        button{

            width:100%;

            padding:12px;

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

            padding:10px;

            border-radius:6px;

            margin-bottom:15px;

            text-align:center;
        }

        .error{

            background:#dc2626;

            padding:10px;

            border-radius:6px;

            margin-bottom:15px;

            text-align:center;
        }

        a{

            color:#93c5fd;

            text-decoration:none;
        }
        /* BACK BUTTON */


.back-btn{
    display:block;
    width:200px;
    padding:12px 18px;
    background:#1e293b;
    color:white;
    text-decoration:none;
    border-radius:8px;
    margin:20px auto;
    text-align:center;
}

.back-btn:hover{
    background:#334155;
}



    </style>

</head>

<body><br><br>
    <!-- BACK BUTTON -->

<a href="admin_dashboard.php" class="back-btn">

    ← Back to Dashboard

</a>
    
    

<div class="register-box">

    <h2>Create Account</h2>

    <?php if($success): ?>

        <div class="success">

            <?= htmlspecialchars($success) ?>

        </div>

    <?php endif; ?>

    <?php if($error): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>

    <form method="POST">

        <input
            type="text"
            name="name"
            placeholder="Full Name"
            required
        >

        <input
            type="email"
            name="email"
            placeholder="Email"
            required
        >

        <input
            type="text"
            name="phone"
            placeholder="Phone Number"
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <!-- ROLE OPTION -->

        <select name="role" required>

            <option value="user">

                User

            </option>

            <option value="admin">

                Admin

            </option>

        </select>

        <button type="submit">

            Register

        </button>

    </form>

    <br>

    <center>

        <a href="login.php">

            Already have an account? Login

        </a>

    </center>

</div>

</body>
</html>

