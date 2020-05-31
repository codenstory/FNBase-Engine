<?php
    if(!empty($_POST['slug'])){
        include '../setting.php';
        if(!empty($_SESSION['fnUserId'])){
            require_once '../editor/htmlpurifier/library/HTMLPurifier.auto.php';
            function filt($arg){
                $purifier = new HTMLPurifier();
                $val = $purifier->purify($arg);
                return $val;
            }
            $id = $_SESSION['fnUserId'];
            $name = $_SESSION['fnUserName'];

            $s = filt($_POST['slug']);
            $t = filt($_POST['title']);
            $nt = filt($_POST['nickTitle']);
            $bi = filt($_POST['boardIntro']);

            $sql_ = "SELECT `point` FROM `_account` WHERE `id` = '$id'";
            $result = mysqli_query($conn, $sql_);
            $r = mysqli_fetch_assoc($result);
            if($r['point'] < 5000){
                die('<script>alert("포인트가 부족합니다.");history.back()</script>');
            }else{
                $sql = "UPDATE `_account` SET `point` = `point` - 5000 WHERE `id` = '$id'";
                $res = mysqli_query($conn, $sql);
            }

            $sql = "INSERT INTO `_board` (`id`, `name`, `type`, `slug`, `title`, `nickTitle`, `boardIntro`)
            VALUES ('$id', '$name', 'OWNER_ONLY', '$s', '$t', '$nt', '$bi')";
            $res = mysqli_query($conn, $sql);
            if($res){
                die('<script>location.href = "../b>'.$s.'"</script>');
            }else{
                die($sql);
            }
        }else{
            die('로그인 후 이용 바랍니다.');
        }
    }elseif(!empty($_POST['tsfSlug'])){ #게시판 소유주 조정
        include '../setting.php';
        $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
        $result = mysqli_query($conn, $sql);
        $iA = mysqli_fetch_assoc($result);
        if($iA['isAdmin'] !== '1'){
            exit;
        }

        $iii = $_POST['tsfId'];
        $sss = $_POST['tsfSlug'];

        $sql = "SELECT `name` FROM `_account` WHERE `id` = '$iii'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $nnn = $row['name'];

        $sql = "UPDATE `_board` SET `name` = '$nnn', `id` = '$iii' WHERE `slug` = '$sss'";
        $result = mysqli_query($conn, $sql);
        if($result){
            die('<script>location.href = "../b>'.$sss.'"</script>');
        }else{
            die($sql);
        }
    }else{
        echo '?';
    }
?>