<?php
require_once '../setting.php';

$re = '/![0-9]+/m';
$n = preg_replace($re, '', $_REQUEST['n']);

$ip = get_client_ip();
$id = $_SESSION['fnUserId'];
$name = $_SESSION['fnUserName'];

if(empty($id) or $id == '0'){
    $id = '*ANON';
}
if(empty($name) or $name == '0'){
    $name = '익명';
}

if(!empty($_POST['n']) or $_POST['n'] == '0'){
    if(empty($_GET['mode']) or $_GET['mode'] == '0'){ #글 추천
        $sql = "SELECT `num` FROM `_othFunc` WHERE `ip` = '$ip' and `type` = 'UPVOTE_POS' and `target` = '$n'";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) !== 0){
            die('<script>alert("이미 추천하셨습니다.");history.back()</script>');
        }else{
            $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `target`, `ip`, `isSuccess`)
            VALUES ('$id', '$name', 'UPVOTE_POS', '$n', '$ip', '1')";
            $result = mysqli_query($conn, $sql);
            if($result){
                $sql = "UPDATE `_content` SET `voteCount_Up` = `voteCount_Up` + 1 WHERE `num` = '$n'";
                mysqli_query($conn, $sql);
            }
            die('<script>history.back()</script>');
        }
    }elseif($_GET['mode'] == 'un'){ #글 비추천
        $sql = "SELECT `num` FROM `_othFunc` WHERE `ip` = '$ip' and `type` = 'DOVOTE_POS' and `target` = '$n'";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) !== 0){
            die('<script>alert("이미 추천하셨습니다.");history.back()</script>');
        }else{
            $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `target`, `ip`, `isSuccess`)
            VALUES ('$id', '$name', 'DOVOTE_POS', '$n', '$ip', '1')";
            $result = mysqli_query($conn, $sql);
            if($result){
                $sql = "UPDATE `_content` SET `voteCount_Down` = `voteCount_Down` + 1 WHERE `num` = '$n'";
                mysqli_query($conn, $sql);
            }
            die('<script>history.back()</script>');
        }
    }
}elseif(!empty($_GET['n']) or $_GET['n'] == '0'){
    if(empty($_GET['mode']) or $_GET['mode'] == '0'){ #댓글 추천
        $sql = "SELECT `num` FROM `_othFunc` WHERE `ip` = '$ip' and `type` = 'UPVOTE_CMT' and `target` = '$n'";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) !== 0){
            die('<script>alert("이미 추천하셨습니다.");history.back()</script>');
        }else{
            $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `target`, `ip`, `isSuccess`)
            VALUES ('$id', '$name', 'UPVOTE_CMT', '$n', '$ip', '1')";
            $result = mysqli_query($conn, $sql);
            if($result){
                $sql = "UPDATE `_comment` SET `voteCount_Up` = `voteCount_Up` + 1 WHERE `num` = '$n'";
                mysqli_query($conn, $sql);
            }
            die('<script>history.back()</script>');
        }
    }elseif($_GET['mode'] == 'un'){ #댓글 비추천
        $sql = "SELECT `num` FROM `_othFunc` WHERE `ip` = '$ip' and `type` = 'DOVOTE_CMT' and `target` = '$n'";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) !== 0){
            die('<script>alert("이미 추천하셨습니다.");history.back()</script>');
        }else{
            $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `target`, `ip`, `isSuccess`)
            VALUES ('$id', '$name', 'DOVOTE_CMT', '$n', '$ip', '1')";
            $result = mysqli_query($conn, $sql);
            if($result){
                $sql = "UPDATE `_comment` SET `voteCount_Down` = `voteCount_Down` + 1 WHERE `num` = '$n'";
                mysqli_query($conn, $sql);
            }
            die('<script>history.back()</script>');
        }
    }
}
echo '뭔가 잘못된 듯 합니다..'.var_dump($_REQUEST);