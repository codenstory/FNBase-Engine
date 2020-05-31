<?php
    $fnMultiNum = 2;
    require_once './setting.php';
    require_once './func.php';

    if(!empty($id)){
        $sql = "SELECT `siteBan` FROM `_account` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sql);
        $sB = mysqli_fetch_assoc($result);
        if($sB['siteBan'] >= 1){
            if($_GET['miscmode'] !== 'vindicate'){
                die('<script>location.href = \'/banned.php\'</script>');
            }
        }
    }

    $fnwTitle = filt($_GET['title'], 'htm');
    require_once './wiki_p.php';
    if(empty($fnwTitle)){
        $fnwTitle = '대문';
    }

    $fnwTitle = documentRender($fnwTitle, TRUE);
    $sql = "SELECT * FROM `_article` WHERE `title` = '$fnwTitle'";
    $document = mysqli_query($conn, $sql);
    if(mysqli_num_rows($document) < 1){
        unset($document);
        $document['title'] = $fnwTitle;
        $document['content'] = '___404TEXT___';
        $document['execute'] = 'search';
        $isNew = '작성';
        $isNewIcon = 'edit';
    }else{
        $isNew = '수정';
        $isNewIcon = 'eraser';
        $document = mysqli_fetch_assoc($document);
    }
    $title = documentRender(filt($document['title'], 'htm'), TRUE);
    $url = $title;
    if($document['namespace']){
        $title = '<span class="muted">'.documentRender($document['namespace'], TRUE).':</span>'.$title;
    }

        //ACL
        $sqla = "SELECT `isAdmin` FROM `_account` WHERE `id` = '$id'";
        $result = mysqli_query($conn, $sqla);
        $iA = mysqli_fetch_assoc($result);
        $iA = $iA['isAdmin'];

        if($document['ACL'] === NULL){
            $canEdit = TRUE;
        }elseif($document['ACL'] == 'none'){
            $canEdit = FALSE;
        }elseif($document['ACL'] == 'user'){
            if($id){
                $canEdit = TRUE;
            }else{
                $canEdit = FALSE;
            }
        }else{
            $canEdit = FALSE;
        }

        if(!$iA){
            $canManage = FALSE;
        }else{
            $canEdit = TRUE;
            $canManage = TRUE;
        }

    $yn = mt_rand(0,10);
    switch ($yn) {
        case 1:
        case 2:
        case 3:
            $sql = "SELECT COUNT(*) as `cnt` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 3 DAY) and `type` = 'USER_ADVER'";
            $res = mysqli_query($conn, $sql);
            $res = mysqli_fetch_assoc($res);
            $cnt = $res['cnt'] - 1;
            $n = mt_rand(0, $cnt);
            $tnLabel = '광고';
    
            if($cnt < 0){
                $isEmpty = TRUE;
            }else{
                $sql = "SELECT `ad`, `link` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 3 DAY) and `type` = 'USER_ADVER' and `ad` IS NOT NULL ORDER BY `at` DESC LIMIT $n, 1";
                $res = mysqli_query($conn, $sql);
                $res = mysqli_fetch_assoc($res);
    
                    $tnHref = $res['link'];
                    $tnText = $res['ad'];
            }
            break;
        case 10:
            $isEmpty = TRUE;
            break;
        default:
            $sql = "SELECT `title`, `namespace` FROM `_article` WHERE `namespace` IN ('프로젝트') ORDER BY `lastEdit` DESC LIMIT 1";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if($row['namespace'] == '프로젝트'){
                $tnLabel = '프로젝트';
                $tnPath = '/w/'.$row['title'];
                $tnText = $row['title'].' '.$tnLabel;
            }
            break;
    }
    if($isEmpty){
        $sql = "SELECT COUNT(*) as `cnt` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 30 DAY) and `type` = 'PUB_S_ADVT'";
        $res = mysqli_query($conn, $sql);
        $res = mysqli_fetch_assoc($res);
        $cnt = $res['cnt'] - 1;
        $n = mt_rand(0, $cnt);
        $tnLabel = '광고';

        if($cnt < 0){
            $isEmpty = TRUE;
        }else{
            $sql = "SELECT `ad`, `link` FROM `_ad` WHERE `at` > DATE_SUB(NOW(), INTERVAL 30 DAY) and `type` = 'PUB_S_ADVT' and `ad` IS NOT NULL ORDER BY `at` DESC LIMIT $n, 1";
            $res = mysqli_query($conn, $sql);
            $res = mysqli_fetch_assoc($res);
    
                $tnHref = $res['link'];
                $tnText = $res['ad'];
        }
    }
?>

