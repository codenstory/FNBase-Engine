<?php
include '../setting.php';

$n = preg_replace('/[^0-9]/m', '', $_GET['n']);

if($_GET['o'] === '0'){
    $o = 1;
}else{
    $o = 0;
}

$sql = "UPDATE `_content` SET `offNotify` = $o WHERE `num` = '$n' and `id` = '$id'";
$result = mysqli_query($conn, $sql);
if($result){
    die('<script>history.back()</script>');
}else{
    die('자신의 게시글이 아닙니다.');
}
?>