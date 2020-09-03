<?php
include "../setting.php";

if(!$id){
    http_response_code(403);
    exit;
}

function filt($arg){
    $arg = htmlspecialchars($arg);
    require_once '../editor/htmlpurifier/library/HTMLPurifier.auto.php';
    $purifier = new HTMLPurifier();
    $val = $purifier->purify($arg);
    return $val;
}

if($_REQUEST['content']){ #저장
    $c = filt($_REQUEST['content']);
    $sql = "UPDATE `_userSet` SET `tempSave` = '$c' WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    if($result){
        exit;
    }
}else{ #불러오기
    $sql = "SELECT `tempSave` FROM `_userSet` WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $c = $row['tempSave'];
    if($result){
        die($c);
    }
}
?>