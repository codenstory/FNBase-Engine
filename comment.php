<?php
require_once './setting.php';
require_once './func.php';

$id = $_SESSION['fnUserId'];
$name = $_SESSION['fnUserName'];
$mail = $_SESSION['fnUserMail'];

if($id == NULL){
    die('<script>alert("로그인이 필요합니다.");history.back()</script>');
}
/*if($id == NULL){
    $id = '_ANON';
    $name = '익명_'.GenStr(5);
    $mail = 'anon@fnbase.xyz';
}*/

$mode = filt($_GET['m'], 'abc');

$c = filt($_POST['c'], 'oth');
$n = filt($_REQUEST['n'], '123');
$r = filt($_POST['reply'], 'htm');
$t = filt($_POST['t'], 'oth');
$v = filt($_POST['v'], 'htm');
$v = str_ireplace('&gt;', '>', $v);
$i = filt($_POST['i'], 'oth');

$cO = filt($_POST['childOf'], '123');
$f = filt($_REQUEST['parentNum'], '123');
$now = date('Y-m-d H:i:s');
$ip = get_client_ip();

if($_POST['fnbcon'] == 'FNBCON_CMT'){
    $type = 'FNBCON_CMT';
}else{
    $type = 'COMMON_CMT';
}

if(preg_match('/(discord\.gg|open\.kakao\.com)/m', $c)){
    $sql = "UPDATE `_account` SET `siteBan` = '1' WHERE `id` = '$id';";
    $result = mysqli_query($conn, $sql);
    exit;
}elseif(preg_match('/(discord\.gg|open\.kakao\.com)/m', $r)){
    $sql = "UPDATE `_account` SET `siteBan` = '1' WHERE `id` = '$id';";
    $result = mysqli_query($conn, $sql);
    exit;
}

