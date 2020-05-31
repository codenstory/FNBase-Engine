<?php

    $sql = "SELECT * FROM `_board` WHERE `slug` = '$lsBoard';"; #게시판 설정 로드
    $lsResult = mysqli_query($conn, $sql);
    if($lsResult === FALSE){
        die('데이터베이스 오류');
    }
    if(mysqli_num_rows($lsResult) == 1){
        $lsBoard = mysqli_fetch_assoc($lsResult);
    }else{
        die('게시판 아이디 값이 바르지 않습니다.');
    }

    $board = $lsBoard['slug']; #변수에 값 넣기
    $ownerId = $lsBoard['id'];
    $ownerName = $lsBoard['name'];
    $boardName = $lsBoard['title'];
    $boardNick = $lsBoard['nickTitle'];
    $boardIntro = $lsBoard['boardIntro'];
    $subs = $lsBoard['subs'];
    $kpr = $lsBoard['keeper'];
    $kcd = $lsBoard['kicked'];
    $relate = $lsBoard['related'];
    $boardType = $lsBoard['type'];
    $alNotice = $lsBoard['notice'];
    $boardOpt = $lsBoard['option'];

    echo '<script>document.title = "'.$lsBoard['title'].'";isTitCh = true</script>'; #제목 변경

    if($_SESSION['fnUserId'] === $ownerId){ #채널 설정 권한 여부
        $lsTemp = '<a class="button error" href="./b>'.$board.'>maint"><i class="icofont-settings"></i><h-m> 채널 설정</h-m></a>&nbsp;';
        $isOwner = TRUE;
        $isStaff = TRUE;
    }elseif(mb_strpos($kpr, $_SESSION['fnUserId']) !== FALSE){
        $isStaff = TRUE;
    }

    if(!empty($lsBoard['icon'])){
        $boardIcon = '<i class="icofont-'.$lsBoard['icon'].'"></i> ';
    }

    switch ($boardType) { #게시판 종류
        case 'AUTO_GENER':
            $boardType = '자동';
            $boardTypeStyle = 'class="label" style="background:gray"';
            $isAG = TRUE;
            break;
        case 'DIRECT_OPT':
            $boardType = '공설';
            $boardTypeStyle = 'class="label"';
            break;
        case 'PRIVAT_OPT':
            $boardType = '사설';
            $boardTypeStyle = 'class="label" style="background:whitesmoke;color:black"';
            break;
        case 'OWNER_ONLY':
            $boardType = '개인';
            $boardTypeStyle = 'class="label" style="background:green;color:white"';
            if(!$isOwner){
                $isAG = TRUE;
            }
            $isOW = TRUE;
            break;
        case '_READ_ONLY':
            $boardType = '읽기전용';
            $boardTypeStyle = 'class="label warning"';
            break;
        case '__DISABLED':
            $boardType = '비활성화';
            $boardTypeStyle = 'class="label dangerous"';
            break;
    }

    if(!empty($uS['display_none'])){
        $sad = "and `id` NOT IN (".$uS['display_none'].")";
    }else{
        $sad = NULL;
    }

    if(!empty($uS['listNum'])){ #1페이지에 표시될 글 수
        $lsPgN = $uS['listNum'];
    }else{
        $lsPgN = 10;
    }

    if(empty($_GET['p'])){
        $limit = '0, '.$lsPgN;
        $lsPg = 1;
    }else{
        $lsPg = filt($_GET['p'], '123');
        $b = $lsPg * $lsPgN - $lsPgN;
        $limit = $b.', '.$lsPgN;
    }

    if($lsBoard['type'] == 'AUTO_GENER'){ #글 정렬
        switch ($board) {
            case 'HOF':
                $sql = "SELECT * FROM `_content` WHERE `voteCount_UP` - `voteCount_Down` > 19 and `viewCount` > 499 and `commentCount` > 49 ORDER BY `num` DESC LIMIT $limit";
                break;
            case 'trash':
                $sql = "SELECT * FROM `_content` WHERE `type` IN ('COMMON', 'ANON_WRITE') and `board` = 'trash' $sad ORDER BY `num` DESC LIMIT $limit";
                break;
            case 'guide':
                $sql = "SELECT * FROM `_content` WHERE `board` = 'guide' ORDER BY `num` DESC LIMIT $limit";
                break;

            default:
                $sql = "SELECT * FROM `_content` WHERE `type` IN ('COMMON', 'ANON_WRITE') and `board` NOT LIKE 'trash' $sad ORDER BY `num` DESC LIMIT $limit";
                break;
        }
    }else{
        $sql = "SELECT * FROM `_content` WHERE `board` = '$board' and `type` IN ('COMMON', 'ANON_WRITE') $sad ORDER BY `num` DESC LIMIT $limit";
    }

    $listResult = mysqli_query($conn, $sql);
    if(mysqli_num_rows($listResult) > 0){
        $isEmpty = FALSE;
        if($alNotice !== NULL){
            $idAlert = 'notice';
        }
    }else{
        $isEmpty = TRUE;
        $idAlert = 'empty';
    }

    if(!empty($_GET['q'])){
        $qS = filt($_GET['q'], 'oth');
        $qM = filt($_GET['qm'], 'oth');
        $qP = filt($_GET['qp'], 'oth');

        if(empty($qP)){
            $qP = 1;
        }
        $l = $qP * 10;
        $lc = $l - 10;
        $l = $lc.', 10';

        switch ($qM) {
            case 'title':
                $sql_ = "SELECT `num` FROM `_content` WHERE `title` like '%$qS%' and `staffOnly` IS NULL";
                $sql = "SELECT * FROM `_content` WHERE `title` like '%$qS%' and `staffOnly` IS NULL ORDER BY `at` DESC LIMIT $l";
                $qT = '(제목)';
                break;
            case 'content':
                $sql_ = "SELECT `num` FROM `_content` WHERE `content` like '%$qS%' and `staffOnly` IS NULL";
                $sql = "SELECT * FROM `_content` WHERE `content` like '%$qS%' and `staffOnly` IS NULL ORDER BY `at` DESC LIMIT $l";
                $qT = '(내용)';
                break;
            case 'author':
                $sql_ = "SELECT `num` FROM `_content` WHERE `name` like '%$qS%' and `staffOnly` IS NULL";
                $sql = "SELECT * FROM `_content` WHERE `name` like '%$qS%' and `staffOnly` IS NULL ORDER BY `at` DESC LIMIT $l";
                $qT = '(글쓴이)';
                break;
            case 'comment':
                $sql_ = "SELECT `num` FROM `_comment` WHERE `content` like '%$qS%' and `staffOnly` IS NULL";
                $sql = "SELECT c.*, s.num AS cn, s.content AS cc
                FROM `_content` AS c
                RIGHT JOIN `_comment` AS s
                ON c.num = s.from
                WHERE s.`content` like '%$qS%' and `staffOnly` IS NULL ORDER BY `at` DESC LIMIT $l";
                $qPlus = '<span class="subInfo">댓글 검색은 내용 일치 여부만 봅니다.</span><br>';
                $qT = '(댓글)';
                break;
            
            default:
                $sql_ = "SELECT `num` FROM `_content` WHERE `title` like '%$qS%' OR `content` like '%$qS%' and `staffOnly` IS NULL";
                $sql = "SELECT * FROM `_content` WHERE `title` like '%$qS%' OR `content` like '%$qS%' and `staffOnly` IS NULL ORDER BY `at` DESC LIMIT $l";
                $qM = 'both';
                break;
        }
        $result = mysqli_query($conn, $sql_);
        $qC = mysqli_num_rows($result);
        $qR = ' 결과 - '.$qC.'건 '.$qT;

        $result = mysqli_query($conn, $sql);

        $lsSearch = '<article class="card">
        <header>
        <h3 class="muted"><i class="icofont-search-document"></i> 통합 검색'.$qR.'</h3>
        </header>
            <section class="content">';
            if(mysqli_num_rows($result) < 1){
                $lsSearch .= '<span class="muted">검색 결과가 없습니다..</span>';
            }else{
                $lsSearch .= '<table class="list full">
                <thead>
                    <tr>
                        <th width="10%" class="hidMob">&nbsp;종류</th>
                        <th width="50%">&nbsp;제목</th>
                        <th width="40%">&nbsp;정보</th>
                    </tr>
                </thead>
                <tbody>';
                if($qM !== 'comment'){
                    while($row = mysqli_fetch_assoc($result)){ #일반 글 조회
                        $time = get_timeFlies($row['at']);

                        $lsSearch .= '<tr>';
                        $lsSearch .= '<td class="hidMob muted">'.$row['category'].'</td>';
                        $lsSearch .= '<td class="black"><a href="./b%3E'.$row['board'].'%3E'.$row['num'].'-'.$lsPg.'">'.textalter($row['title'], 3).'</a>
                        <green class="little">['.$row['commentCount'].']</green>';
                        if(!empty($row['staffOnly'])){
                            $lsSearch .= '<i class="icofont-lock"></i>';
                        }
                        $lsSearch .= '</td>';
                        $lsSearch .= '<td class="infoList"><i class="icofont-user-alt-7"></i>
                        <a class="muted" href="./u%3E'.$row['id'].'_'.$board.'">'.$row['name'].'</a><h-d><br></h-d>';
                        $lsSearch .= ' <i class="icofont-clock-time"></i> '.$time.'<h-m> <green>'.$row['viewCount'].'</green></h-m></td>';
                        $lsSearch .= '</tr>';
                    }
                }else{
                    while($row = mysqli_fetch_assoc($result)){ #댓글 조회
                        $time = get_timeFlies($row['at']);

                        $lsSearch .= '<tr>';
                        $lsSearch .= '<td class="hidMob muted">'.$row['category'].'</td>';
                        $lsSearch .= '<td class="black"><a href="./b%3E'.$row['board'].'%3E'.$row['num'].'-'.$lsPg.'#cmt-'.$row['cn'].'">'.textalter($row['title'], 3).'</a>
                        <green class="little">['.$row['commentCount'].']</green>';
                        if(!empty($row['staffOnly'])){
                            $lsSearch .= '<i class="icofont-lock"></i>';
                        }
                        if(strlen($row['cc']) > 50){
                            $row['cc'] = mb_substr($row['cc'], 0, 47).'...';
                        }
                        $lsSearch .= '<br><span class="subInfo">'.$row['cc'].'</span>';
                        $lsSearch .= '</td>';
                        $lsSearch .= '<td class="infoList"><i class="icofont-user-alt-7"></i>
                        <a class="muted" href="./u%3E'.$row['id'].'_'.$board.'">'.$row['name'].'</a><h-d><br></h-d>';
                        $lsSearch .= ' <i class="icofont-clock-time"></i> '.$time.'</td>';
                        $lsSearch .= '</tr>';
                    }
                }
                $lsSearch .= '</tbody></table><br>'.$qPlus;
                $lsSearch .= '<!-- 페이지 이동 -->
                <div class="center">
                    <div class="pagination">
                        <a href="./?qm='.$qM.'&q='.$qS.'&qp=1">&laquo;</a>';

                        $cac = $qC / 10;
                        $qlast = ceil($cac);

                        $cal = $qC % 10;
                        if($cal == 0){
                            $qlast = $qlast - 1;
                        }

                        $cac2 = $qP - 2;
                        if($cac2 <= 0){
                            $qPg = 1;
                        }else{
                            $qPg = $cac2;
                        }

                        $i = 1;
                        while (1) {
                            if($qPg == $qP){
                                $lsSearch .= '<a class="active" href="./?qm='.$qM.'&q='.$qS.'&qp='.$qPg.'">'.$qPg.'</a>';
                            }else{
                                $lsSearch .= '<a href="./?qm='.$qM.'&q='.$qS.'&qp='.$qPg.'">'.$qPg.'</a>';
                            }
                            if($i == 5){
                            break;
                            }
                            if($qPg == $qlast){
                            break;
                            }
                            $qPg++;
                            $i++;
                        }

                        $lsSearch .= '<a href="./?qm='.$qM.'&q='.$qS.'&qp='.$qlast.'">&raquo;</a>';
                    $lsSearch .= '</div>
                </div>';
            }
            $lsSearch .= '
                <section class="search">
                    <hr>
                    <form action="./main" method="get">
                        <select name="qm">
                            <option value="both">제목/내용</option>
                            <option value="title">제목</option>
                            <option value="content">내용</option>
                            <option value="author">글쓴이</option>
                            <option value="comment">댓글</option>
                        </select>
                        <input type="text" placeholder="검색어 입력" name="q" value="'.$qS.'">
                        <input type="submit" value="검색">
                    </form>
                    <hr>
                </section>
            </section>
        </article>';
    }

    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
    $result = mysqli_query($conn, $sql);
    $iA = mysqli_fetch_assoc($result);
        if($iA['isAdmin']){
            $isAdmin = TRUE;
        }
