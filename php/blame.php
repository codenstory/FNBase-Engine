<?php
    include '../setting.php';
    $bn = htmlspecialchars($_POST['blameNum']);
    $pn = htmlspecialchars($_POST['blamePageNum']);
    $jn = htmlspecialchars($_POST['n']);

    if(empty($pn)){
        $pn = $jn;
        $ic = 'content';
    }else{
        $jn = $bn;
        $ic = 'comment';
    }

    $i = $_SESSION['fnUserId'];
    $n = $_SESSION['fnUserName'];
    $ip = get_client_ip();

    if(empty($i)){
        $i = '*ANON';
        $n = '익명';
    }

    $sql = "SELECT `num` FROM `_othFunc` WHERE `ip` = '$ip' and `type` = 'BLAME_MENT' and `value` = '$jn' and `reason` = '$ic'";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) > 0){
            die('<script>alert("이미 신고하셨습니다.");history.back()</script>');
        }

    $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `value`, `reason`, `ip`, `isSuccess`)
    VALUES ('$i', '$n', 'BLAME_MENT', '$jn', '$ic', '$ip', '0')";
    $result = mysqli_query($conn, $sql);

    $sql = "UPDATE `_$ic` SET `blameCount` = `blameCount` + 1 WHERE `num` = '$jn'";
    $result = mysqli_query($conn, $sql);
    $sqls = $sql;

    $sql = "SELECT `num` FROM `_othFunc` WHERE `type` = 'BLAME_MENT' and `value` = '$jn' and `reason` = '$ic'";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) >= 5){
        $link = 'b>recent>'.$jn;
        $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `reason`, `ip`, `isSuccess`)
        VALUES ('__AUTO', '5인 이상의 신고 접수', 'NOFI_MENTN', '$link', 'admin', '$ic', '127.0.0.1', '0')";
        $result = mysqli_query($conn, $sql);
    }
    die('<script>history.back()</script>');
?>