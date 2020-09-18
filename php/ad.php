<?php
    if(!empty($_POST['ad']) and $_POST['ad'] != '0'){
        session_start();
        if(!empty($_SESSION['fnUserId']) and $_SESSION['fnUserId'] != '0'){
            require_once '../setting.php';
            require_once '../editor/htmlpurifier/library/HTMLPurifier.auto.php';

            $sql_ = "SELECT COUNT(`ad`) as `cnt` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 1 DAY) and `id` = '$id'";
            $result = mysqli_query($conn, $sql_);
            $r = mysqli_fetch_assoc($result);
            if($r['cnt'] > 3){
                die('<script>alert("광고는 하루에 3개만 등록 가능합니다.");history.back()</script>');
            }

            function filt($arg, $opt){
                $arg = str_ireplace('>', '%3E', $arg);
                $arg = str_ireplace('"%3E', '">', $arg);
                $arg = str_ireplace("'", '\'', $arg);
                $purifier = new HTMLPurifier();
                $val = $purifier->purify($arg);
                return $val;
            }
            $id = $_SESSION['fnUserId'];
            $name = $_SESSION['fnUserName'];
            $ad = filt($_POST['ad'], 'oth');
            $link = filt($_POST['link'], 'oth');
            if(mb_strlen($ad) > 100){
                $ad = substr($ad, 0, 98).'..';
            }

            $sql_ = "SELECT `point` FROM `_account` WHERE `id` = '$id'";
            $result = mysqli_query($conn, $sql_);
            $r = mysqli_fetch_assoc($result);
            if($r['point'] < 5000){
                die('<script>alert("포인트가 부족합니다.");history.back()</script>');
            }else{
                $sql = "UPDATE `_account` SET `point` = `point` - 5000 WHERE `id` = '$id'";
                $res = mysqli_query($conn, $sql);
            }

            if($_POST['type'] == 'PUB'){
                $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
                $result = mysqli_query($conn, $sql);
                $iA = mysqli_fetch_assoc($result);
                    if($iA['isAdmin']){
                        $type = 'PUB_S_ADVT';
                    }
            }else{
                $type = 'USER_ADVER';
            }

            $sql = "INSERT INTO `_ad` (`id`, `name`, `type`, `at`, `ad`, `link`) VALUES ('$id', '$name', '$type', CURRENT_TIMESTAMP, '$ad', '$link')";
            $res = mysqli_query($conn, $sql);
            if($res){
                die('<script>location.href = "../"</script>');
            }else{
                die($sql);
            }
        }else{
            die('로그인 후 이용 바랍니다.');
        }
    }

    $yn = mt_rand(0,3);
    if($yn > 0){
        $sql = "SELECT COUNT(*) as `cnt` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 3 DAY) and `type` = 'USER_ADVER'";
        $res = mysqli_query($conn, $sql);
        $res = mysqli_fetch_assoc($res);
        $cnt = $res['cnt'] - 1;
        $n = mt_rand(0, $cnt);

        if($cnt == -1){
            echo '광고가 없습니다..';
        }else{
            $sql = "SELECT `ad`, `link` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 3 DAY) and `type` = 'USER_ADVER' and `ad` IS NOT NULL ORDER BY `at` DESC LIMIT $n, 1";
            $res = mysqli_query($conn, $sql);
            $res = mysqli_fetch_assoc($res);

            if($res['link'] == NULL){
                echo '<span id="fnbAd">'.$res['ad'].'</span>';
            }else{
                echo '<a id="fnbAd" href="'.$res['link'].'">'.$res['ad'].'</a>';
            }
        }
    }else{
        $sql = "SELECT COUNT(*) as `cnt` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 30 DAY) and `type` = 'PUB_S_ADVT'";
        $res = mysqli_query($conn, $sql);
        $res = mysqli_fetch_assoc($res);
        $cnt = $res['cnt'] - 1;
        $n = mt_rand(0, $cnt);

        $sql = "SELECT `ad`, `link` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 30 DAY) and `type` = 'PUB_S_ADVT' and `ad` IS NOT NULL ORDER BY `at` DESC LIMIT $n, 1";
        $res = mysqli_query($conn, $sql);
        $res = mysqli_fetch_assoc($res);

        if($res['link'] == NULL){
            echo '<span id="fnbAd" class="sponsored">'.$res['ad'].'</span>';
        }else{
            echo '<a id="fnbAd" href="'.$res['link'].'">'.$res['ad'].'</a>';
        }
    }
?>