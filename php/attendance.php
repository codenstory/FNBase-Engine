<?php

    include '../setting.php';
    if(empty($_SESSION['fnUserId']) or $_SESSION['fnUserId'] == '0'){
        die('<script>alert("로그인이 반드시 필요한 서비스입니다.");history.back()</script>');
    }

    $randint = mt_rand(1, 30);
    if($randint == 1){
        $pt = mt_rand(100, 5000);
    }elseif($randint < 3){
        $pt = mt_rand(100, 3000);
    }elseif($randint < 7){
        $pt = mt_rand(100, 500);
    }else{
        $pt = '100';
    }

    $id = $_SESSION['fnUserId'];
    $name = $_SESSION['fnUserName'];
    $ip = get_client_ip();

    $sql = "SELECT * FROM `_othFunc` WHERE `at` > curdate() and `type` = 'ATTENDANCE' and `id` = '$id'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result)){
        die('<script>alert("이미 출석하셨습니다!");history.back()</script>');
    }

    $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `value`, `ip`, `isSuccess`)
    VALUES ('$id', '$name', 'ATTENDANCE', '$pt', '$ip', '1')";
    $result = mysqli_query($conn, $sql);
    if($result){
        $sql = "UPDATE `_account` SET `point` = `point` + $pt WHERE `id` = '$id';";
        $result = mysqli_query($conn, $sql);
    }

    die('<script>location.href = document.referrer;</script>');

?>