switch($mode){
    case '': #댓글 작성
        
        //없는 글 댓글 방지
        $sql = "SELECT `num` FROM `_content` WHERE `num` = '$n'";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) == 0){
            die('해당 게시글이 존재하지 않습니다.');
        }

        if($c == ''){
            die('<script>alert("내용이 비어있습니다.");history.back()</script>');
        }elseif(strlen($c) > 1000){
            die('<script>alert("내용이 1000자를 초과했습니다.");history.back()</script>');
        }

        $sql = "SELECT Count(*) as `cnt` FROM `_comment` WHERE `id` = '$id' and `at` > DATE_SUB(NOW(), INTERVAL 2 SECOND)";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if($row['cnt'] >= 1){
            die('<script>history.back()</script>');
        }

        $sql = "SELECT Count(*) as `cnt` FROM `_comment` WHERE `id` = '$id' and `at` > DATE_SUB(NOW(), INTERVAL 20 SECOND)";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if($row['cnt'] >= 6){
            $link = 'b>recent>'.$n;
            $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `reason`, `ip`, `isSuccess`)
            VALUES ('__AUTO', '시스템 경고', 'NOFI_MENTN', '$link', 'admin', '댓글 도배', '127.0.0.1', '0')";
            $result = mysqli_query($conn, $sql);
            $sql = "SELECT Count(*) as `cnt` FROM `_comment` WHERE `id` = '$id' and `at` > DATE_SUB(NOW(), INTERVAL 60 SECOND)";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if($row['cnt'] >= 10){
                $sql = "UPDATE `_account` SET `siteBan` = '1' WHERE `id` = '$id';";
                $result = mysqli_query($conn, $sql);
                die('<script>alert("귀하께서는 도배로 인하여 광역차단 되셨습니다.");location.href = \'./\'</script>');
            }else{
                die('<script>alert("댓글 작성 빈도가 너무 짧습니다.");history.back()</script>');
            }
        }
        
        $sql = "INSERT INTO `_comment` (`id`, `name`, `type`, `at`, `content`, `from`, `mail`)
        VALUES ('$id', '$name', '$type', '$now', '$c', '$n', '$mail')";
        $result = mysqli_query($conn, $sql);
        if(!$result){
            die($sql);
        }
        $sql = "UPDATE `_content` SET `commentCount` = `commentCount` + 1 WHERE `num` = $n ";
        $result = mysqli_query($conn, $sql);

        $sql = "UPDATE `_account` SET `point` = `point` + 5 WHERE `id` = '$id';";
        $result = mysqli_query($conn, $sql);
        if(!$result){
            die($sql);
        }

        $sql = "SELECT `num` FROM `_comment` WHERE `at` = '$now' and `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        $cn = mysqli_fetch_assoc($result);
        $cn = $cn['num'];

        $sql = "SELECT `offNotify` FROM `_content` WHERE `num` = '$n'";
        $result = mysqli_query($conn, $sql);
        $on = mysqli_fetch_assoc($result);
        $on = $on['offNotify'];

        if($on == 0){
            if($id !== $i){
                $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `cmt_id`, `reason`, `ip`, `isSuccess`)
                VALUES ('$id', '$name', 'NOFI_CMMNT', '$v', '$i', '#cmt-".$cn."', '$t', '$ip', '0')";
                $result = mysqli_query($conn, $sql);
            }
        }

        //호출 처리
        preg_match_all('/@[^\s\n<>]+/', $c, $out_arr);
        $i = 0;
        foreach( $out_arr['0'] as $value ){
            $mnt_name = str_replace('@', '', $value);
            $sql = "SELECT `id` from `_account` WHERE `name` = '$mnt_name'";
            $result = mysqli_query($conn, $sql);
            $mnt_id = mysqli_fetch_assoc($result);
            if(mysqli_num_rows($result) == 1){
                $c = preg_replace('/'.$value.'/', '<a href="./u>'.$mnt_id['id'].'">'.$value.'</a>', $c); #내용에서 변경
                $mid = $mnt_id['id'];
                if($id !== $mid){ #호출 반영
                    $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `cmt_id`, `reason`, `ip`, `isSuccess`)
                    VALUES ('$id', '$name', 'NOFI_MENTN', '$v', '$mid', '#cmt-".$cn."',  '$t', '$ip', '0')";
                    $result = mysqli_query($conn, $sql);
                }

                $i++;
                if($i > 20){ #안전장치
                    break;
                }
            }
        }
        $sql = "UPDATE `_comment` SET `content` = '$c' WHERE `num` = '$cn'"; #멘션으로 변경된 내용 반영
        $result = mysqli_query($conn, $sql);

        echo '<script>window.location.href = "./'.$v.'#cmt-'.$cn.'"</script>';
        break;

    case 'edit': #댓글 수정
        if($r == ''){
            if($c == ''){
                die('<script>alert("내용이 비어있습니다.");history.back()</script>');
            }
        }
        if(strlen($r) > 1000){
            die('<script>alert("내용이 1000자를 초과했습니다.");history.back()</script>');
        }elseif(strlen($c) > 1000){
            die('<script>alert("내용이 1000자를 초과했습니다.");history.back()</script>');
        }

        //호출 처리
        preg_match_all('/@[^\s\n<>]+/', $r, $out_arr);
        $i = 0;
        foreach( $out_arr['0'] as $value ){
            $mnt_name = str_replace('@', '', $value);
            $sql = "SELECT `id` from `_account` WHERE `name` = '$mnt_name'";
            $result = mysqli_query($conn, $sql);
            $mnt_id = mysqli_fetch_assoc($result);
            if(mysqli_num_rows($result) == 1){
                $r = preg_replace('/'.$value.'/', '<a href="./u>'.$mnt_id['id'].'">'.$value.'</a>', $r); #내용에서 변경
                $mid = $mnt_id['id'];
                if($id !== $mid){ #호출 반영
                    $f = filt($_POST['fn'], '123');
                    $sql = "SELECT `board`, `title` from `_content` WHERE `num` = '$f'";
                    $result = mysqli_query($conn, $sql);
                    $mn = mysqli_fetch_assoc($result);

                    $b = $mn['board'];
                    $t = $mn['title'];

                    $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `cmt_id`, `reason`, `ip`, `isSuccess`)
                    VALUES ('$id', '$name', 'NOFI_MENTN', 'b>$b>$f', '$mid', '', '$t', '$ip', '0')";
                    $result = mysqli_query($conn, $sql);
                }

                $i++;
                if($i > 20){ #안전장치
                    break;
                }
            }
        }

        $sql = "UPDATE `_comment` SET `content` = '$r', `isEdited` = '$now', `whoEdited` = '$name' WHERE `num` = $n and
        `type` in ('COMMON_CMT','COMMON_REP') and `id` = \"".$_SESSION['fnUserId'].'"';
        $result = mysqli_query($conn, $sql);
        if(!$result){
            die($sql);
        }

        echo '<script>window.location.href = document.referrer;</script>';
        break;
    case 'delete':
        $sql = "SELECT `id` FROM `_comment` WHERE `num` = '$n' and `type` = 'FNBCON_CMT'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);

        $sql = "UPDATE `_account` SET `point` = `point` - 5 WHERE `id` = '$id';";
        $result = mysqli_query($conn, $sql);
        if(!$result){
            die($sql);
        }

        if($row['id'] == $_SESSION['fnUserId']){
            $sql = "DELETE FROM `_comment` WHERE `num` = '$n' and `type` = 'FNBCON_CMT'";
            $result = mysqli_query($conn, $sql);
            $sqli = "UPDATE `_content` SET `commentCount` = `commentCount` - 1 WHERE `num` = '$f'";
            $result = mysqli_query($conn, $sqli);
            echo '<script>window.location.href = document.referrer;</script>';
            exit;
        }
        break;
    case 'reply': #답글 작성
        if(empty($r)){
            die('<script>alert("내용이 비어있습니다.");history.back()</script>');
        }elseif(strlen($r) > 1000){
            die('<script>alert("내용이 1000자를 초과했습니다.");history.back()</script>');
        }

        //없는 글 댓글 방지
        $sql = "SELECT `num` FROM `_content` WHERE `num` = '$n'";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) == 0){
            die('해당 게시글이 존재하지 않습니다.');
        }

        $sql = "SELECT Count(*) as `cnt` FROM `_comment` WHERE `id` = '$id' and `at` > DATE_SUB(NOW(), INTERVAL 2 SECOND)";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if($row['cnt'] >= 1){
            die('<script>history.back()</script>');
        }
        $sql = "SELECT Count(*) as `cnt` FROM `_comment` WHERE `id` = '$id' and `at` > DATE_SUB(NOW(), INTERVAL 20 SECOND)";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if($row['cnt'] >= 6){
            $link = 'b>recent>'.$n;
            $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `reason`, `ip`, `isSuccess`)
            VALUES ('__AUTO', '시스템 경고', 'NOFI_MENTN', '$link', 'admin', '댓글 도배', '127.0.0.1', '0')";
            $result = mysqli_query($conn, $sql);
            $sql = "SELECT Count(*) as `cnt` FROM `_comment` WHERE `id` = '$id' and `at` > DATE_SUB(NOW(), INTERVAL 60 SECOND)";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if($row['cnt'] >= 10){
                $sql = "UPDATE `_account` SET `siteBan` = '1' WHERE `id` = '$id';";
                $result = mysqli_query($conn, $sql);
                die('<script>alert("귀하께서는 도배로 인하여 광역차단 되셨습니다.");location.href = \'./\'</script>');
            }else{
                die('<script>alert("댓글 작성 빈도가 너무 짧습니다.");history.back()</script>');
            }
        }

        if($_POST['fnbcon']){
            $type = 'FNBCON_REP';
        }else{
            $type = 'COMMON_REP';
        }

        $sql = "INSERT INTO `_comment` (`id`, `name`, `at`, `type`, `content`, `from`, `childOf`, `parentNum`, `mail`)
        VALUES ('$id', '$name', '$now', '$type', '$r', '$n', '$cO', '$f', '$mail')";
        $result = mysqli_query($conn, $sql);
        if(!$result){
            die($sql);
        }
        $sql = "UPDATE `_content` SET `commentCount` = `commentCount` + 1 WHERE `num` = $n ";
        $result = mysqli_query($conn, $sql);

        $sql = "UPDATE `_account` SET `point` = `point` + 3 WHERE `id` = '$id';";
        $result = mysqli_query($conn, $sql);
        if(!$result){
            die($sql);
        }

        $sql = "SELECT `num` FROM `_comment` WHERE `at` = '$now' and `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        $cn = mysqli_fetch_assoc($result);
        $cn = $cn['num'];

        $sql = "SELECT `offNotify` FROM `_content` WHERE `num` = '$n'";
        $result = mysqli_query($conn, $sql);
        $on = mysqli_fetch_assoc($result);
        $on = $on['offNotify'];

        if($on == 0){
            if($id !== $i){
                $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `cmt_id`, `reason`, `ip`, `isSuccess`)
                VALUES ('$id', '$name', 'NOFI_CMMNT', '$v', '$i', '#cmt-".$cn."', '$t', '$ip', '0')";
                $result = mysqli_query($conn, $sql);
            }
        }

        //호출 처리
        preg_match_all('/@[^\s\n<>]+/', $r, $out_arr);
        $i = 0;
        foreach( $out_arr['0'] as $value ){
            $mnt_name = str_replace('@', '', $value);
            $sql = "SELECT `id` from `_account` WHERE `name` = '$mnt_name'";
            $result = mysqli_query($conn, $sql);
            $mnt_id = mysqli_fetch_assoc($result);
            if(mysqli_num_rows($result) == 1){
                $r = preg_replace('/'.$value.'/', '<a href="./u>'.$mnt_id['id'].'">'.$value.'</a>', $r); #내용에서 변경
                $mid = $mnt_id['id'];
                if($id !== $mid){ #호출 반영
                    $sql = "INSERT INTO `_ment` (`id`, `name`, `type`, `value`, `target`, `cmt_id`, `reason`, `ip`, `isSuccess`)
                    VALUES ('$id', '$name', 'NOFI_MENTN', '$v', '$mid', '#cmt-".$cn."', '$t', '$ip', '0')";
                    $result = mysqli_query($conn, $sql);
                }

                $i++;
                if($i > 20){ #안전장치
                    break;
                }
            }
        }
        $sql = "UPDATE `_comment` SET `content` = '$r' WHERE `num` = '$cn'"; #멘션으로 변경된 내용 반영
        $result = mysqli_query($conn, $sql);

        echo '<script>window.location.href = "./'.$v.'#cmt-'.$cn.'"</script>';
        break;
    case 'listing': #댓글 목록
        $sql = "SELECT * FROM `_content` WHERE `num` = '$n'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);

        echo '<main>
        <div class="flex">
        <section class="hidMob">
        </section>
        <section id="mainSec" class="half">
        <div class="card">
        <header style="background:#f3f3f3;border-bottom:1px solid #e6e6e6">';
        echo '<h3><a style="color:#000" href="b>'.$row['board'].'>'.$n.'">'.$row['title'].'</a></h3>';
        echo '<span class="subInfo"><i class="icofont-user-alt-7"></i> <a class="muted" href="./u>'.$row['id'].'_'.$row['board'].'">'.$row['name'].'</a></span></header></div>';

        echo '<div class="card">
        <header style="background:#f3f3f3;border-bottom:1px solid #e6e6e6">
            <h4><i class="icofont-comment"></i> 댓글</h4>
        </header>';

        $pgNum = $n;

        $userMail = get_gravatar($_SESSION['fnUserMail'], 56, 'identicon', 'pg'); #회원 메일 불러오기

        $sql = "SELECT * FROM `_comment` WHERE `type` in ('COMMON_CMT', 'FNBCON_CMT') and `from` = '$pgNum'";
        $cmtResult = mysqli_query($conn, $sql);
            if(empty($_SESSION['fnUserId'])){
                echo '
                <section class="muted" style="padding:8px;font-size:0.9em">
                    댓글 열람을 위해서는 <a href="./login"><i class="icofont-sign-in"></i> 로그인</a>이 필요합니다.
                </section>
                </div>
                ';
            }else{
                if(mysqli_num_rows($cmtResult) == 0){
                    echo '
                    <section class="muted">
                        댓글이 없습니다.
                    </section><hr>
                    ';
                }

                    while($cmtRow = mysqli_fetch_assoc($cmtResult)){
                        if($cmtRow['id'] == $_SESSION['fnUserId']){
                            $isMe = TRUE;
                        }
                        //댓글 로딩 (1차)
                        echo '<section class="comm" id="cmt-'.$cmtRow['num'].'">';
                            echo '<div class="cimg">
                                <img src="';
                                echo get_gravatar($cmtRow['mail'], 56, 'identicon', 'pg');
                            echo '"></div>';
                        echo '<div class="card">
                            <header>
                                <span class="subInfo">
                                    &nbsp;<i class="icofont-user-alt-7"></i>
                                    <a class="muted" href="./u>'.$cmtRow['id'].'">'.$cmtRow['name'].'</a><h-d><br></h-d>';
                                    echo ' <i class="icofont-clock-time"></i> '.$cmtRow['at'];
                                if($cmtRow['isEdited']){
                                    echo '<span data-tooltip="'.$cmtRow['isEdited'].' / '.$cmtRow['whoEdited'].'"
                                    class="tooltip-left"><i class="icofont-eraser"></i> 수정됨</span>';
                                }
                                echo '</span></header>
                            <section>';
                                if($cmtRow['type'] == 'FNBCON_CMT'){
                                    echo '<img height="100" src="./fnbcon/'.$cmtRow['content'].'">';
                                }else{
                                    echo nl2br($cmtRow['content']);
                                }
                            echo '</section>
                            <footer><form method="post" action="./comment.php?m=edit">';
                            if($isMe){
                                echo '<button onclick="editC('.$cmtRow['num'].')" id="ediB'.$cmtRow['num'].'"
                                style="background:#a8a8a8" type="button"><i class="icofont-eraser"></i><h-m> 수정</h-m></button> ';
                            }else{
                                echo '<button class="error"><i class="icofont-exclamation-circle"></i><h-m> 신고</h-m></button> ';
                            }
                                echo '<button class="warning"><i class="icofont-thumbs-down"></i><h-m> 반대</h-m></button>
                                <button class="success"><i class="icofont-thumbs-up"></i><h-m> 동의</h-m></button>
                                <span class="right">
                                <button onclick="addRp('.$cmtRow['num'].')" id="addR'.$cmtRow['num'].'" type="button"><i class="icofont-comment"></i><h-m> 답글 달기</h-m></button>
                                </span></form>
                            </footer>
                        </div>
                    </section>';
                        //답글 창 로딩
                        $parNum = $cmtRow['num'];
                        echo '<section class="comm step_1" id="reply-'.$cmtRow['num'].'" style="display:none"><form method="post" action="./comment.php?m=reply">
                            <div class="cimg">
                                <img src="'.$userMail.'">
                            </div>
                            <div class="card">
                                <header>
                                    <span class="subInfo">
                                        <i class="icofont-share-alt"></i> 답글 달기
                                    </span>
                                </header>
                                <section>
                                    <textarea onkeydown="ctrSM('.$cmtRow['num'].')" id="txtA'.$cmtRow['num'].'" name="reply" placeholder="댓글 작성"></textarea>
                                    <input type="hidden" name="childOf" value="'.$parNum.'">
                                    <input type="hidden" name="parentNum" value="'.$parNum.'">
                                    <input type="hidden" name="n" value="'.$pgNum.'">
                                </section>
                                <footer>
                                    <button style="width:100%;background:green" type="submit" id="addB'.$cmtRow['num'].'"
                                    data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top"><i class="icofont-check"></i> 작성 완료</button>
                                </footer>
                            </div>
                        </form></section>';
                    if($isMe){
                        //수정 창 로딩
                        $parNum = $cmtRow['num'];
                        echo '<section class="comm step_1" id="editC-'.$cmtRow['num'].'" style="display:none"><form method="post" action="./comment.php?m=edit">
                            <div class="cimg">
                                <img src="'.$userMail.'">
                            </div>
                            <div class="card">
                                <header>
                                    <span class="subInfo">
                                        <i class="icofont-eraser"></i> 수정하기
                                    </span>
                                </header>
                                <section>
                                    <textarea onkeydown="ctrSM('.$cmtRow['num'].')" id="txtA'.$cmtRow['num'].'" name="reply" placeholder="댓글 작성">'.$cmtRow['content'].'</textarea>
                                    <input type="hidden" name="n" value="'.$cmtRow['num'].'">
                                </section>
                                <footer>
                                    <button style="width:100%;background:#a8a8a8" type="submit" id="addB'.$cmtRow['num'].'"
                                    data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top"><i class="icofont-check"></i> 작성 완료</button>
                                </footer>
                            </div>
                        </form></section>';
                    }
                                //답글 로딩 (2차)
                                $rpSql = "SELECT * FROM `_comment` WHERE `type` = 'COMMON_REP' and `from` = '$pgNum' and `childOf` = '$parNum'";
                                $rpResult = mysqli_query($conn, $rpSql);
                                if(mysqli_num_rows($rpResult) !== 0){
                                    while($rpRow = mysqli_fetch_assoc($rpResult)){
                                        if($rpRow['id'] == $_SESSION['fnUserId']){
                                            $isMe = TRUE;
                                        }
                                        echo '<section class="comm step_1" id="cmt-'.$rpRow['num'].'">';
                                            echo '<div class="cimg">
                                                <img src="';
                                                echo get_gravatar($rpRow['mail'], 56, 'identicon', 'pg');
                                            echo '"></div>';
                                        echo '<div class="card">
                                            <header>
                                                <span class="subInfo">
                                                    &nbsp;<i class="icofont-user-alt-7"></i>
                                                    <a class="muted" href="./u>'.$rpRow['id'].'">'.$rpRow['name'].'</a><h-d><br></h-d>';
                                                    echo ' <i class="icofont-clock-time"></i> '.$rpRow['at'];
                                                if($rpRow['isEdited']){
                                                    echo '<span data-tooltip="'.$rpRow['isEdited'].' / '.$rpRow['whoEdited'].'"
                                                    class="tooltip-left"><i class="icofont-eraser"></i> 수정됨</span>';
                                                }
                                                echo '</span></header>
                                            <section>';
                                                echo nl2br($rpRow['content']);
                                            echo '</section>
                                            <footer><form>';
                                            if($isMe){
                                                echo '<button onclick="editC('.$rpRow['num'].')" id="ediB'.$rpRow['num'].'"
                                                style="background:#a8a8a8" type="button"><i class="icofont-eraser"></i><h-m> 수정</h-m></button> ';
                                            }else{
                                                echo '<button class="error"><i class="icofont-exclamation-circle"></i><h-m> 신고</h-m></button> ';
                                            }
                                                echo '<button class="warning"><i class="icofont-thumbs-down"></i><h-m> 반대</h-m></button>
                                                <button class="success"><i class="icofont-thumbs-up"></i><h-m> 동의</h-m></button>
                                                <span class="right">
                                                <button onclick="addRp('.$rpRow['num'].')" id="addR'.$rpRow['num'].'" type="button"><i class="icofont-comment"></i><h-m> 답글 달기</h-m></button>
                                                </span></form>
                                            </footer>
                                        </div>
                                    </section>';
                                        //답글 창 로딩
                                        $parNum = $rpRow['num'];
                                        echo '<section class="comm step_2" id="reply-'.$rpRow['num'].'" style="display:none"><form method="post" action="./comment.php?m=reply">
                                            <div class="cimg">
                                                <img src="'.$userMail.'">
                                            </div>
                                            <div class="card">
                                                <header>
                                                    <span class="subInfo">
                                                        <i class="icofont-share-alt"></i> 답글 달기
                                                    </span>
                                                </header>
                                                <section>
                                                    <textarea onkeydown="ctrSM('.$rpRow['num'].')" id="txtA'.$rpRow['num'].'" name="reply" placeholder="댓글 작성"></textarea>
                                                    <input type="hidden" name="childOf" value="'.$parNum.'">
                                                    <input type="hidden" name="parentNum" value="'.$parNum.'">
                                                    <input type="hidden" name="n" value="'.$pgNum.'">
                                                </section>
                                                <footer>
                                                    <button style="width:100%;background:green" type="submit" id="addB'.$rpRow['num'].'"
                                                    data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top"><i class="icofont-check"></i> 작성 완료</button>
                                                </footer>
                                            </div>
                                        </form></section>';
                                    if($isMe){
                                        //수정 창 로딩
                                        echo '<section class="comm step_2" id="editC-'.$rpRow['num'].'" style="display:none"><form method="post" action="./comment.php?m=edit">
                                            <div class="cimg">
                                                <img src="'.$userMail.'">
                                            </div>
                                            <div class="card">
                                                <header>
                                                    <span class="subInfo">
                                                        <i class="icofont-eraser"></i> 수정하기
                                                    </span>
                                                </header>
                                                <section>
                                                    <textarea onkeydown="ctrSM('.$rpRow['num'].')" id="txtA'.$rpRow['num'].'" name="reply" placeholder="댓글 작성">'.$rpRow['content'].'</textarea>
                                                    <input type="hidden" name="n" value="'.$rpRow['num'].'">
                                                </section>
                                                <footer>
                                                    <button style="width:100%;background:#a8a8a8" type="submit" id="addB'.$rpRow['num'].'"
                                                    data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top"><i class="icofont-check"></i> 작성 완료</button>
                                                </footer>
                                            </div>
                                        </form></section>';
                                    }
                                                //답글 로딩 (3차)
                                                $rplSql = "SELECT * FROM `_comment` WHERE `type` = 'COMMON_REP' and `from` = '$pgNum' and `parentNum` = '$parNum'";
                                                $rplResult = mysqli_query($conn, $rplSql);
                                                if(mysqli_num_rows($rplResult) !== 0){
                                                    while($rplRow = mysqli_fetch_assoc($rplResult)){
                                                        if($rplRow['id'] == $_SESSION['fnUserId']){
                                                            $isMe = TRUE;
                                                        }
                                                        echo '<section class="comm step_2" id="cmt-'.$rplRow['num'].'">';
                                                            echo '<div class="cimg">
                                                                <img src="';
                                                                echo get_gravatar($rplRow['mail'], 56, 'identicon', 'pg');
                                                            echo '"></div>';
                                                        echo '<div class="card">
                                                            <header>
                                                                <span class="subInfo">
                                                                    &nbsp;<i class="icofont-user-alt-7"></i>
                                                                    <a class="muted" href="./u>'.$rplRow['id'].'">'.$rplRow['name'].'</a><h-d><br></h-d>';
                                                                    echo ' <i class="icofont-clock-time"></i> '.$rplRow['at'];
                                                                if($rplRow['isEdited']){
                                                                    echo '<span data-tooltip="'.$rplRow['isEdited'].' / '.$rplRow['whoEdited'].'"
                                                                    class="tooltip-left"><i class="icofont-eraser"></i> 수정됨</span>';
                                                                }
                                                                echo '</span></header>
                                                            <section>';
                                                                if($rplRow['parentNum'] != $rplRow['childOf']){
                                                                    echo '<a onclick="cmtHR(\''.$rplRow['childOf'].'\')"><i class="icofont-share-alt"></i></a> ';
                                                                }
                                                                echo nl2br($rplRow['content']);
                                                            echo '</section>
                                                            <footer><form>';
                                                            if($isMe){
                                                                echo '<button onclick="editC('.$rplRow['num'].')" id="ediB'.$rplRow['num'].'"
                                                                style="background:#a8a8a8" type="button"><i class="icofont-eraser"></i><h-m> 수정</h-m></button> ';
                                                            }else{
                                                                echo '<button class="error"><i class="icofont-exclamation-circle"></i><h-m> 신고</h-m></button> ';
                                                            }
                                                                echo '<button class="warning"><i class="icofont-thumbs-down"></i><h-m> 반대</h-m></button>
                                                                <button class="success"><i class="icofont-thumbs-up"></i><h-m> 동의</h-m></button>
                                                                <span class="right">
                                                                <button onclick="addRp('.$rplRow['num'].')" id="addR'.$rplRow['num'].'" type="button"><i class="icofont-comment"></i><h-m> 답글 달기</h-m></button>
                                                                </span></form>
                                                            </footer>
                                                        </div>
                                                    </section>';
                                                        //답글 창 로딩
                                                        echo '<section class="comm step_3" id="reply-'.$rplRow['num'].'" style="display:none"><form method="post" action="./comment.php?m=reply">
                                                            <div class="cimg">
                                                                <img src="'.$userMail.'">
                                                            </div>
                                                            <div class="card">
                                                                <header>
                                                                    <span class="subInfo">
                                                                        <i class="icofont-share-alt"></i> 답글 달기
                                                                    </span>
                                                                </header>
                                                                <section>
                                                                    <textarea onkeydown="ctrSM('.$rplRow['num'].')" id="txtA'.$rplRow['num'].'" name="reply" placeholder="댓글 작성"></textarea>
                                                                    <input type="hidden" name="childOf" value="'.$rplRow['num'].'">
                                                                    <input type="hidden" name="parentNum" value="'.$parNum.'">
                                                                    <input type="hidden" name="n" value="'.$pgNum.'">
                                                                </section>
                                                                <footer>
                                                                    <button style="width:100%;background:green" type="submit" id="addB'.$rplRow['num'].'"
                                                                    data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top"><i class="icofont-check"></i> 작성 완료</button>
                                                                    <span class="subInfo">이 단계부터는 들여쓰기가 적용되지 않습니다.</span>
                                                                </footer>
                                                            </div>
                                                        </form></section>';
                                                    if($isMe){
                                                        //수정 창 로딩
                                                        echo '<section class="comm step_3" id="editC-'.$rplRow['num'].'" style="display:none"><form method="post" action="./comment.php?m=edit">
                                                            <div class="cimg">
                                                                <img src="'.$userMail.'">
                                                            </div>
                                                            <div class="card">
                                                                <header>
                                                                    <span class="subInfo">
                                                                        <i class="icofont-eraser"></i> 수정하기
                                                                    </span>
                                                                </header>
                                                                <section>
                                                                    <textarea onkeydown="ctrSM('.$rplRow['num'].')" id="txtA'.$rplRow['num'].'" name="reply" placeholder="댓글 작성">'.$rplRow['content'].'</textarea>
                                                                    <input type="hidden" name="n" value="'.$rplRow['num'].'">
                                                                </section>
                                                                <footer>
                                                                    <button style="width:100%;background:#a8a8a8" type="submit" id="addB'.$rplRow['num'].'"
                                                                    data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top"><i class="icofont-check"></i> 작성 완료</button>
                                                                </footer>
                                                            </div>
                                                        </form></section>';
                                                    }
                                                    $isMe = NULL;
                                                }
                                            }
                                            $isMe = NULL;
                                        }
                                    }
                                $isMe = NULL;
                }
            }
        echo '</div></section>
        <aside class="hidMob" id="nofiSec"></aside></div></main>';

        break;
}
?>

