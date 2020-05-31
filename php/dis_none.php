<?php
    include '../setting.php';
    $t = preg_replace('/[^a-zA-Z0-9]*/', '', $_POST['i']);
    $m = $_SESSION['fnUserId'];

    $sql = "SELECT `display_none` FROM `_userSet` WHERE `id` = '$m'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if(mb_strpos($row['display_none'], $t) === FALSE){ #차단
        if(empty($row['display_none'])){
            $s = "'$t'";
        }else{
            $s = $row['display_none'].",'$t'";
        }
        $s = preg_replace('/[,]{2,}/m', ',', $s);
        $s = preg_replace('/^,/', '', $s);
        $s = preg_replace('/,$/', '', $s);
        $s = preg_replace("/''/", '', $s);
        $s = str_ireplace(' ', '', $s);

        $sql = "UPDATE `_userSet` SET `display_none` = \"$s\" WHERE `id` = '$m'";
    }else{ #차단 취소
        $s = $row['display_none'];
        $s = str_ireplace("'$t'", '', $s);
        $s = preg_replace('/[,]{2,}/m', ',', $s);
        $s = preg_replace('/^,/', '', $s);
        $s = preg_replace("/''/", '', $s);
        $s = preg_replace('/,$/', '', $s);
        $s = str_ireplace(' ', '', $s);

        $sql = "UPDATE `_userSet` SET `display_none` = \"$s\" WHERE `id` = '$m'";
    }
    $result = mysqli_query($conn, $sql);
    die('<script>history.back()</script>');
?>