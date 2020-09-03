<?php
    include '../setting.php';
    $t = htmlspecialchars($_POST['t']);
    $c = htmlspecialchars($_POST['c']);
    $i = $_SESSION['fnUserId'];
    $n = $_SESSION['fnUserName'];
    $ip = get_client_ip();

    $sql = "SELECT `siteBan` FROM `_account` WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    $sB = mysqli_fetch_assoc($result);
    if($sB['siteBan'] <= 0){
        die('차단당하지 않았습니다!');
    }elseif($sB['siteBan'] > 1){
        die('차단 소명이 불가능한 이용자이십니다.');
    }

    $sql = "SELECT `id` FROM `_othFunc` WHERE `id` = '$i' and `type` = 'VINDICATE'";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) > 2){
        die('소명은 2회만 가능');
    }

    $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `value`, `reason`, `ip`, `isSuccess`)
    VALUES ('$i', '$n', 'VINDICATE_', '$t', '$c', '$ip', '0')";
    $result = mysqli_query($conn, $sql);
?>