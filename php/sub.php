<?php
    include '../setting.php';
    $b = preg_replace('/[^a-zA-Z0-9_]*/', '', $_POST['board']);
    $i = $_SESSION['fnUserId'];

    $sql = "SELECT `subs` FROM `_userSet` WHERE `id` = '$i'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if(preg_match('/'.$b.'/', $row['subs'])){ #구독취소
        $s = $row['subs'];
        $s1 = str_ireplace($b, '', $s);
        $s2 = preg_replace('/[,]{2,}/m', ',', $s1);
        $s3 = preg_replace('/^\s*,/', '', $s2);
        $s = preg_replace('/,\s*$/', '', $s3);
        $sql = "UPDATE `_userSet` SET `subs` = '$s' WHERE `id` = '$i'";
        $result = mysqli_query($conn, $sql);
        $sqlb = "UPDATE `_board` SET `subs` = `subs` - 1 WHERE `slug` = '$b'";
        $result = mysqli_query($conn, $sqlb);
    }else{ #구독하기
        if(empty($row['subs'])){
            $s = $b;
        }else{
            $s = $row['subs'].','.$b;
        }
        $s1 = preg_replace('/[,]{2,}/m', ',', $s);
        $s2 = preg_replace('/^\s*,/', '', $s1);
        $s = preg_replace('/,\s*$/', '', $s2);
        $sql = "UPDATE `_userSet` SET `subs` = '$s' WHERE `id` = '$i'";
        $result = mysqli_query($conn, $sql);
        $sqlb = "UPDATE `_board` SET `subs` = `subs` + 1 WHERE `slug` = '$b'";
        $result = mysqli_query($conn, $sqlb);
    }
    die('<script>history.back()</script>');
?>