<!DOCTYPE html>
<html lang="<?=$fnLang?>">
    <head>
        <meta charset="UTF-8">
        <meta name="robots" content="noindex">
        <meta name="author" content="FNBase Team">
        <meta name="theme-color" content="<?=$fnPColor?>">
        <meta name="classification" content="html">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
        <!-- 정보 -->
        <title><?=$fnTitle?></title>
        <meta name="description" content="<?=$fnDesc?>">
        <meta property="og:type" content="website">
    
        <!-- 불러오기 -->
        <style>
            @media (min-width: 300px) and (max-width: 1800px) {
                html {
                    scrollbar-width: none;
                }
                h-m {
                    display: none;
                }
                .hidMob {
                    display:none;
                }
            }
            @media (min-width: 1900px) {
                html {
                    scrollbar-width: thin;
                }
                h-d {
                    display: none;
                }
            }
            html { overflow-y:scroll; word-break: break-word }
        </style>
        <link rel="stylesheet" href="https://fnbase.xyz/wiki.css">
        <link rel="stylesheet" href="https://fnbase.xyz/default.css">
        <link rel="stylesheet" href="https://fnbase.xyz/picnic.css">
        <link rel="stylesheet" type="text/css" href="https://fnbase.xyz/icofont2/icofont.min.css">
        <link rel="shortcut icon" href="https://fnbase.xyz/wiki.png">
        <?=$fnPHead.$lsHead?>
    </head>
    <body style="background:<?=$fnBColor?>">
        <a id="top"></a>
        <!-- 상단바 #6149ad -->
        <header>
            <nav class="nav" style="position:static;background: <?=$fnPColor?>">
                <a href="/w/" class="brand">
                    <span style="color:#fff"><?=$fnTitle?></span>
                </a>
                <a href="/w/최근 바뀜" class="brand-r">
                    <span style="color:#fff"><i class="icofont-ui-rotation"></i><h-m> 최근 바뀜</h-m></span>
                </a>
                <a href="/w/최근 토론" class="brand-r">
                    <span style="color:#fff"><i class="icofont-users-alt-2"></i><h-m> 최근 토론</h-m></span>
                </a>
                <a href="/w/특수 문서 목록" class="brand-r">
                    <span style="color:#fff"><i class="icofont-file-exe"></i><h-m> 다른 기능</h-m></span>
                </a>
                <a href="/b>recent" class="brand-r">
                    <span style="color:#fff"><i class="icofont-listing-box"></i><h-m> 게시판</h-m></span>
                </a>
                <?php
                    if($_SESSION['fnUserId']){
                        echo '<div class="menu">
                            <label for="userModal"><img height="40" id="myGravatar" src="'.get_gravatar($_SESSION['fnUserMail'], 40, 'identicon', 'pg').'"></label>
                        </div>';
                        $isLogged = TRUE;
                    }else{
                        echo '<div class="menu">
                            <a href="/w/로그인" class="button"><h-m><i class="icofont-login"></i> 로그인</h-m><h-d><i class="icofont-invisible"></i></h-d></a>
                        </div>';
                        $isLogged = FALSE;
                    }
                ?>
            </nav>
            <span style="color:white">
                <div id="topNoticeLine" style="background:<?=$fnSColor?>">
                    <a href="<?=$tnHref?>" style="color:white"><span class="label success"><?=$tnLabel?></span> <?=$tnText?></a>
                </div>
            </span>
        </header>
        <!-- 페이지 로드 -->
        <article class="flex">
            <div class="hidMob">
            </div>
            <section id="mainArticle" class="half">
                <?php 
                if($_GET['on'] == 'discussPage'){
                    $pgNum = filt($_GET['d_num'], '123');
                    $pgMent = filt($_GET['ment'], '123');
                    if($isLogged){
                        if($pgMent){
                            $sql = "SELECT `target` FROM `_ment` WHERE `num` = '$pgMent'";
                            $result = mysqli_query($conn, $sql);
                            if(mysqli_num_rows($result) == 1){
                                $sql = "UPDATE `_ment` SET `isSuccess` = 1 WHERE `num` = '$pgMent'";
                                $result = mysqli_query($conn, $sql);
                                if(!$result){
                                    echo '데이터베이스 연결 실패';
                                }
                            }
                        }
                    }

                    $sql = "SELECT * FROM `_discuss` WHERE `num` = '$pgNum'";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    $d_num = $row['num'];
                    $d_title = $row['discussName'];
                    echo '<div class="card" class="wikiDiscussCard">
                            <header style="background:#f3f3f3;border-bottom:1px solid #e6e6e6">
                                <h4 class="black noGray"><a href="/d/'.$row['title'].'"><i class="icofont-page"></i> '.$row['title'].'</a></h4>
                            </header>
                            <section>';
                            if($row['status'] == 'ACTIVE'){
                                $dC = 'green';
                                $dT = '#fff';
                            }elseif($row['status'] == 'PAUSE'){
                                $dC = 'orange';
                                $dT = '#000';
                                $dSt = 'PAUSE';
                            }else{
                                $dC = 'black';
                                $dT = '#fff';
                                $dSt = 'DISABLED';
                            }
                            $sql = "SELECT count(*) as `cnt` FROM `_discussThread` WHERE `origin` = '$pgNum'";
                            $result = mysqli_query($conn, $sql);
                            $count = mysqli_fetch_assoc($result);
                            echo '<div class="card" style="border: 2px dashed gainsboro" class="wikiDiscussCard">
                                    <header style="background:'.$dC.';color:'.$dT.';border-bottom:1px solid #e6e6e6">
                                        <h4><i class="icofont-users-alt-2"></i> '.$row['discussName'].' ( '.$count['cnt'].' )</h4>
                                    </header>
                                    <section>';
                            $sql = "SELECT * FROM `_discussThread` WHERE `origin` = '$pgNum'";
                            $result = mysqli_query($conn, $sql);
                            
                            $d = 1;
                            while($row = mysqli_fetch_assoc($result)){
                                $wE = $row['id'];
                                $sqln = "SELECT `name` FROM `_account` WHERE `id` = '$wE'";
                                $resultn = mysqli_query($conn, $sqln);
                        
                                if(mysqli_num_rows($resultn) < 1){
                                    $name = $wE;
                        
                                    $icon = 'invisible';
                                    $href = 'javascript:void(0);" data-tooltip="가입하지 않은 사용자입니다." class="tooltip-bottom';
                                }else{
                                    $name = mysqli_fetch_assoc($resultn);
                                    $name = $name['name'];
                        
                                    $icon = 'user-alt-7';
                                    $href = '/u>'.$wE;
                                }

                                $content = nl2br(documentRender($row['content']));
                                $content = filt(preg_replace('/<\/h(\d)><br \/>/m', '</h$1>', $content), 'oth');

                                echo '<div class="card" class="wikiDiscussCard">
                                    <header class="black noGray" style="background:#f6f6f6;border-bottom:1px solid #e6e6e6">
                                        <b>#'.$d.'</b> <span class="muted"><i class="icofont-'.$icon.'"></i> <a href="'.$href.'">'.$name.'</a></span><span class="subInfo"><h-d><br></h-d><h-m>
                                        </h-m>('.$row['at'].')</span>
                                    </header>
                                    <section style="padding-bottom:.45em">
                                        '.$content.'
                                    </section>
                                </div>';
                                $d++;
                            }
                                        if($_SESSION['fnUserName']){
                                            $icon = 'user-alt-7';
                                            $name = $_SESSION['fnUserName'];
                                        }else{
                                            $icon = 'invisible';
                                            $name = $ip;
                                        }

                                        if($canEdit || $id){
                                        echo '<br><a id="discussBtm"></a><div class="card" class="wikiDiscussCard">
                                            <header class="black noGray" style="background:#f6f6f6;border-bottom:1px solid #e6e6e6">
                                                <b><i class="icofont-mic"></i> 발언하기</b><span class="muted"> <i class="icofont-'.$icon.'"></i> '.$name.'<span>
                                            </header>
                                            <section style="padding-bottom:.45em">
                                            <form action="/wiki_d.php?mode=speak&title='.$fnwTitle.'" method="post">
                                                <input type="hidden" name="num" value="'.$d_num.'">';
                                            if(!$dSt){
                                                echo '<textarea name="content" placeholder="예의를 지킵시다. (1000자 이내 작성)" style="border:0" maxlength="1000" required></textarea>
                                                <input type="submit" class="button success full" value="발언하기">';
                                            }else{
                                                echo '<textarea style="border:0" disabled>이 토론은 활성 상태가 아닙니다.</textarea>';
                                            }
                                            echo '</form>';
                                            if($iA){
                                                echo '<form method="post" action="/wiki_v.php?discuss=pause"><input type="hidden" name="num" value="'.$d_num.'">';
                                                if(!$dSt){
                                                    echo '<button type="submit" class="full" style="background:orange">이 토론 멈추기</button><br>';
                                                    echo '<button type="submit" class="error full" formaction="/wiki_v.php?discuss=stop">이 토론 숨기기</button>';
                                                }else{
                                                    echo '<button type="submit" class="success full" formaction="/wiki_v.php?discuss=play">이 토론 활성화</button>';
                                                }
                                                echo '</form>';
                                            }
                                            echo '</section>
                                        </div>';
                                        }
                                    echo '</section>
                                </div>';
                            echo '</section>
                        </div>';
                        $fnwTitle = '(토론) '.$d_title;
                }else{ ?>
                <div id="mainTitle">
                    <h2 id="wikiTitleText" class="black noGray"><a href="/w/<?=$url?>"><?=$title?><span id="wikiModeText" class="muted"></span></a></h2>
                    <hr><span id="wikiTitleRaw" style="display:none"><?=$fnwTitle?></span>
                </div>
                <div id="mainContent">
                    <?php
                        $parStr = documentRender('___PARENT___', TRUE);
                        if(strlen($parStr) > 0){
                            echo '상위 문서 : <a href="/w/'.$parStr.'">'.$parStr.'</a><hr>';
                        }
                        $content = nl2br(documentRender($document['content']));
                        echo preg_replace('/<br( \/)*>\n<hr>/m', '<hr>', preg_replace('/(src="|<hr>)(.*)<br( \/)*>/m', '$1$2', preg_replace('/<\/h(\d)><br \/>/m', '</h$1>', $content)));
                        if($document['execute']){
                            switch ($document['execute']) {
                                case 'list':
                                    echo '<br><table class="full"><tbody>';
                                    $sql = "SELECT `title`, `lastEdit`, `whoEdited` FROM `_article` WHERE `type` = 'SpecialDOC' ORDER BY `num`";
                                    $result = mysqli_query($conn, $sql);
                                    while($row = mysqli_fetch_assoc($result)){
                                        echo '<tr><td class="black"><a href="/w/'.$row['title'].'"><i class="icofont-gears"></i> '.$row['title'].'</td></tr>';
                                    }
                                    echo '</tbody></table>';
                                    break;
                                case 'login':
                                    if($id){
                                        session_unset();
                                        echo '<script>alert("로그아웃 되었습니다.");history.back()</script>';
                                    }else{
                                        echo '<br><form method="post" action="/sub/login.php"><hr>
                                            <label><input type="id" name="id" placeholder="아이디" style="border:0" required></label><hr>
                                            <label><input type="password" name="pw" placeholder="비밀번호" style="border:0" required></label>
                                            <input type="hidden" name="from" value="<?=$idPath?>"><hr>
                                            <a href="/forgot_password">비밀번호를 잊으셨나요?</a>
                                            <a href="/sub/ip_login.php" style="float:right"><i class="icofont-checked"></i><h-m> ip 로그인</h-m></a>
                                            <button class="button success full" type="submit">로그인</button>
                                        </form>';
                                    }
                                    break;
                                case 'recent':
                                        $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
                                        $result = mysqli_query($conn, $sql);
                                        $iA = mysqli_fetch_assoc($result);
                                            if($iA['isAdmin']){
                                                $isAdmin = TRUE;
                                            }
                                    echo '<br><table class="full"><tbody>';
                                    $sql = "SELECT * FROM `_article` WHERE `ACL` IS NULL OR `ACL` IN ('user', 'admin') ORDER BY `lastEdit` DESC LIMIT 30";
                                    $result = mysqli_query($conn, $sql);
                                    while($row = mysqli_fetch_assoc($result)){
                                        $wE = $row['whoEdited'];
                                        $sqln = "SELECT `name` FROM `_account` WHERE `id` = '$wE'";
                                        $resultn = mysqli_query($conn, $sqln);

                                        if(mysqli_num_rows($resultn) < 1){
                                            $name = $wE;
                                
                                            $icon = 'invisible';
                                            $href = 'javascript:void(0);" data-tooltip="가입하지 않은 사용자입니다." class="tooltip-bottom';
                                            if($isAdmin){
                                                $href = '../misc>manageCenter>'.$name;
                                            }
                                        }else{
                                            $name = mysqli_fetch_assoc($resultn);
                                            $name = $name['name'];
                                
                                            $icon = 'user-alt-7';
                                            $href = '/u>'.$wE;
                                        }
                                        echo '<tr><td class="black"><a href="/w/'.$row['title'].'"><i class="icofont-page"></i> '.$row['title'].'<br>
                                        <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).'
                                        <i class="icofont-'.$icon.'"></i> <a class="subInfo" href="'.$href.'">'.$name.'</a></span></td></tr>';
                                    }
                                    echo '</tbody></table>';
                                    break;
                                case 'discussRecent':
                                    echo '<br><table class="full"><tbody>';
                                    $sql = "SELECT * FROM `_discuss` WHERE `status` IN ('ACTIVE', 'PAUSE') ORDER BY `lastEdit` DESC LIMIT 30";
                                    $result = mysqli_query($conn, $sql);
                                    while($row = mysqli_fetch_assoc($result)){

                                        echo '<tr><td class="black"><a href="/discuss/'.$row['num'].'"><i class="icofont-users-alt-7"></i> '.$row['discussName'].'<br>
                                        <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).' /</span>
                                        <a href="/w/'.$row['title'].'" class="subInfo"><i class="icofont-page"></i> '.$row['title'].'</a></td></tr>';
                                    }
                                    echo '</tbody></table>';
                                    break;
                                case 'random':
                                    $sql = "SELECT count(*) as `cnt` FROM `_article`";
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);
                                    $rand = mt_rand('1', $row['cnt']);
                                    $sql = "SELECT `title` FROM `_article` WHERE `num` = '$rand'";
                                    $result = mysqli_query($conn, $sql);
                                    $row = mysqli_fetch_assoc($result);
                                    echo '<script>location.href = "/w/'.$row['title'].'"</script>';
                                    break;
                                case 'search':
                                    echo '<br><hr><h3>검색 결과</h3><b>제목 일치</b><table class="full"><tbody>';
                                    $sql = "SELECT * FROM `_article` WHERE `title` like '%$fnwTitle%' ORDER BY `lastEdit` DESC LIMIT 30";
                                    $result = mysqli_query($conn, $sql);
                                    if(mysqli_num_rows($result) > 0){
                                        while($row = mysqli_fetch_assoc($result)){
                                            $wE = $row['whoEdited'];
                                            $sqln = "SELECT `name` FROM `_account` WHERE `id` = '$wE'";
                                            $resultn = mysqli_query($conn, $sqln);

                                            if(mysqli_num_rows($resultn) < 1){
                                                $name = $wE;
                                    
                                                $icon = 'invisible';
                                                $href = 'javascript:void(0);" data-tooltip="가입하지 않은 사용자입니다." class="tooltip-bottom';
                                            }else{
                                                $name = mysqli_fetch_assoc($resultn);
                                                $name = $name['name'];
                                    
                                                $icon = 'user-alt-7';
                                                $href = '/u>'.$wE;
                                            }
                                            echo '<tr><td class="black noGray"><a href="/w/'.$row['title'].'"><i class="icofont-page"></i> '.$row['title'].'<br>
                                            <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).'
                                            <i class="icofont-'.$icon.'"></i> <a class="subInfo" href="'.$href.'">'.$name.'</a></span></td></tr>';
                                        }
                                    }else{
                                        echo '<tr><td>검색 결과가 없습니다..</td></tr>';
                                    }
                                    echo '</tbody></table>';
                                    echo '<br><b>내용 일치</b><table class="full"><tbody>';
                                    $sql = "SELECT * FROM `_article` WHERE `content` like '%$fnwTitle%' ORDER BY `lastEdit` DESC LIMIT 30";
                                    $result = mysqli_query($conn, $sql);
                                    if(mysqli_num_rows($result) > 0){
                                        while($row = mysqli_fetch_assoc($result)){
                                            $wE = $row['whoEdited'];
                                            $sqln = "SELECT `name` FROM `_account` WHERE `id` = '$wE'";
                                            $resultn = mysqli_query($conn, $sqln);

                                            preg_match('/(.{0,80})('.$fnwTitle.')(.{0,80})/mu', $row['content'], $stxt);
                                            $stxt = str_ireplace($fnwTitle, '<mark>'.$fnwTitle.'</mark>', $stxt);

                                            if(mysqli_num_rows($resultn) < 1){
                                                $name = $wE;
                                    
                                                $icon = 'invisible';
                                                $href = 'javascript:void(0);" data-tooltip="가입하지 않은 사용자입니다." class="tooltip-bottom';
                                            }else{
                                                $name = mysqli_fetch_assoc($resultn);
                                                $name = $name['name'];
                                    
                                                $icon = 'user-alt-7';
                                                $href = '/u>'.$wE;
                                            }
                                            echo '<tr><td class="black noGray"><a href="/w/'.$row['title'].'"><i class="icofont-page"></i> '.$row['title'].'<br>
                                            <blockquote>..'.$stxt[0].'..</blockquote>
                                            <span class="subInfo"><i class="icofont-eraser"></i> '.get_timeFlies($row['lastEdit']).'
                                            <i class="icofont-'.$icon.'"></i> <a class="subInfo" href="'.$href.'">'.$name.'</a></span><br></td></tr>';
                                        }
                                    }else{
                                        echo '<tr><td>검색 결과가 없습니다..</td></tr>';
                                    }
                                    echo '</tbody></table>';
                                    echo '<br><hr><form action="javascript:void(0)" onsubmit="location.href = \'/w/\'+document.getElementById(\'search-query\').value">
                                    <input type="text" id="search-query" placeholder="검색..">
                                    <button type="submit" class="success full"><i class="icofont-search-folder"></i> 검색하기</button>
                                    <a class="button full" href="/w/임의 문서로"><i class="icofont-random"></i> 임의 문서로</a></form>';
                                    echo '<br><hr><button style="background:gray;color:white;border:black solid 2px"
                                    onclick="location.href=\'https://ko.wikipedia.org/wiki/'.$fnwTitle.'\'">위키백과<h-m>에서 보기</h-m></button>
                                    <button style="background:#4188f1;color:white;border:blue solid 2px"
                                    onclick="location.href=\'https://librewiki.net/wiki/'.$fnwTitle.'\'">리브레위키<h-m>에서 보기</h-m></button>
                                    <button style="background-image:linear-gradient(to right, #00a495 30%, #13ad65);color:white;border:green solid 2px;float:right"
                                    onclick="location.href=\'https://namu.wiki/w/'.$fnwTitle.'\'">나무위키<h-m>에서 보기</h-m></button>';
                                    break;
                                
                                default:
                                    echo '<br><table class="full">';

                                    echo '<tbody>';
                                    echo '<tr><td colspan="2" style="background:'.$fnPColor.'"><strong>사이트 정보</strong></td></tr>';
                                    echo '<tr><td>사이트 이름</td><td>'.$fnTitle.'</td></tr>';
                                    echo '<tr><td>사이트 설명</td><td>'.$fnDesc.'</td></tr>';
                                    echo '<tr><td>기본 언어 설정</td><td>'.$fnLang.'</td></tr>';
                                    echo '<tr><td>기본 시간대 값</td><td>'.$fnTz.'</td></tr>';

                                    echo '<tr><td colspan="2" style="background:'.$fnPColor.'"><strong>스킨 정보</strong></td></tr>';
                                    echo '<tr><td>적용 스킨</td><td>Default</td></tr>';
                                    echo '<tr><td>기본 색상</td><td style="color:'.$fnPColor.'">'.$fnPColor.'</td></tr>';
                                    echo '<tr><td>보조 색상</td><td><span style="color:'.$fnSColor.'">'.$fnSColor.'</span><sub>2단계</sub> /
                                    <span style="color:'.$fnBColor.';background:rgba(0,0,0,0.5)">'.$fnBColor.'</span><sub>배경</sub></td></tr>';

                                    echo '<tr><td colspan="2" style="background:'.$fnPColor.'"><strong>서버 환경</strong></td></tr>';
                                    echo '<tr><td>PHP 버전</td><td>'.phpversion().'</td></tr>';
                                    echo '<tr><td>FNBE 버전</td><td><a href="/w/버전별 변경점#'.$fnVersion.'">'.$fnVersion.'</a></td></tr>';

                                    echo '<tr><td colspan="2" style="background:'.$fnPColor.'"><strong>오픈소스 정보</strong></td></tr>';
                                    echo '<tr><td><a href="https://picnicss.com">Picnic CSS</a></td><td>MIT - Francisco Presencia</td></tr>';
                                    echo '<tr><td><a href="https://icofont.org">IcoFont</a></td><td>MIT - Icofont</td></tr>';
                                    echo '<tr><td><a href="https://htmlpurifier.org">HTML Purifier</a></td><td>LGPL - HTML Purifier</td></tr>';
                                    echo '</tbody>';

                                    echo '</table>';
                                    break;
                            }
                        }
                    ?>
                </div>
                <div id="editPlace" style="display:none">
                    <div id="editPlaceText"></div>
                </div>
                <div id="contentFooter" class="subInfo" style="text-align: right">
                    <hr>
                    <p>모든 문서는 <a href="https://creativecommons.org/licenses/by-sa/4.0/deed.ko">CC-BY-SA 4.0</a>으로 배포되며,
                    <h-d><br></h-d>기여자는 기여 부분에 대한 저작권을 갖습니다.&nbsp;<br>
                    문서에 첨부된 사진, 동영상 등의 경우, 개별적인 라이선스를 따릅니다.&nbsp;</p>
                    <p>OpenFNB는 정보만을 중시하지 않으며, 정확성을 보장하지 않습니다.&nbsp;<br>
                    자유롭게 이용 가능하나 내부 규칙 및 대한민국 법률을 위반하지 마세요.&nbsp;</p>
                </div>
                    <?php } ?>
            </section>
            <aside class="hidMob">
                    <section id="asideFunction">
                        <div>
                            <br><br><br><br>
                        <?php
                        if(!$pgNum){
                            if($document['ACL'] !== 'none'){
                                    echo '&nbsp;<button type="button" onclick="wikiEdit(\''.$fnwTitle.'\')"';
                                    if(!$canEdit){
                                        echo ' class="outline"';
                                        $isNew = '원본';
                                        $isNewIcon = 'file-text';
                                    }else{
                                        echo ' class="outline-green"';
                                    }
                                    echo'><i class="icofont-'.$isNewIcon.'"></i> '.$isNew.'</button><br>';
                                    echo '&nbsp;<button type="button" class="outline-blue" onclick="wikiDiscuss(\''.$fnwTitle.'\')"><i class="icofont-users-alt-2"></i> 토론</button><br>';
                            }
                                    echo '&nbsp;<button type="button" class="outline" onclick="wikiHistory(\''.$fnwTitle.'\')"><i class="icofont-history"></i> 기록</button>';
                                    echo '<br>&nbsp;<button type="button" class="outline-danger" onclick="wikiManage(\''.$fnwTitle.'\')"><i class="icofont-gears"></i> 조정</button>';
                        }
                        ?>
                        </div>
                    </section>
            </aside>
        </article>
        <hr>
        <!-- 로그인 모달 호출 -->
            <?php
                if($_SESSION['fnUserId']){
                    echo '
                    <!-- 유저 모달 호출 -->
                    <div class="modal">
                        <input id="userModal" type="checkbox" />
                        <label for="userModal" class="overlay"></label>
                        <article>
                            <header>
                            <h3>메뉴</h3>
                            <label for="userModal" class="close">&times;</label>
                            </header>
                            <form method="post" action="login.php">
                                <section class="content">
                                    회원 전용 기능입니다. 사용해보세요!
                                </section>
                                <footer class="lilMob">
                                    <a class="button" href="/u>'.$_SESSION['fnUserId'].'">내 정보</a>
                                    <span class="right">
                                    <a class="button dangerous" href="/wiki/로그인">로그아웃</a>
                                    </span>
                                </footer>
                            </form>
                        </article>
                    </div>
                    ';
                }
            ?>
            </a>
            <div class="modal">
                <input id="wikiOption" type="checkbox" />
                <label for="wikiOption" class="overlay"></label>
                <article>
                    <header>
                    <h3><i class="icofont-plus-square"></i> 표시 설정</h3>
                    <label for="wikiOption" class="close">&times;</label>
                    </header>
                    <form method="post" action="login.php">
                        <section class="content">
                            <label><input type="id" name="id" placeholder="상단바 색상 (hex)" class="pcwidth" value="<?=$fnPColor?>" required></label><hr>
                            <label>
                                <input type="checkbox">
                                <span class="checkable">상단 알림줄 끄기</span>
                            </label><h-d><br></h-d>
                            <label>
                                <input type="checkbox">
                                <span class="checkable">야간 모드 활성화</span>
                            </label><h-d><br></h-d>
                            <label>
                                <input type="checkbox">
                                <span class="checkable">글씨 크게</span>
                            </label>
                        </section>
                        <footer>
                            <button class="button success full" type="submit">설정 저장</button>
                        </footer>
                    </form>
                </article>
            </div>
            <div class="modal">
                <input id="wikiFunction" type="checkbox" />
                <label for="wikiFunction" class="overlay" id="wFClose"></label>
                <article>
                    <header>
                    <h3><i class="icofont-options"></i> 문서 옵션</h3>
                    <label for="wikiFunction" class="close">&times;</label>
                    </header>
                    <footer class="lilMob">
                        <button type="button" class="outline-blue" onclick="wikiDiscuss('<?=$fnwTitle?>')"><i class="icofont-users-alt-2"></i> 토론</button>
                        <button type="button" class="outline" onclick="wikiHistory('<?=$fnwTitle?>')"><i class="icofont-history"></i> <h-m>편집 </h-m>기록</button>
                        <button type="button" class="outline-danger" onclick="wikiManage('<?=$fnwTitle?>')"><i class="icofont-gears"></i> 조정</button>&nbsp;
                        <button type="button" onclick="wikiEdit('<?=$fnwTitle?>')" class="<?php
                            if(!$canEdit){
                                echo 'outline';
                                $isNew = '원본';
                                $isNewIcon = 'file-text';
                            }else{
                                echo 'outline-green';
                            }
                        ?> right"><i class="icofont-<?=$isNewIcon?>"></i> <?=$isNew?></button>
                    </footer>
                </article>
            </div>
            <div class="modal">
                <input id="wikiSearch" type="checkbox" />
                <label for="wikiSearch" class="overlay"></label>
                <article>
                    <header>
                    <h3><i class="icofont-search-document"></i> 문서 검색</h3>
                    <label for="wikiSearch" class="close">&times;</label>
                    </header>
                    <footer class="pcwidth">
                    <form action="javascript:void(0)" onsubmit="location.href = '/w/'+document.getElementById('search-query').value">
                        <input type="text" id="search-query" placeholder="검색..">
                        <button type="submit" class="success full"><i class="icofont-search-folder"></i> 검색하기</button>
                        <a class="button full" href="/w/임의 문서로"><i class="icofont-random"></i> 임의 문서로</a>
                    </form>
                    </footer>
                </article>
            </div>
            <div class="modal">
                <input id="wikiHistoryRevView" type="checkbox" />
                <label for="wikiHistoryRevView" id="wHrClose" class="overlay"></label>
                <article>
                    <header>
                    <h3><i class="icofont-search-document"></i> 편집 기록 열람</h3>
                    <label for="wikiHistoryRevView" class="close">&times;</label>
                    </header>
                    <section class="lilMob" style="border-bottom: 2px dashed gainsboro">
                        <button class="success" type="button" onclick="wikiRollConf()"><i class="icofont-spinner-alt-3"></i> 이 버전으로 되돌리기</button>
                        <button class="right" type="button" onclick="wikiHisRaw()"><i class="icofont-file-text"></i> Raw</button>
                    </section>
                    <footer class="pcwidth" id="wHrText">
                        표시 대기중...
                    </footer>
                </article>
            </div>
            <div class="modal">
                <input id="wikiPreview" type="checkbox" />
                <label for="wikiPreview" id="wPreClose" class="overlay"></label>
                <article>
                    <header>
                    <h3><i class="icofont-search-document"></i> 편집 미리보기</h3>
                    <label for="wikiPreview" class="close">&times;</label>
                    </header>
                    <footer class="pcwidth" id="wPreText">
                        표시 대기중...
                    </footer>
                </article>
            </div>
            <!-- 알림창 -->
            <?php
            if($isLogged){
                $sql = "SELECT `type` FROM `_ment` WHERE `target` = '$id' and `isSuccess` = 0";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) > 0){
                    echo '<a id="nofiBox" href="./nofi">
                        <span style="color:yellow">[알림] 새 알림이 있습니다!</span>';
                    $cmC = 0;
                    $mtC = 0;
                    while($row = mysqli_fetch_assoc($result)){
                        $i++;
                        if($row['type'] == 'NOFI_CMMNT' || $row['type'] == 'NOFI_REPLY'){
                            $cmC++;
                        }else{
                            $mtC++;
                        }
                    }
                    echo '<br><span class="nofiText">(호출 '.$mtC.'건, 댓글 '.$cmC.'건)</span><br>';
                }elseif($board == 'recent'){
                    echo '<a id="nofiBox" href="./nofi" style="opacity: 0.7">
                        <span class="nofiText">[알림] 새 알림이 없습니다.</span><br>';
                }
            }
            echo '</a>';
            ?>
            <!-- Top / Bottom -->
            <div id="pgOption">
                <label for="wikiOption"><i class="icofont-options"></i></label>
                <label for="wikiSearch"><i class="icofont-search-document"></i></label>
                <label for="wikiFunction"><i class="icofont-plus-square"></i></label>
            </div>
            <div id="pgUpDown">
                <a href="#top"><i class="icofont-swoosh-up"></i></a>
                <a href="#bottom"><i class="icofont-swoosh-down"></i></a>
            </div>
            <!-- 알림창 -->
            <?php
            if($isLogged){
                $sql = "SELECT `type` FROM `_ment` WHERE `target` = '$id' and `isSuccess` = 0";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) > 0){
                    echo '<a id="nofiBox" href="/nofi">
                        <span style="color:yellow">[알림] 새 알림이 있습니다!</span>';
                    $cmC = 0;
                    $mtC = 0;
                    while($row = mysqli_fetch_assoc($result)){
                        $i++;
                        if($row['type'] == 'NOFI_CMMNT' || $row['type'] == 'NOFI_REPLY'){
                            $cmC++;
                        }else{
                            $mtC++;
                        }
                    }
                    echo '<br><span class="nofiText">(호출 '.$mtC.'건, 댓글 '.$cmC.'건)</span><br>';
                }
            }
            echo '</a>';
            ?>
            <!-- 하단바 -->
        <footer>
            <div class="flex">
                <div>
                    <p class="right">(c) 2020 FNBase</p>
                </div>
                <div>
                    <p class="left muted">
                        <a href="/w/이용 안내">이용 안내</a>
                    </p>
                </div>
            </div>
        </footer>
            <a id="bottom"></a>
        <script type="text/javascript" src="/wiki.js"></script>
        <script>
            document.title = "<?=$fnwTitle.' - '.$fnTitle?>"
            wikiTitle = "<?=$fnwTitle?>"</script>
        <?php
            if($_GET['on'] == 'discuss'){
                echo '<script>wikiDiscuss(\''.$fnwTitle.'\');</script>';
            }elseif($_GET['on'] == 'edit'){
                echo '<script>wikiEdit(\''.$fnwTitle.'\');</script>';
            }elseif($_GET['on'] == 'history'){
                echo '<script>wikiHistory(\''.$fnwTitle.'\');</script>';
            }

            $sql = "UPDATE `_article` SET `viewCount` = `viewCount` + 1 WHERE `title` = '$fnwTitle'";
            $result = mysqli_query($conn, $sql);
        ?>
    </body>
</html>