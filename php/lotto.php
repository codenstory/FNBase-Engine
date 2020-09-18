<?php

    include '../setting.php';
    if(empty($_SESSION['fnUserId']) and $_SESSION['fnUserId'] != '0'){
        die('<script>alert("로그인이 반드시 필요한 서비스입니다.");history.back()</script>');
    }

    $randint = mt_rand(1, 100);
    if($randint <= 3){
        $ri = mt_rand(1, 100);
            if($ri == 1){
                $pt = 500000;
                $txt = '1등 당첨! 당첨금은 500,000ⓟ!';
            }elseif($ri <= 5){
                $pt = mt_rand(100000, 500000);
                $txt = '2등 당첨! 축하드립니다.';
            }else{
                $pt = mt_rand(50000, 100000);
                $txt = '3등 당첨! 축하드립니다.';
            }
    }elseif($randint <= 10){
        $pt = mt_rand(20000, 50000);
        $txt = '4등 당첨!';
    }elseif($randint <= 50){
        $pt = 1000;
        $txt = '본전! 다음기회에.';
    }else{
        $pt = 0;
        $txt = '꽝! 다음기회에.';
    }

    $i = $_SESSION['fnUserId'];
    $sql = "SELECT `point` FROM `_account` WHERE `id` = '$i' and `point` < 999";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) == 1){
        die('<script>alert("잔액이 부족합니다.");history.back()</script>');
    }

    $id = $i;
    $name = $_SESSION['fnUserName'];
    $ip = get_client_ip();

    $sql = "SELECT * FROM `_othFunc` WHERE `at` > curdate() and `type` = 'READYSHOOT' and `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result)){
        die('<script>alert("이미 한번 구매하셨습니다.");history.back()</script>');
    }

    $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `value`, `reason`, `ip`, `isSuccess`)
    VALUES ('$id', '$name', 'READYSHOOT', '$pt', '$txt', '$ip', '1')";
    $result = mysqli_query($conn, $sql);
    if($result){
        $sql = "UPDATE `_account` SET `point` = `point` - 1000 WHERE `id` = '$id';";
        $result = mysqli_query($conn, $sql);
        $sql = "UPDATE `_account` SET `point` = `point` + $pt WHERE `id` = '$id';";
        $result = mysqli_query($conn, $sql);
    }

    die('<script>location.href = document.referrer;</script>');

?>