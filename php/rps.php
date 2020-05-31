<?php
    include '../setting.php';
    if(empty($_SESSION['fnUserId'])){
        die('LOGIN REQUIRED');
    }

    function rps_num_str($arg){
        switch ($arg) {
            case 1:
                return '가위';
                break;
            case 2:
                return '바위';
                break;
            case 3:
                return '보';
                break;
        }
    }

    $p = rps_num_str(htmlspecialchars($_GET['p']));
    $v = htmlspecialchars($_POST['v']);
    $v = $v * 2;
    $i = $_SESSION['fnUserId'];
    $n = $_SESSION['fnUserName'];
    $ip = get_client_ip();

    $rn = mt_rand(1,3);
    $rn = rps_num_str($rn);
    if($rn == '가위'){
        if($p == '바위'){
            $isWin = TRUE;
        }else{
            $isWin = FALSE;
        }
    }else{
        if($rn == '바위'){
            if($p == '보'){
                $isWin = TRUE;
            }else{
                $isWin = FALSE;
            }
        }elseif($rn == '보'){
            if($p == '가위'){
                $isWin = TRUE;
            }else{
                $isWin = FALSE;
            }
        }
    }

    

    $sql = "SELECT `point` FROM `_account` WHERE `id` = '$i' and `point` < $v";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) == 1){
        die('<script>alert("잔액이 부족합니다.");history.back()</script>');
    }

    if($rn == $p){#무승부
        $v = 0;
        $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `value`, `target`, `reason`, `ip`, `isSuccess`)
        VALUES ('$i', '$n', 'POINT_RPSG', '$v', '$rn', '$p', '$ip', '0')";
        $result = mysqli_query($conn, $sql);
    }elseif($isWin == FALSE){#패
        $sql = "UPDATE `_account` SET `point` = `point` - '$v' WHERE `id` = '$i'";
        $result = mysqli_query($conn, $sql);
        $v = '-'.$v;
        $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `value`, `target`, `reason`, `ip`, `isSuccess`)
        VALUES ('$i', '$n', 'POINT_RPSG', '$v', '$rn', '$p', '$ip', '0')";
        $result = mysqli_query($conn, $sql);
    }else{#승
        $sql = "UPDATE `_account` SET `point` = `point` + '$v' WHERE `id` = '$i'";
        $result = mysqli_query($conn, $sql);
        $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `value`, `target`, `reason`, `ip`, `isSuccess`)
        VALUES ('$i', '$n', 'POINT_RPSG', '$v', '$rn', '$p', '$ip', '1')";
        $result = mysqli_query($conn, $sql);
    }

    if($result){
        die('<script>history.back()</script>');
    }
?>