?>
    <main>
        <div class="flex">
        <!-- 상단 보조메뉴 -->
            <section id="mainSec" class="half listMain">
            <?php require './alert.php'; ?>
                <hr>
                    &nbsp;<a href="./b%3E<?=$board?>" class="lager"><?=$boardIcon.$boardName?></a><label for="boardInfoModal" <?=$boardTypeStyle?>><?=$boardType?></label><br>
                    &nbsp;<span class="subInfo"><?=$boardIntro?>
                    <span class="right"><?=$lsTemp?><?php if($id){if(!$isAG){ #게시글 작성·수정 옵션
                    if(!$isEditor){echo '<a href="./b%3E'.$board.'>write"
                    class="button" style="background:green"><i class="icofont-edit"></i><h-m> 글쓰기</h-m></a></span></span>';}else{echo '
                    <label for="optModal" style="float:right;background:#6633ff" class="button">
                    <i class="icofont-plus-square"></i><h-m> 부가 기능</h-m></label></span></span>';
                    echo '<!-- 글 옵션 모달 호출 -->
                    <form method="post" action="save.php';
                    if($edContent){
                        echo '?e=dit">';
                        echo '<input type="hidden" name="n" value="'.$idTemp_5.'">';
                    }else{
                        echo '">';
                    }
                    echo '
                    <input type="hidden" name="b" value="'.$board.'">
                    <input type="hidden" name="bn" value="'.$boardNick.'">
                    <div class="modal">
                        <input id="optModal" type="checkbox" />
                        <label for="optModal" class="overlay"></label>
                        <article>
                            <header>
                            <h3>부가 기능</h3>
                            <label for="optModal" class="close">&times;</label>
                            </header>
                            <section class="content">
                                <label>태그 달기 
                                    <select name="category" id="catSel" class="pcwidth">
                                        <option value="기본" selected>기본</option>
                                        <option value="잡담">잡담</option>
                                        <option value="설명">설명</option>';
                                        if($isOwner){
                                        echo '<option value="공지">공지</option>';
                                        }
                                        if($isAdmin){
                                        echo '<option value="보고">보고</option>';
                                        }
                                        echo '<option value="주장">주장</option>
                                        <option value="반박">반박</option>
                                        <option value="조사">조사</option>
                                    </select>';
                                    if($edContent){
                                        echo '<script>
                                            var cat = "'.$edContent['category'].'";
                                            var sel = document.getElementById("catSel");
                                            var selOpt = sel.options;

                                            for (var opt, j = 0; opt = selOpt[j]; j++) {
                                                if (opt.value == cat) {
                                                    sel.selectedIndex = j;
                                                    break;
                                                }
                                            }
                                        </script>';
                                    }
                                echo '</label><br><hr>
                                <label>등급 심의
                                    <select name="rate" id="ratSel" class="pcwidth">
                                        <option value="G">전 연령에 적합</option>
                                        <option value="PG" selected>12세 관람가</option>
                                        <option value="R">15세 관람가</option>
                                    </select>
                                </label><br><hr>
                                <label>서식 적용
                                    <select name="isMd" id="ratSel" class="pcwidth">
                                        <option value="'.$edContent['isMarkdown'].'" selected>선택 안 함</option>
                                        <option value="0">미사용</option>
                                        <option value="1">Markdown</option>
                                    </select>
                                </label><br><hr>';
                                if($edContent){
                                    echo '<script>
                                        var rt = "'.$edContent['rate'].'";
                                        var sel = document.getElementById("ratSel");
                                        var selOpt = sel.options;

                                        for (var opt, j = 0; opt = selOpt[j]; j++) {
                                            if (opt.value == rt) {
                                                sel.selectedIndex = j;
                                                break;
                                            }
                                        }
                                    </script>';
                                }
                                echo '<label>열람 제한
                                    <select id="secSelect" class="pcwidth" onchange="secselect()">
                                        <option value="none" selected>사용 안 함</option>
                                        <option value="myself">나만 보기</option>
                                        <option value="select">사용자 지정</option>
                                    </select>
                                </label><br>
                                <label id="secInputLabel" style="display:none"><br>
                                    <input id="secInput" name="staffOnly" type="text" placeholder="닉네임 입력">
                                    <span class="subInfo">닉네임은 쉼표(,)로 구분하여 주세요.</span><br>
                                    <span class="subInfo">자신의 닉네임이 적혀있지 않아도 됩니다.</span>
                                </label>';
                                if($edContent){ echo '<script>
                                    var sO = "'.$edContent['staffOnly'].'";
                                    var user = "'.$_SESSION['fnUserName'].'";

                                        if(sO == ""){
                                            var public = true;
                                        }else if(sO == user){
                                            var public = false;
                                            var text = "myself";
                                            document.getElementById(\'secInput\').value = user;
                                        }else{
                                            var public = false;
                                            var text = "select";
                                            document.getElementById(\'secInput\').value = sO;
                                            document.getElementById(\'secInputLabel\').style.display = \'\';
                                        }
                                        if(!public){
                                            var sel = document.getElementById("secSelect");
                                            var selOpt = sel.options;

                                            for (var opt, j = 0; opt = selOpt[j]; j++) {
                                                if (opt.value == text) {
                                                    sel.selectedIndex = j;
                                                    break;
                                                }
                                            }
                                        }
                                </script>';
                                }
                                echo '<script>
                                    function secselect() {
                                        if(document.getElementById(\'secSelect\').value == \'select\'){
                                            document.getElementById(\'secInputLabel\').style.display = \'\';
                                        }else if(document.getElementById(\'secSelect\').value == \'myself\'){
                                            document.getElementById(\'secInput\').value = \''.$_SESSION['fnUserName'].'\';
                                        }else{
                                            document.getElementById(\'secInput\').value = null;
                                            document.getElementById(\'secInputLabel\').style.display = \'none\';
                                        }
                                    }
                                </script>
                                <hr>
                                <label for="optModal" class="button success full"><i class="icofont-check"></i> 부가 기능 적용</label><br>
                                <button class="default full" onclick="location.href=\'./b%3Emaint%3E3\'" type="button"><i class="icofont-info-square"></i> 부가기능 사용법</button>
                                <span class="subInfo">부가 기능 사용 방침은 각 게시판마다 다를 수 있습니다.</span><br>
                                <span class="subInfo">열람 제한 기능을 악용하지 마십시오.</span>';
                                echo '<hr>
                                <label>에디터 변경
                                <select name="editor">
                                    <option value="">Textarea</option>
                                    <option value="s">Summernote(권장)</option>
                                </select></label>
                                <button type="submit" formaction="./save.php?yes=please" style="background:green" class="button full"><i class="icofont-edit"></i> 에디터 변경</button><br>
                                <span class="subInfo">Summernote는 글자 서식 용도로만 사용해주세요.</span><br>
                                <span class="subInfo">Textarea 선택시 글쓰기 창에서 이미지 업로드 가능. (업로드 권한 필요)</span>
                                ';
                            echo '</section>
                        </article>
                    </div>';
                    }
                }else{echo '</span></span>';}}elseif($board == 'uita'){echo '<a href="./misc>anonwrite"
                    class="button" style="background:green"><i class="icofont-edit"></i><h-m> 비회원 글쓰기</h-m></a></span></span>';}else{echo '</span></span>';}?>
                <hr>
        <!-- 페이지 추가 로드 -->
        <?php
            if(mb_strpos($kcd, $id) !== FALSE){ #채널 추방 여부
                $sql = "SELECT `at` FROM `_othFunc` WHERE `type` = 'BOARD_KICK' and `value` = '$board' and `target` = '$id' and `at` > NOW() and `isSuccess` = '1'";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) !== 1){ #차단 기간 만료로 인한 해제
                    $sql = "UPDATE `_othFunc` SET `isSuccess` = '0' WHERE `type` = 'BOARD_KICK' and `value` = '$board' and `target` = '$id' and `at` < NOW()";
                    $result = mysqli_query($conn, $sql);
                    $s = $kcd;
                    $s = str_ireplace($id, '', $s);
                    $s = preg_replace('/[,]{2,}/m', ',', $s);
                    $s = preg_replace('/^,/', '', $s);
                    $s = preg_replace('/,$/', '', $s);
                    $sql = "UPDATE `_board` SET `kicked` = '$s' WHERE `slug` = '$board'";
                    $result = mysqli_query($conn, $sql);
                    if($result){
                        die('<script>location.reload()</script>');
                    }else{
                        die('데이터베이스 연결 실패');
                    }
                }else{
                    $row = mysqli_fetch_assoc($result);
                    echo $row['at'].'까지 차단됨! </section><aside class="hidMob"></aside></div><hr></body></html>';
                    exit;
                }
            }
            if(!empty($lsPlus)){
                echo $lsPlus;
            }elseif(!empty($lsSearch)){
                echo $lsSearch;
            }
            if($lsShowPg){
                require 'page.php';
            }
            $boardType = $lsBoard['type'];
            echo '<!-- 게시글 정렬 -->';
            if(!$isEmpty){
                    if($boardType !== 'AUTO_GENER'){ #개별 게시판
                        echo '<table class="list full">
                        <thead>
                            <tr>
                                <th width="10%" class="hidMob">&nbsp;종류</th>
                                <th width="50%">&nbsp;제목</th>
                                <th width="40%">&nbsp;정보</th>
                            </tr>
                        </thead>
                        <tbody>';

                        $sql = "SELECT * FROM `_content` WHERE `category` = '공지' and `board` = '$board' ORDER BY `num` DESC"; #공지 조회
                        $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) != 0){
                            while($row = mysqli_fetch_assoc($result)){

                                if($row['type'] == 'COMMON'){
                                    $pgUser = '<i class="icofont-user-alt-7"></i> <a class="muted" href="./u%3E'.$row['id'].'_'.$board.'">'.$row['name'].'</a>';
                                }else{
                                    $ip_s = preg_replace('/([0-9]+\.[0-9]+)\.[0-9]+\.[0-9]+/i', '$1', $row['ip']);
                                    $pgUser = '<a class="muted tooltip-bottom" data-tooltip="가입하지 않은 사용자입니다."><i class="icofont-invisible"></i>
                                    '.$row['name'].'</a>';
                                }

                                $time = get_timeFlies($row['at']);
                                $media = NULL;
                                if($row['isMedia'] !== NULL){
                                    if($row['isMedia'] == 2){
                                        $media = ' <blue><i class="icofont-ui-video-play "></i></blue>';
                                    }elseif($row['isMedia'] == 3){
                                        $media = ' <red><i class="icofont-youtube-play"></i></red>';
                                    }else{
                                        $media = ' <blue><i class="icofont-image"></i></blue>';
                                    }
                                }
                                echo '<tr>';
                                echo '<td class="hidMob"><b>공지</b></td>';
                                echo '<td class="black noGray"><b><a href="./b%3E'.$row['board'].'%3E'.$row['num'].'">'.textalter(textalter($row['title'], 3), 3).'</a></b>
                                <green class="little">['.$row['commentCount'].']</green>'.$media;
                                if(!empty($row['staffOnly'])){
                                    if(preg_match('/(^|,)'.$name.'($|,)/', $row['staffOnly'])){
                                        $isOpen = TRUE;
                                    }elseif($row['id'] == $id){
                                        $isOpen = TRUE;
                                    }
                                    if($isOpen){
                                        echo '<i class="icofont-unlock"></i>';
                                    }elseif($board !== 'mafia'){
                                        echo '<span class="tooltip-top" data-tooltip="'.$row['staffOnly'].'"><i class="icofont-lock"></i></span>';
                                    }else{
                                        echo '<span class="tooltip-top" data-tooltip="마피아 게임 채널에서는 기밀 대상이 가려집니다."><i class="icofont-lock"></i></span>';
                                    }
                                    $isOpen = FALSE;
                                }
                                echo '</td>';
                                echo '<td class="infoList"> '.$pgUser.'<h-d><br></h-d>';
                                echo ' <i class="icofont-clock-time"></i> '.$time.' <span class="little"><green>('.$row['viewCount'].')</green></span></td>';
                                echo '</tr>';
                                $pgUser = NULL;
                            }
                        }

                        while($row = mysqli_fetch_assoc($listResult)){ #일반 글 조회
                            $time = get_timeFlies($row['at']);
                            $media = NULL;
                            if($row['isMedia'] !== NULL){
                                if($row['isMedia'] == 2){
                                    $media = ' <blue><i class="icofont-ui-video-play "></i></blue>';
                                }elseif($row['isMedia'] == 3){
                                    $media = ' <red><i class="icofont-youtube-play"></i></red>';
                                }else{
                                    $media = ' <blue><i class="icofont-image"></i></blue>';
                                }
                            }

                            if($row['type'] == 'COMMON'){
                                $pgUser = '<i class="icofont-user-alt-7"></i> <a class="muted" href="./u%3E'.$row['id'].'_'.$board.'">'.$row['name'].'</a>';
                            }else{
                                $ip_s = preg_replace('/([0-9]+\.[0-9]+)\.[0-9]+\.[0-9]+/i', '$1', $row['ip']);
                                $pgUser = '<a class="muted tooltip-bottom" data-tooltip="가입하지 않은 사용자입니다."><i class="icofont-invisible"></i>
                                '.$row['name'].'</a>';
                            }

                            echo '<tr>';
                            echo '<td class="hidMob muted">'.$row['category'].'</td>';
                            echo '<td class="black"><a href="./b%3E'.$row['board'].'%3E'.$row['num'].'-'.$lsPg.'">'.textalter($row['title'], 3).'</a>
                            <green class="little">['.$row['commentCount'].']</green>'.$media;
                            if(!empty($row['staffOnly'])){
                                if(preg_match('/(^|,)'.$name.'($|,)/', $row['staffOnly'])){
                                    $isOpen = TRUE;
                                }elseif($row['id'] == $id){
                                    $isOpen = TRUE;
                                }
                                if($isOpen){
                                    echo '<i class="icofont-unlock"></i>';
                                }elseif($board !== 'mafia'){
                                    echo '<span class="tooltip-top" data-tooltip="'.$row['staffOnly'].'"><i class="icofont-lock"></i></span>';
                                }else{
                                    echo '<span class="tooltip-top" data-tooltip="마피아 게임 채널에서는 기밀 대상이 가려집니다."><i class="icofont-lock"></i></span>';
                                }
                                $isOpen = FALSE;
                            }
                            echo '</td>';
                            echo '<td class="infoList"> '.$pgUser.'<h-d><br></h-d>';
                            echo ' <i class="icofont-clock-time"></i> '.$time.' <span class="little" style="font-weight: normal;"><green>('.$row['viewCount'].')</green></span></td>';
                            echo '</tr>';
                            $pgUser = NULL;
                        }

                    }else{ #종합 글 목록
                        echo '<table class="list full">
                        <thead>
                            <tr>
                                <th width="15%" class="hidMob">&nbsp;출처</th>
                                <th width="47%">&nbsp;제목</th>
                                <th width="36%">&nbsp;정보</th>
                            </tr>
                        </thead>
                        <tbody>';

                        include 'siteNotice.php';

                        while($row = mysqli_fetch_assoc($listResult)){ #일반 글 조회
                            $time = get_timeFlies($row['at']);
                            $media = NULL;
                            if($row['isMedia'] !== NULL){
                                if($row['isMedia'] == 2){
                                    $media = ' <blue><i class="icofont-ui-video-play "></i></blue>';
                                }elseif($row['isMedia'] == 3){
                                    $media = ' <red><i class="icofont-youtube-play"></i></red>';
                                }else{
                                    $media = ' <blue><i class="icofont-image"></i></blue>';
                                }
                            }
                            
                            if($row['type'] == 'COMMON'){
                                $pgUser = '<i class="icofont-user-alt-7"></i> <a class="muted" href="./u%3E'.$row['id'].'_'.$board.'">'.$row['name'].'</a>';
                            }else{
                                $ip_s = preg_replace('/([0-9]+\.[0-9]+)\.[0-9]+\.[0-9]+/i', '$1', $row['ip']);
                                $pgUser = '<a class="muted tooltip-bottom" data-tooltip="가입하지 않은 사용자입니다."><i class="icofont-invisible"></i>
                                '.$row['name'].'</a>';
                            }

                            echo '<tr>';
                            echo '<td class="hidMob black"'.$lsAGp.'><a href="b%3E'.$row['board'].'">'.$row['boardName'].'</a></td>';
                            if($row['rate'] == 'R'){
                                echo '<td><a class="subInfo" href="./b%3Erecent%3E'.$row['num'].'-'.$lsPg.'">15세 관람가 게시물입니다.</a>
                                <green class="little">['.$row['commentCount'].']</green>';
                            }else{
                                echo '<td class="black"><a href="./b%3Erecent%3E'.$row['num'].'-'.$lsPg.'">'.textalter($row['title'], 3).'</a>
                                <green class="little">['.$row['commentCount'].']</green>'.$media;
                            }
                                if(!empty($row['staffOnly'])){
                                    if(preg_match('/(^|,)'.$name.'($|,)/', $row['staffOnly'])){
                                        $isOpen = TRUE;
                                    }elseif($row['id'] == $id){
                                        $isOpen = TRUE;
                                    }
                                    if($isOpen){
                                        echo '<i class="icofont-unlock"></i>';
                                    }elseif($row['board'] !== 'mafia'){
                                        echo '<span class="tooltip-top" data-tooltip="'.$row['staffOnly'].'"><i class="icofont-lock"></i></span>';
                                    }else{
                                        echo '<span class="tooltip-top" data-tooltip="마피아 게임 채널에서는 기밀 대상이 가려집니다."><i class="icofont-lock"></i></span>';
                                    }
                                    $isOpen = FALSE;
                                }
                                echo '</td>';
                            echo '<td class="infoList"> '.$pgUser.'<h-d><br></h-d>';
                            echo ' <i class="icofont-clock-time"></i> '.$time.' <span class="little"><green>('.$row['viewCount'].')</green></span></td>';
                            echo '</tr>';
                            $pgUser = NULL;
                        }
                    }

                    echo '</tbody>
                </table>';
?>
                <section class="search">
                    <hr>
                    <form action="./main" method="get">
                        <select name="qm">
                            <option value="both">제목/내용</option>
                            <option value="title">제목</option>
                            <option value="content">내용</option>
                            <option value="author">글쓴이</option>
                            <option value="comment">댓글</option>
                        </select>
                        <input type="text" placeholder="검색어 입력" name="q">
                        <input type="submit" value="검색">
                    </form>
                    <hr>
                </section>
<?php
        echo '<!-- 페이지 이동 -->
                <div class="center">
                    <div class="pagination">
                        <a href="b%3E'.$board.'-1">&laquo;</a>';

                        if($lsBoard['type'] == 'AUTO_GENER'){ #글 수 조회
                            switch ($board) {
                                case 'HOF':
                                    $sql = "SELECT COUNT(`num`) as `cnt` FROM `_content` WHERE `voteCount_UP` - `voteCount_Down` > 19 and `viewCount` > 499 and `commentCount` > 49";
                                    break;
                                case 'trash':
                                    $sql = "SELECT COUNT(`num`) as `cnt` FROM `_content` WHERE `board` = 'trash'";
                                    break;
                    
                                default:
                                    $sql = "SELECT COUNT(`num`) as `cnt` FROM `_content` WHERE `board` NOT LIKE 'trash'";
                                    break;
                            }
                        }else{
                            $sql = "SELECT COUNT(`num`) as `cnt` FROM `_content` WHERE `board` = '$board'";
                        }

                        $result = mysqli_query($conn, $sql);
                        $cntCtt = mysqli_fetch_assoc($result);
                        $cnt = $cntCtt['cnt'];
                        $cnt--;

                        $lsPgEnd = 1;
                        $lsPgCacVal = $cnt / $lsPgN;
                        while($lsPgEnd < $lsPgCacVal){
                            $lsPgEnd++;
                        }

                        $lsPgStart = $lsPg - 2; #시작 페이지 값
                        if($lsPgStart < 1){
                            $lsPgStart = 1;
                        }

                        $i = 1;
                        while ($lsPgStart <= $lsPgEnd) {
                            if($lsPg == $lsPgStart){
                                echo '<a class="active" href="b%3E'.$board.'-'.$lsPgStart.'">'.$lsPgStart.'</a>';
                            }else{
                                echo '<a href="b%3E'.$board.'-'.$lsPgStart.'">'.$lsPgStart.'</a>';
                            }
                            if($i == 5){
                            break;
                            }
                            $lsPgStart++;
                            $i++;
                        }

                        echo '<a href="b%3E'.$board.'-'.$lsPgEnd.'">&raquo;</a>';
                    echo '</div>
                </div>';
            }

            if(!empty($boardOpt)){
                switch ($boardOpt) {
                    case 'NSTAT':
                        echo '<hr><br><hr>';
                        include './sub/nstat.php';
                        break;
                }
            }
        ?>
            </section>
            <aside class="hidMob" id="nofiSec">
                <h-m style="position:absolute;right:2em;margin:9px;opacity:0.7">
            <?php 
        if($uS['hideAdv'] != 1 || $isLogged == FALSE){
            if($boardType !== 'AUTO_GENER'){
                echo '<div class="card" style="width:300px">
                    <header>
                        '.$boardName.'의 인기 게시글
                    </header>
                    <section><table style="font-size:0.8em">';
                $sql = "SELECT * FROM `_content` WHERE `board` = '$board' and `rate` NOT LIKE 'R' and `staffOnly` IS NULL ORDER BY `voteCount_Up` DESC LIMIT 5"; #인기글 조회
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) != 0){
                    while($row = mysqli_fetch_assoc($result)){
                        $time = get_timeFlies($row['at']);
                        echo '<tr>';
                        echo '<td><b><a href="./b%3E'.$row['board'].'%3E'.$row['num'].'" target="_blank">'.textalter($row['title'], 3).'</a></b> <green class="little">['.$row['commentCount'].']</green>';
                        echo '</td>';
                        echo '<td><i class="icofont-user-suited"></i>
                        <a class="muted" target="_blank" href="./u%3E'.$row['id'].'">'.$row['name'].'</a></td>';
                        echo '</tr>';
                    }
                }
                    echo '</table></section>
                </div>';
            }else{
                echo '<div class="card" style="width:300px">
                    <header>
                        '.$fnTitle.' 인기 게시글
                    </header>
                    <section><table style="font-size:0.8em">';
                $sql = "SELECT * FROM `_content` WHERE `rate` NOT LIKE 'R' and `staffOnly` IS NULL ORDER BY `voteCount_Up` DESC LIMIT 5"; #인기글 조회
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) != 0){
                    while($row = mysqli_fetch_assoc($result)){
                        $time = get_timeFlies($row['at']);
                        echo '<tr>';
                        echo '<td><b><a href="./b%3E'.$row['board'].'%3E'.$row['num'].'">'.textalter($row['title'], 3).'</a></b> <green class="little">['.$row['commentCount'].']</green>';
                        echo '</td>';
                        echo '<td><i class="icofont-user-suited"></i>
                        <a class="muted" href="./u%3E'.$row['id'].'">'.$row['name'].'</a></td>';
                        echo '</tr>';
                    }
                }
                    echo '</table></section>
                </div>';
            }
                echo '<div class="card" style="width:300px">
                    <header>
                        광고 <a href="./adv" target="_blank" class="little right">등록하기</a>
                    </header>
                    <section id="advSec"><a href="'.$tnHref.'">'.$tnText.'</a><hr><p style="font-size: 0.7em;text-align:right"><b>광고는 커뮤니티를 지탱하는 기둥입니다.</b><br>
                        우측 카드가 화면을 가릴 경우,<br>
                        \'내 정보\'에서 해제하실 수 있습니다.</p>
                    </section>
                </div>';
        }?>
                </h-m>
            </aside>
        </div>
        <div class="modal">
        <input id="boardInfoModal" type="checkbox" />
        <label for="boardInfoModal" class="overlay"></label>
            <article>
                <header>
                <h3>게시판 정보</h3>
                <label for="boardInfoModal" class="close">&times;</label>
                </header>
                <section class="content">
                    소유주 : <a href="./u%3E<?=$ownerId?>">@<?=$ownerName?></a><br><hr>
                    연관 게시판 : <span class="subInfo">기능 준비중..</span><hr>
                <?php
                if($isAdmin){
                    echo '채널 이양 :<br><form action="./php/mkBoard.php" method="post"><input type="text" name="tsfId" placeholder="id"><br>
                    <input type="submit" class="full" value="소유권 넘기기">
                    <input type="hidden" name="tsfSlug" value="'.$board.'"></form><hr>';
                }
                ?>
                </section>
                <?php
                if($isLogged){
                    if($board != 'recent'){
                        echo '<footer>
                        <form method="post" action="./php/sub.php">';
                        echo '<input type="hidden" name="board" value="'.$board.'">';
                        if($uS['subs'] == NULL){
                            $isSubed = FALSE;
                        }else{
                            if(preg_match('/'.$board.'/', $uS['subs'])){
                                $isSubed = TRUE;
                            }else{
                                $isSubed = FALSE;
                            }
                        }
                        if(!$isSubed){
                        echo '<button class="outline" style="width:100%">구독하기</button>';
                        }else{
                        echo '<button class="outline-blue" style="width:100%">구독 취소</button>';
                        }
                        echo '</form>
                        </footer>';
                    }
                }
                ?>
            </article>
        </div>
    </main>