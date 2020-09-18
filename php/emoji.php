<?php
if(empty($_GET) and $_GET != '0'){
    exit;
}

require '../setting.php';
$id = $_SESSION['fnUserId'];
$f = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['f']);
if(empty($id) and $id != '0'){
    die('로그인 필요');
}

$sql = "SELECT `cost`, `id` FROM `_fnbcon` WHERE `folder` = '$f'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$a_id = $row['id'];
$cost = $row['cost'];

if(!empty($f) and $f != '0'){ #사용 / 미사용
    $sql = "SELECT `fnbcon` FROM `_userSet` WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if(preg_match('/(^|,)'.$f.'($|,)/m', $row['fnbcon'])){ #사용안함
        $e = $row['fnbcon'];
        $e = preg_replace('/(^|,)'.$f.'($|,)/', ',', $e);
        $e = preg_replace('/^,/', '', $e);
        $e = preg_replace('/,$/', '', $e);
        $sql = "UPDATE `_userSet` SET `fnbcon` = '$e' WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        $sql = "UPDATE `_fnbcon` SET `use` = `use` - 1 WHERE `folder` = '$f'";
        $result = mysqli_query($conn, $sql);
    }else{ #사용하기
        if($cost !== 0){
            $sql_ = "SELECT `point` FROM `_account` WHERE `id` = '$id'";
            $result = mysqli_query($conn, $sql_);
            $r = mysqli_fetch_assoc($result);
            if($r['point'] < $cost){
                die('<script>alert("포인트가 부족합니다.");history.back()</script>');
            }else{ //이모티콘 비용 결제
                $sql = "UPDATE `_account` SET `point` = `point` - $cost WHERE `id` = '$id'";
                $result = mysqli_query($conn, $sql); #출금
                $sql = "UPDATE `_account` SET `point` = `point` + $cost WHERE `id` = '$a_id'";
                $result = mysqli_query($conn, $sql); #입금
            }
        }

        if(empty($row['fnbcon']) and $row['fnbcon'] != '0'){
            $e = $f;
        }else{
            $e = $row['fnbcon'].','.$f;
        }
        $e = preg_replace('/^,/', '', $e);
        $e = preg_replace('/,$/', '', $e);

        $sql = "UPDATE `_userSet` SET `fnbcon` = '$e' WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        $sql = "UPDATE `_fnbcon` SET `use` = `use` + 1 WHERE `folder` = '$f'";
        $result = mysqli_query($conn, $sql);
    }
}
die('<script>window.location.href = document.referrer;</script>');
?>