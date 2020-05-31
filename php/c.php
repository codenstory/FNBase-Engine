<?php
    require '../setting.php';
        $uA = $_SERVER['HTTP_USER_AGENT'];
        $ip = get_client_ip();
        $id = $_SESSION['fnUserId'];
        $sql = "UPDATE `_account` SET `lastIp` = '$ip', `userAgent` = '$uA' WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
            if($result){
                die('<script>window.location.href = document.referrer;</script>');
            }
?>