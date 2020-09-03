<?php
    include '../setting.php';
    if(empty($_SESSION['fnUserId']) or $_SESSION['fnUserId'] == '0'){
        die('LOGIN REQUIRED');
    }
    $t = htmlspecialchars($_POST['t']);
    $v = preg_replace('/[^0-9]/', '', $_POST['v']);
    if($v < 300){
        exit;
    }
    $i = $_SESSION['fnUserId'];
    $n = $_SESSION['fnUserName'];
    $ip = get_client_ip();

    $sql = "SELECT `name` FROM `_account` WHERE `id` = '$t'";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) !== 1){
        die('<script>alert("없는 아이디입니다.");history.back()</script>');
    }
    $r = mysqli_fetch_assoc($result);
    $r = $r['name'];

    $sql = "SELECT `point` FROM `_account` WHERE `id` = '$i' and `point` < $v";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) == 1){
        die('<script>alert("잔액이 부족합니다.");history.back()</script>');
    }

    $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `value`, `target`, `reason`, `ip`, `isSuccess`)
    VALUES ('$i', '$n', 'POINT_GIVE', '$v', '$t', '$r', '$ip', '1')";
    $result = mysqli_query($conn, $sql);
    if($result){
        $sql = "UPDATE `_account` SET `point` = `point` - '$v' WHERE `id` = '$i'";
        $result = mysqli_query($conn, $sql);
        $sql = "UPDATE `_account` SET `point` = `point` + '$v' WHERE `id` = '$t'";
        $result = mysqli_query($conn, $sql);
        die('<script>history.back()</script>');
    }
?>