<script>
    function addRp(arg){
        if(document.getElementById('reply-' + arg).style.display == 'none'){
            document.getElementById('reply-' + arg).style.display = '';
            document.getElementById('addR' + arg).innerHTML = '<i class="icofont-error"></i><h-m> 창 닫기</h-m>';
        }else{
            document.getElementById('reply-' + arg).style.display = 'none';
            document.getElementById('addR' + arg).innerHTML = '<i class="icofont-comment"></i><h-m> 답글 달기</h-m>';
        }
    }

    function editC(arg){
        if(document.getElementById('editC-' + arg).style.display == 'none'){
            document.getElementById('editC-' + arg).style.display = '';
            document.getElementById('ediB' + arg).innerHTML = '<i class="icofont-error"></i><h-m> 창 닫기</h-m>';
        }else{
            document.getElementById('editC-' + arg).style.display = 'none';
            document.getElementById('ediB' + arg).innerHTML = '<i class="icofont-eraser"></i><h-m> 수정</h-m>';
        }
    }

    function cmtHR(arg){
        if(document.querySelector('#cmt-' + arg + ' div section').style.backgroundColor == 'yellow'){
            document.querySelector('#cmt-' + arg + ' div section').style.backgroundColor = '';
        }else{
            document.querySelector('#cmt-' + arg + ' div section').style.backgroundColor = 'yellow';
        }
    }

    function ctrSM(arg){
        var input = document.getElementById('txtA' + arg);
        input.addEventListener("keydown", function(event) {
            if (event.which == 13 && event.ctrlKey) {
                document.getElementById('addB' + arg).click();
            }
        });
    }
</script>