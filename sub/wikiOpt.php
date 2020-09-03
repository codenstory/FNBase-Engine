<?php
    require_once '../setting.php';
    require '../func.php';
    if(!$id){
        die('로그인이 필요합니다.');
    }

    if($_POST['topColor']){
        $c = filt($_POST['topColor'], 'abc').','.filt($_POST['seaColor'], 'abc');
        $p = $fnPColor.','.$fnSColor;
        if($c != $p){
            $sql = "UPDATE `_userSet` SET `wikiColor` = '$c' WHERE `id` = '$id'";
            if(!mysqli_query($conn, $sql)){
                die('오류');
            }
        }else{
            $sql = "UPDATE `_userSet` SET `wikiColor` = NULL WHERE `id` = '$id'";
            if(!mysqli_query($conn, $sql)){
                die('오류');
            }
        }
    }
    
    die('<script>location.href = \'/w/\'</script>');
?>