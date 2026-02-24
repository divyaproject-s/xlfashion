<?php
session_start();
require "config.php";

$action = $_POST['action'] ?? '';

/* ---------- REGISTER ---------- */
if($action == "register"){

    $u = $_POST['username'];
    $e = $_POST['email'];
    $p = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s",$e);
    $check->execute();
    $check->store_result();

    if($check->num_rows > 0){
        $_SESSION['error']="Email already exists";
        header("Location:index.php"); exit();
    }

    $stmt = $conn->prepare("INSERT INTO users(username,email,password) VALUES(?,?,?)");
    $stmt->bind_param("sss",$u,$e,$p);
    $stmt->execute();

    $_SESSION['message']="Registration success. Login now.";
    header("Location:index.php"); exit();
}


/* ---------- LOGIN ---------- */
if($action == "login"){

    $e=$_POST['email'];
    $p=$_POST['password'];

    $stmt=$conn->prepare("SELECT id,password FROM users WHERE email=?");
    $stmt->bind_param("s",$e);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows==1){
        $stmt->bind_result($id,$hash);
        $stmt->fetch();

        if(password_verify($p,$hash)){

            $otp=random_int(100000,999999);
            $exp=date("Y-m-d H:i:s",strtotime("+5 minutes"));

            $u=$conn->prepare("UPDATE users SET otp=?,otp_expiry=? WHERE id=?");
            $u->bind_param("ssi",$otp,$exp,$id);
            $u->execute();

            sendOTP($e,$otp);

            $_SESSION['temp_email']=$e;
            header("Location: otp.php"); exit();
        }
    }

    $_SESSION['error']="Invalid login";
    header("Location:index.php"); exit();
}


/* ---------- VERIFY OTP ---------- */
if($action == "verify"){

    $otp=$_POST['otp'];
    $e=$_SESSION['temp_email'] ?? '';

    $stmt=$conn->prepare("SELECT id,username FROM users WHERE email=? AND otp=? AND otp_expiry>NOW()");
    $stmt->bind_param("ss",$e,$otp);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows==1){
        $stmt->bind_result($id,$name);
        $stmt->fetch();

        $_SESSION['user_id']=$id;
        $_SESSION['username']=$name;
        unset($_SESSION['temp_email']);

        $clear=$conn->prepare("UPDATE users SET otp=NULL,otp_expiry=NULL WHERE id=?");
        $clear->bind_param("i",$id);
        $clear->execute();

        header("Location: dashboard.php"); exit();
    }

    $_SESSION['error']="Invalid OTP";
    header("Location: otp.php"); exit();
}
?>
