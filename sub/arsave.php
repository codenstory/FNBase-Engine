<?php
include '../setting.php';
$idS = substr($_SESSION['fnUserId'], 0, 4).'*';

$c = htmlspecialchars($_POST['comm']);
$t = htmlspecialchars($_POST['title']);
$f = htmlspecialchars($_POST['from']);
$a = preg_replace('/[^ㄱ-ㅎ가-힣0-9a-zA-Z]/', '', $_POST['name']).' ('.$idS.')';
$n = preg_replace('/[^0-9]/', '', $_POST['num']);
$b = preg_replace('/[^a-zA-Z]/', '', strtolower($_POST['board']));
if(empty($n) or $n == '0'){
    exit;
}
if(empty($t) or $t == '0'){
    exit;
}

$sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
$result = mysqli_query($conn, $sql);
$iA = mysqli_fetch_assoc($result);
    if($iA['isAdmin']){
        //중복시 UPDATE
        $sql = "SELECT `num` FROM `아카이브` WHERE `no` = $n AND `board` LIKE '$b'";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) > 0){
            $sql = "UPDATE `아카이브` SET `title` = '$t', `name` = '$a', `at` = CURRENT_DATE(), `comment` = '$c' WHERE `no` = $n and `board` like '$b'";
            $result = mysqli_query($conn, $sql);
            die('<script>history.back()</script>');
        }

        //중복 안 됨 = 신규 등록
        $sql = "INSERT INTO `아카이브` (`no`, `board`, `from`, `title`, `name`, `at`, `comment`) VALUES ('$n', '$b', '$f', '$t', '$a', CURRENT_DATE(), '$c')";
        $result = mysqli_query($conn, $sql);
        die('<script>history.back()</script>');
    }
?>