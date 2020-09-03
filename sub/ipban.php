<?php
    require '../setting.php';
    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
    $result = mysqli_query($conn, $sql);
    $target = $_GET['ip'];
    $iA = mysqli_fetch_assoc($result);
        if($iA['isAdmin']){
            $sqls = "SELECT * FROM `_ipban` WHERE `ip` = '$target'";
            $results = mysqli_query($conn, $sqls);
            if(mysqli_num_rows($results) > 0){
                $sql = "DELETE FROM `_ipban` WHERE `ip` = '$target';";
                $result = mysqli_query($conn, $sql);
                $sql = "UPDATE `_content` SET `board` = 'uita' WHERE `ip` = '$target' and `id` = '_anon'";
                $result = mysqli_query($conn, $sql);
                $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `at`, `value`, `target`, `ip`, `isSuccess`)
                VALUES ('$id', '$name', 'AUDIT_LOG', CURRENT_TIMESTAMP(), 'ipban', '$target', '$ip', '0')";
                $result = mysqli_query($conn, $sql);
                die('<script>alert("\"'.$target.'\" - 차단 해제");history.back()</script>');
            }else{
                $sql = "INSERT INTO `_ipban` (`ip`) VALUES ('$target');";
                $result = mysqli_query($conn, $sql);
                $sql = "UPDATE `_content` SET `board` = 'trash' WHERE `ip` = '$target' and `id` = '_anon'";
                $result = mysqli_query($conn, $sql);
                $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `at`, `value`, `target`, `ip`, `isSuccess`)
                VALUES ('$id', '$name', 'AUDIT_LOG', CURRENT_TIMESTAMP(), 'ipban', '$target', '$ip', '1')";
                $result = mysqli_query($conn, $sql);
                die('<script>alert("\"'.$target.'\" - 차단 완료");history.back()</script>');
            }
        }
?>