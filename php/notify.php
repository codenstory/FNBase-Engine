<?php
include '../setting.php';

$sql = "UPDATE `_ment` SET `isSuccess` = 1 WHERE `target` = '".$_SESSION['fnUserId']."'";
$result = mysqli_query($conn, $sql);
if($result){
    die('<script>history.back()</script>');
}
?>