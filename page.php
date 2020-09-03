<?php
    $pgNum = filt($_GET['n'], '123');
    $pgPage = filt($_GET['p'], '123');
    if(empty($pgPage) or $pgPage == '0'){
        $pgPage = 1;
    }
    $pgBoard = $board;

    if(in_array($pgBoard, array('recent', 'whole', 'HOF'))){ #게시글 표시
        $sql = "SELECT * FROM `_content` WHERE `num` = '$pgNum' and `type` IN ('COMMON', 'ANON_WRITE')";
        $isRCT = TRUE;
    }else{
        $sql = "SELECT * FROM `_content` WHERE `board` = '$pgBoard' and `num` = '$pgNum' and `type` IN ('COMMON', 'ANON_WRITE')";
    }
    $contResult = mysqli_query($conn, $sql);
    if(mysqli_num_rows($contResult) == 1){
        $isWrong = FALSE;
        $sql = "UPDATE `_content` SET `viewCount` = `viewCount` + 1 WHERE `num` = $pgNum";
        $result = mysqli_query($conn, $sql);
        if($result){
            $pgContent = mysqli_fetch_assoc($contResult);
        }
    }else{
        $isWrong = TRUE;
        $idAlert = 'wrongContent';
    }
    if(strtolower($pgContent['id']) == strtolower($_SESSION['fnUserId'])){
        $isMe = TRUE;
    }
    $so = $pgContent['staffOnly'];
    $name = $_SESSION['fnUserName'];

    if($so !== NULL){
        if($_SESSION['fnUserId'] == NULL){
            echo '</section><aside></aside>';
            die('<script>alert("글을 열람할 권한이 없습니다.");window.location.href = document.referrer;</script>');
        }
        if(!preg_match('/(^|,)'.$name.'($|,)/i', $so)){
            if(strtolower($id) !== strtolower($pgContent['id'])){
                if(!$isAdmin){
                    if(!$isStaff){
                        echo '</section><aside></aside>';
                        die('<script>alert("글을 열람할 권한이 없습니다.");window.location.href = document.referrer;</script>');
                    }
                }
            }
        }
    }
        $at = $pgContent['at'];
        $rt = $pgContent['rate'];
        $cat = $pgContent['category'];
        $vc = $pgContent['viewCount'];
        $cc = $pgContent['commentCount'];
        $uv = $pgContent['voteCount_Up'];
        $dv = $pgContent['voteCount_Down'];
        $iE = $pgContent['isEdited'];
        $eU = $pgContent['whoEdited'];
        $im = $pgContent['isMarkdown'];
        if($pgContent['offNotify'] == 1){
            $offNotify = TRUE;
            $onC = 'gainsboro;color:black';
            $onT = '꺼짐';
            $onS = '이 글에 달린 댓글 알림을 받습니다.';
        }else{
            $offNotify = FALSE;
            $onC = 'labenderblush;color:white';
            $onT = '켜짐';
            $onS = '이 글에 달린 댓글 알림을 받지 않습니다.';
        }
        $cac = $uv - $dv;
        if($cac < -1){
            $em = '<red>반대함</red>';
        }elseif($cac > 1){
            $em = '<green>동의함</green>';
        }elseif($cac > 10){
            $em = '<blue>적극 동의</blue>';
        }else{
            $em = '중립적';
        }

        if($pgContent['type'] == 'COMMON'){
            $pgUser = '<i class="icofont-user-alt-7"></i> <a class="muted" href="/u/'.$pgContent['id'].'_'.$board.'">'.$pgContent['name'].'</a>';
        }else{
            $ip_s = preg_replace('/([0-9]+\.[0-9]+)\.[0-9]+\.[0-9]+/i', '$1', $pgContent['ip']);
            $pgUser = '<a class="muted tooltip-bottom" data-tooltip="가입하지 않은 사용자입니다."><i class="icofont-invisible"></i>
            '.$pgContent['name'].' ('.$ip_s.')'.'</a>';
        }

        echo '<script>document.title += " - '.$pgContent['title'].'"</script>'; #제목 변경
?>
                <div class="card">
                    <header style="background:#f3f3f3;border-bottom:1px solid #e6e6e6">
                        <h3 id="title"><?=textalter($pgContent['title'], 3)?></h3><br>
                        <span class="subInfo"><?=$pgUser?>
                        <i class="icofont-clock-time"></i> <?=get_timeFlies($pgContent['at'])?>
                        <?php if($pgContent['category'] == NULL){$pgContent['category'] = '기본';}
                        if($isRCT){echo '<a class="muted" href="/b/'.$pgContent['board'].'"><i class="icofont-folder"></i> '.$pgContent['boardName'].'</a>';}
                        else{echo '<i class="icofont-tag"></i> <a class="muted" href="/b/'.$pgContent['board'].'/tags_'.$pgContent['category'].'">'.$pgContent['category'].'</a>';} ?>
                        | 반응 : <?=$em?> | <a href="#comment">댓글 <green id="commnt"><?=$cc?></a></green>
                        </span>
                    </header>
                    <article class="mainCon" id="mainCon">
                        <p><?php
                        if($isWrong){
                            echo '글이 삭제되었거나, 주소가 잘못되었거나, 데이터베이스 연결 오류입니다.';
                        }else{
                            if($im == 1){
                                $cont = textAlter($pgContent['content'], 2);
                            }else{
                                $cont = textAlter($pgContent['content'], 1);
                            }
                            echo nl2br($cont);
                        }
                        ?></p>
                    </article>
                    <footer style="font-size:0.75em;">
                        <form method="post" action="/index.php?mode=edit&board=<?=$board?>">
                        <?php
                        echo '<label for="infoModal" class="button" style="background:#6633FF"><i class="icofont-info-square"></i><h-m> 글 정보</h-m></label>';
                        if($isRCT){
                            echo ' <a class="button" href="/b/'.$pgContent['board'].'/'.$pgContent['num'].'"><i class="icofont-archive"></i><h-m> 원글보기</h-m></a>';
                            echo ' <a class="button" style="background:green" href="/b/'.$pgContent['board'].'>write"><i class="icofont-edit"></i><h-m> 글쓰기</h-m></a>';
                        }elseif($isMe){
                            echo ' <button style="background:#a8a8a8" type="submit"><i class="icofont-eraser"></i><h-m> 수정</h-m></button>';
                        }
                        ?>
                        <span class="right">
                        <?php
                    if($isMe && !$isRCT){
                            if($pgContent['commentCount'] > 0){
                                echo '<a class="button tooltip-top" style="background:'.$onC.';" href="/php/mute.php?n='.$pgNum.'&o='.$pgContent['offNotify'].'"
                                data-tooltip="'.$onS.'"><i class="icofont-volume-mute"></i><h-m> 알림 '.$onT.'</h-m></a>';
                            }else{
                                echo '<button class="error tooltip-top" formaction="/index.php?mode=delete&board='.$board.'"
                                type="submit"><i class="icofont-bin"></i><h-m> 삭제</h-m></button>';
                            }
                    }/*else{
                        echo '<a class="button error" href="/b/maint>write"><i class="icofont-exclamation-circle"></i><h-m> 신고</h-m></a>';
                    }*/
                        ?>
                        <button class="warning" formaction="/php/push.php?mode=un"><i class="icofont-thumbs-down"></i> 반대</button> 
                        <button class="success" formaction="/php/push.php"><i class="icofont-thumbs-up"></i> 동의</button>
                        </span>
                        <input type="hidden" name="t" value="<?=$pgContent['title']?>">
                        <input type="hidden" name="n" value="<?=$pgNum?>">
                        </form>
                        <?php
                            if($board == 'quiz'){
                                $sql = "SELECT `isSuccess`, `value`, `target`, `reason` FROM `_othFunc` WHERE `type` = 'QUIZ_QUEST' and `target` LIKE '$pgNum'";
                                $result = mysqli_query($conn, $sql);
                                if(mysqli_num_rows($result) == 1){
                                    $row = mysqli_fetch_assoc($result);
                                    if($row['isSuccess'] or $isMe){
                                        if($row['isSuccess']){
                                            $row['reason'] = '<strike style="muted">'.$row['reason'].'</strike>';
                                        }else{
                                            $row['reason'] = '<span>'.$row['reason'].'</span>';
                                        }
                                        $val = '<br>정답: '.$row['value'];
                                    }
                                    echo '<hr>상금: '.$row['reason'].'ⓟ'.$val;
                                }
                            }
                        ?>
                    </footer>
                </div>
            <?php
            if($isStaff || $isAdmin){
                if($cat == '공지'){
                    $ntc = '해제';
                }else{
                    $ntc = '지정';
                }
                if($rt == 'R'){
                    $rtT = 'PG등급';
                    $rtC = 'green';
                    $rtI = 'gavel';
                }else{
                    $rtT = 'R등급';
                    $rtC = 'danger';
                    $rtI = 'weed';
                }
echo '<!-- 글 관리 -->
                <section class="modifyCon">
                  <form method="post" action="/php/modifyCon.php">
                    <button class="outline-'.$rtC.'" formaction="/php/modifyCon.php?mode=R"><i class="icofont-'.$rtI.'"></i> '.$rtT.'<h-m> 부여</h-m></button>
                    <button class="outline-blue"><i class="icofont-megaphone"></i> <h-m>공지 </h-m>'.$ntc.'</button>
                    <button class="outline right" formaction="/php/modifyCon.php?mode=B"><i class="icofont-close-squared-alt"></i> 블라인드</button>';
                    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
                    $result = mysqli_query($conn, $sql);
                    $iA = mysqli_fetch_assoc($result);
                    if($iA['isAdmin']){
                        echo '<h-d><br></h-d><input type="text" name="m" style="display:inline;width:6em" placeholder="slug">
                        <input type="submit" formaction="/php/modifyCon.php?mode=M" style="display:inline" value="이동">';
                        if($board == 'trash'){
                            echo ' <span class="muted">'.$pgContent['boardName'].'</span>';
                        }
                        if($pgContent['type'] == 'ANON_WRITE'){
                            $sqls = "SELECT * FROM `_ipban` WHERE `ip` = '".$pgContent['ip']."'";
                            $results = mysqli_query($conn, $sqls);
                            if(mysqli_num_rows($results) > 0){
                                echo ' <a class="button error" href="/sub/ipban.php?ip='.$pgContent['ip'].'">차단됨</a>';
                            }else{
                                echo ' <a class="button error" href="/sub/ipban.php?ip='.$pgContent['ip'].'">ip 차단</a>';
                            }
                        }
                    }
                    if(!$isMe){
                        if(mb_strpos($kcd, $pgContent['id']) === FALSE){
                            echo '<span class="right">&nbsp;<button class="outline-danger" formaction="/php/modifyCon.php?mode=K"><i class="icofont-ban"></i> 차단</button><h-m>&nbsp;</h-m></span>
                            <input type="hidden" name="i" value="'.$pgContent['id'].'">';
                        }else{
                            echo '<span class="right">&nbsp;<button class="outline-green" formaction="/php/modifyCon.php?mode=K"><i class="icofont-gavel"></i> 차단 해제</button><h-m>&nbsp;</h-m></span>
                            <input type="hidden" name="i" value="'.$pgContent['id'].'">';
                        }
                    }
                    echo '<select name="kT" style="background:none;float:right;width:4em;margin-top:4px">
                        <option value="1440">1일</option>
                        <option value="4320">3일</option>
                        <option value="10080">7일</option>
                        <option value="525600">1년</option>
                        <option value="10">10분</option>
                        <option value="30">30분</option>
                        <option value="60">1시간</option>
                        <option value="360">6시간</option>
                        <option value="43800">1개월</option>
                        <option value="262800">6개월</option>
                        <option value="10000000">무기한</option>
                    </select>
                    <input type="hidden" name="n" value="'.$pgNum.'">
                    <input type="hidden" name="b" value="'.$board.'">
                  </form>
                </section>';
            }
            ?>
<!-- 댓글 -->
                <div class="card" id="comment">
                    <header style="background:#f3f3f3;border-bottom:1px solid #e6e6e6">
                        <h4><i class="icofont-comment"></i> 댓글 <green>[ <?=$cc?> ]</green></h4>
                    </header>
<!-- 댓글 처리 -->
                    <?php
                $sql = "SELECT * FROM `_comment` WHERE `type` in ('COMMON_CMT', 'FNBCON_CMT') and `from` = '$pgNum'";
                $cmtResult = mysqli_query($conn, $sql);
                $sql = "SELECT * FROM `_comment` WHERE `from` = '$pgNum'";
                $cmtfResult = mysqli_query($conn, $sql);
            if(empty($_SESSION['fnUserId']) or $_SESSION['fnUserId'] == '0'){
                echo '<section class="muted" style="font-size:0.9em;padding:5px;border-bottom:1px solid #f5f5f5">
                    댓글 작성을 위해서는 <a href="/login"><i class="icofont-sign-in"></i> 로그인</a>이 필요합니다.
                </section>';
                while($cmtRow = mysqli_fetch_assoc($cmtResult)){
                    //비로그인 댓글 로딩
                    echo '<section class="comm" id="cmt-'.$cmtRow['num'].'">';
                            echo '<div class="cimg">
                                <img src="';
                                echo get_gravatar($cmtRow['mail'], 56, 'identicon', 'pg');
                            echo '"></div>';
                        echo '<div class="card">
                            <header>
                                <span class="subInfo">
                                    &nbsp;<i class="icofont-user-alt-7"></i>
                                    <a class="muted" href="/u/'.$cmtRow['id'].'">'.$cmtRow['name'].'</a><h-d><br></h-d>&nbsp;';
                                    echo '<i class="icofont-clock-time"></i> '.get_timeFlies($cmtRow['at']);
                                if($cmtRow['isEdited']){
                                    echo ' <span data-tooltip="'.$cmtRow['isEdited'].' / '.$cmtRow['whoEdited'].'"
                                    class="tooltip-bottom"><i class="icofont-eraser"></i> 수정됨</span>';
                                }
                                echo '</span>
                            </header>
                            <section class="conText '.$cacc.'">';
                                if($cmtRow['type'] == 'FNBCON_CMT'){
                                    echo '<img height="160" onclick="viewFNBCON(\''.$cmtRow['content'].'\')" src="/fnbcon/'.$cmtRow['content'].'">';
                                }else{
                                    echo textAlter(nl2br($cmtRow['content']));
                                }
                            echo '</section></div></section>';

                            //2차
                            $parNum = $cmtRow['num'];
                            $rpSql = "SELECT * FROM `_comment` WHERE `type` IN ('COMMON_REP', 'FNBCON_REP') and `from` = '$pgNum' and `childOf` = '$parNum'";
                            $rpResult = mysqli_query($conn, $rpSql);
                            if(mysqli_num_rows($rpResult) !== 0){
                                while($rpRow = mysqli_fetch_assoc($rpResult)){
                                    echo '<section class="comm step_2" id="cmt-'.$rpRow['num'].'">';
                                    echo '<div class="cimg">
                                        <img src="';
                                        echo get_gravatar($rpRow['mail'], 56, 'identicon', 'pg');
                                            echo '"></div>';
                                        echo '<div class="card">
                                            <header>
                                                <span class="subInfo">
                                                    &nbsp;<i class="icofont-user-alt-7"></i>
                                                    <a class="muted" href="/u/'.$rpRow['id'].'">'.$rpRow['name'].'</a><h-d><br></h-d>&nbsp;';
                                                    echo '<i class="icofont-clock-time"></i> '.get_timeFlies($rpRow['at']);
                                                if($rpRow['isEdited']){
                                                    echo ' <span data-tooltip="'.$rpRow['isEdited'].' / '.$rpRow['whoEdited'].'"
                                                    class="tooltip-bottom"><i class="icofont-eraser"></i> 수정됨</span>';
                                                }
                                                echo '</span>
                                            </header>
                                            <section class="conText '.$cacc.'">';
                                                if($rpRow['type'] == 'FNBCON_REP'){
                                                    echo '<img height="160" onclick="viewFNBCON(\''.$rpRow['content'].'\')" src="/fnbcon/'.$rpRow['content'].'">';
                                                }else{
                                                    echo textAlter(nl2br($rpRow['content']));
                                                }
                                            echo '</section></div></section>';

                                            //3차
                                            $parNum = $rpRow['num'];
                                            $rplSql = "SELECT * FROM `_comment` WHERE `type` IN ('COMMON_REP', 'FNBCON_REP') and `from` = '$pgNum' and `childOf` = '$parNum'";
                                            $rpResult = mysqli_query($conn, $rplSql);
                                            if(mysqli_num_rows($rpResult) !== 0){
                                                while($rplRow = mysqli_fetch_assoc($rpResult)){
                                                    echo '<section class="comm step_3" id="cmt-'.$rplRow['num'].'">';
                                                    echo '<div class="cimg">
                                                        <img src="';
                                                        echo get_gravatar($rplRow['mail'], 56, 'identicon', 'pg');
                                                            echo '"></div>';
                                                        echo '<div class="card">
                                                            <header>
                                                                <span class="subInfo">
                                                                    &nbsp;<i class="icofont-user-alt-7"></i>
                                                                    <a class="muted" href="/u/'.$rplRow['id'].'">'.$rplRow['name'].'</a><h-d><br></h-d>&nbsp;';
                                                                    echo '<i class="icofont-clock-time"></i> '.get_timeFlies($rplRow['at']);
                                                                if($rplRow['isEdited']){
                                                                    echo ' <span data-tooltip="'.$rplRow['isEdited'].' / '.$rplRow['whoEdited'].'"
                                                                    class="tooltip-bottom"><i class="icofont-eraser"></i> 수정됨</span>';
                                                                }
                                                                echo '</span>
                                                            </header>
                                                            <section class="conText '.$cacc.'">';
                                                                if($rplRow['type'] == 'FNBCON_REP'){
                                                                    echo '<img height="160" onclick="viewFNBCON(\''.$rplRow['content'].'\')" src="/fnbcon/'.$rplRow['content'].'">';
                                                                }else{
                                                                    echo textAlter(nl2br($rplRow['content']));
                                                                }
                                                            echo '</section></div></section>';
                                                }
                                            }
                                    }
                                }
                }
                echo '</div>';
            }else{
                if(mysqli_num_rows($cmtfResult) == 0){
                    echo '
                    <section class="muted">
                        댓글이 없습니다.
                    </section><hr>
                    ';
                }elseif(mysqli_num_rows($cmtfResult) >= 200){
                    echo '
                    <section class="muted">
                    댓글이 200개 이상입니다.<br>
                    <a href="/comment_'.$pgNum.'"><i class="icofont-page"></i> 댓글 전체 목록</a> 보기<br>
                    <span class="subInfo">브라우저가 정지하거나 기기에 문제를 일으킬 수 있습니다.</span>
                    </section><hr>
                    ';
                }else{
                    $cmtfResult = FALSE;
                    $userMail = get_gravatar($_SESSION['fnUserMail'], 56, 'identicon', 'pg'); #회원 메일 불러오기

                    while($cmtRow = mysqli_fetch_assoc($cmtResult)){
                        $isMe = FALSE;
                        if($cmtRow['id'] == $_SESSION['fnUserId']){
                            $isMe = TRUE;
                        }
                        //댓글 로딩 (1차)
                        $cac = NULL;
                        $cacc = NULL;
                        $cac = $cmtRow['voteCount_Up'] - $cmtRow['voteCount_Down'];
                        if($cac > 4){
                            $cacc = ' upv';
                        }elseif($cac < -4){
                            $cacc = ' dov';
                        }
                        echo '<section class="comm" id="cmt-'.$cmtRow['num'].'">';
                            echo '<div class="cimg">
                                <img src="';
                                echo get_gravatar($cmtRow['mail'], 56, 'identicon', 'pg');
                            echo '"></div>';
                        echo '<div class="card">
                            <header>
                                <span class="subInfo">
                                    &nbsp;<i class="icofont-user-alt-7"></i>
                                    <a class="muted" href="/u/'.$cmtRow['id'].'">'.$cmtRow['name'].'</a><h-d><br></h-d>&nbsp;';
                                    echo '<i class="icofont-clock-time"></i> '.get_timeFlies($cmtRow['at']);
                                if($cmtRow['isEdited']){
                                    echo ' <span data-tooltip="'.$cmtRow['isEdited'].' / '.$cmtRow['whoEdited'].'"
                                    class="tooltip-bottom"><i class="icofont-eraser"></i> 수정됨</span>';
                                }
                                echo '</span>
                            </header>
                            <section class="conText '.$cacc.'">';
                                if($cmtRow['type'] == 'FNBCON_CMT'){
                                    echo '<img height="160" onclick="viewFNBCON(\''.$cmtRow['content'].'\')" src="/fnbcon/'.$cmtRow['content'].'">';
                                }else{
                                    echo textAlter(nl2br($cmtRow['content']));
                                }
                            echo '</section>
                            <footer><form method="post" action="/comment.php?m=edit">';
                            if($isMe){
                                if($cmtRow['type'] == 'FNBCON_CMT'){
                                    echo '<button onclick="location.href = \'/comment.php?m=delete&n='.$cmtRow['num'].'&parentNum='.$pgNum.'\'"
                                    style="background:red" type="button"><i class="icofont-bin"></i><h-m> 삭제</h-m></button> ';
                                }else{
                                    echo '<button onclick="editC('.$cmtRow['num'].')" id="ediB'.$cmtRow['num'].'"
                                    style="background:#a8a8a8" type="button"><i class="icofont-eraser"></i><h-m> 수정</h-m></button> ';
                                }
                            }elseif($isStaff){
                                echo '<button class="error" type="submit" formaction="/php/blame.php"><i class="icofont-ban"></i> 차단</button> ';
                                echo '<input type="hidden" name="board" value="'.$board.'"><input type="hidden" name="target" value="'.$cmtRow['id'].'">';
                            }
                                echo '<button class="warning" formaction="/php/push.php?mode=un&n='.$cmtRow['num'].'"><i class="icofont-thumbs-down"></i> '.$cmtRow['voteCount_Down'].'</button>
                                <button class="success" formaction="/php/push.php?n='.$cmtRow['num'].'"><i class="icofont-thumbs-up"></i> '.$cmtRow['voteCount_Up'].'</button>
                                <span class="right">
                                <button onclick="addRp('.$cmtRow['num'].')" id="addR'.$cmtRow['num'].'" type="button"><i class="icofont-comment"></i><h-m> 답글 달기</h-m></button>
                                </span></form>
                            </footer>
                        </div>
                    </section>';
                        //답글 창 로딩
                        $parNum = $cmtRow['num'];
                        echo '<section class="comm step_1" id="reply-'.$cmtRow['num'].'" style="display:none"><form method="post" action="/comment.php?m=reply">
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
                                    <span><textarea maxlength="1000" style="height:5em" onkeydown="ctrSM('.$cmtRow['num'].')" id="txtA'.$cmtRow['num'].'" name="reply" placeholder="댓글 작성"></textarea></span>
                                    <input type="hidden" name="childOf" value="'.$parNum.'">
                                    <input type="hidden" name="parentNum" value="'.$parNum.'">
                                    <input type="hidden" name="n" value="'.$pgNum.'">
                                </section>
                                <footer>
                                    <button style="width:100%;background:green" type="submit" id="addB'.$cmtRow['num'].'"><i class="icofont-check"></i> 작성 완료</button>
                                </footer>
                                <input type="hidden" name="t" value="'.$pgContent['title'].'">
                                <input type="hidden" name="i" value="'.$cmtRow['id'].'">
                                <input type="hidden" name="v" value="b>'.$board.'>'.$pgNum.'-'.$pgPage.'">
                                <span id="fnbCA'.$cmtRow['num'].'"></span>
                            </div>
                        </form></section>';
                    if($isMe){
                        //수정 창 로딩
                        $parNum = $cmtRow['num'];
                        $ccnt = NULL;
                        $ccnt = preg_replace('/(<a href=".*">|<\/a>)/', '', $cmtRow['content']);
                        echo '<section class="comm step_1" id="editC-'.$cmtRow['num'].'" style="display:none"><form method="post" action="/comment.php?m=edit">
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
                                    <textarea maxlength="1000" style="height:5em" onkeydown="ctrSMe('.$cmtRow['num'].')" id="txtE'.$cmtRow['num'].'" name="reply" placeholder="댓글 작성">'.preg_replace('/<span .+>.+<\/span>/mu', '', $ccnt).'</textarea>
                                    <input type="hidden" name="n" value="'.$cmtRow['num'].'">
                                    <input type="hidden" name="fn" value="'.$pgNum.'">
                                </section>
                                <footer>
                                    <button style="width:100%;background:#a8a8a8" type="submit" id="edcB'.$cmtRow['num'].'"
                                    data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top"><i class="icofont-check"></i> 작성 완료</button>
                                </footer>
                            </div>
                        </form></section>';
                    }
                                //답글 로딩 (2차)
                                $rpSql = "SELECT * FROM `_comment` WHERE `type` IN ('COMMON_REP', 'FNBCON_REP') and `from` = '$pgNum' and `childOf` = '$parNum'";
                                $rpResult = mysqli_query($conn, $rpSql);
                                if(mysqli_num_rows($rpResult) !== 0){
                                    while($rpRow = mysqli_fetch_assoc($rpResult)){
                                        $isMe = FALSE;
                                        if($rpRow['id'] == $_SESSION['fnUserId']){
                                            $isMe = TRUE;
                                        }
                                        $cacc = NULL;
                                        $cac = NULL;
                                        $cac = $rpRow['voteCount_Up'] - $rpRow['voteCount_Down'];
                                        if($cac > 4){
                                            $cacc = ' upv';
                                        }elseif($cac < -4){
                                            $cacc = ' dov';
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
                                                    <a class="muted" href="/u/'.$rpRow['id'].'">'.$rpRow['name'].'</a><h-d><br></h-d>&nbsp;';
                                                    echo '<i class="icofont-clock-time"></i> '.get_timeFlies($rpRow['at']);
                                                if($rpRow['isEdited']){
                                                    echo ' <span data-tooltip="'.$rpRow['isEdited'].' / '.$rpRow['whoEdited'].'"
                                                    class="tooltip-bottom"><i class="icofont-eraser"></i> 수정됨</span>';
                                                }
                                                echo '</span></header>
                                            <section class="conText">';
                                                if($rpRow['type'] == 'FNBCON_REP'){
                                                    echo '<img height="110" onclick="viewFNBCON(\''.$rpRow['content'].'\')" src="/fnbcon/'.$rpRow['content'].'">';
                                                }else{
                                                    echo textAlter(nl2br($rpRow['content']));
                                                }
                                            echo '</section>
                                            <footer><form method="post" action="/comment.php?m=edit">';
                                            if($isMe){
                                                echo '<button onclick="editC('.$rpRow['num'].')" id="ediB'.$rpRow['num'].'"
                                                style="background:#a8a8a8" type="button"><i class="icofont-eraser"></i><h-m> 수정</h-m></button> ';
                                            }elseif($isStaff){
                                                echo '<button class="error" type="submit" formaction="/php/blame.php"><i class="icofont-ban"></i> 차단</button> ';
                                                echo '<input type="hidden" name="board" value="'.$board.'"><input type="hidden" name="target" value="'.$rpRow['id'].'">';
                                            }
                                                echo '<button class="warning" formaction="/php/push.php?mode=un&n='.$rpRow['num'].'"><i class="icofont-thumbs-down"></i> '.$rpRow['voteCount_Down'].'</button>
                                                <button class="success" formaction="/php/push.php?n='.$rpRow['num'].'"><i class="icofont-thumbs-up"></i> '.$rpRow['voteCount_Up'].'</button>
                                                <span class="right">
                                                <button onclick="addRp('.$rpRow['num'].')" id="addR'.$rpRow['num'].'" type="button"><i class="icofont-comment"></i><h-m> 답글 달기</h-m></button>
                                                </span></form>
                                            </footer>
                                        </div>
                                    </section>';
                                        //답글 창 로딩
                                        $parNum = $rpRow['num'];
                                        echo '<section class="comm step_2" id="reply-'.$rpRow['num'].'" style="display:none"><form method="post" action="/comment.php?m=reply">
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
                                                    <span><textarea maxlength="1000" style="height:5em" onkeydown="ctrSM('.$rpRow['num'].')" id="txtA'.$rpRow['num'].'" name="reply" placeholder="댓글 작성"></textarea></span>
                                                    <input type="hidden" name="childOf" value="'.$parNum.'">
                                                    <input type="hidden" name="parentNum" value="'.$parNum.'">
                                                    <input type="hidden" name="n" value="'.$pgNum.'">
                                                </section>
                                                <footer>
                                                    <button style="width:100%;background:green" type="submit" id="addB'.$rpRow['num'].'"
                                                    data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top"><i class="icofont-check"></i> 작성 완료</button>
                                                </footer>
                                            </div>
                                            <input type="hidden" name="t" value="'.$pgContent['title'].'">
                                            <input type="hidden" name="i" value="'.$rpRow['id'].'">
                                            <input type="hidden" name="v" value="b>'.$board.'>'.$pgNum.'-'.$pgPage.'">
                                            <span id="fnbCA'.$rpRow['num'].'"></span>
                                        </form></section>';
                                    if($isMe){
                                        //수정 창 로딩
                                        $ccnt = NULL;
                                        $ccnt = preg_replace('/(<a href=".*">|<\/a>)/', '', $rpRow['content']);
                                        echo '<section class="comm step_2" id="editC-'.$rpRow['num'].'" style="display:none"><form method="post" action="/comment.php?m=edit">
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
                                                    <textarea maxlength="1000" style="height:5em" onkeydown="ctrSMe('.$rpRow['num'].')" id="txtE'.$rpRow['num'].'" name="reply" placeholder="댓글 작성">'.$ccnt.'</textarea>
                                                    <input type="hidden" name="n" value="'.$rpRow['num'].'">
                                                    <input type="hidden" name="fn" value="'.$pgNum.'">
                                                </section>
                                                <footer>
                                                    <button style="width:100%;background:#a8a8a8" type="submit" id="edcB'.$rpRow['num'].'"
                                                    data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top"><i class="icofont-check"></i> 작성 완료</button>
                                                </footer>
                                            </div>
                                        </form></section>';
                                    }
                                                //답글 로딩 (3차)
                                                $rplSql = "SELECT * FROM `_comment` WHERE `type` IN ('COMMON_REP', 'FNBCON_REP') and `from` = '$pgNum' and `parentNum` = '$parNum'";
                                                $rplResult = mysqli_query($conn, $rplSql);
                                                if(mysqli_num_rows($rplResult) !== 0){
                                                    while($rplRow = mysqli_fetch_assoc($rplResult)){
                                                        $isMe = FALSE;
                                                        if($rplRow['id'] == $_SESSION['fnUserId']){
                                                            $isMe = TRUE;
                                                        }
                                                        $cacc = NULL;
                                                        $cac = NULL;
                                                        $cac = $rplRow['voteCount_Up'] - $rplRow['voteCount_Down'];
                                                        if($cac > 4){
                                                            $cacc = ' upv';
                                                        }elseif($cac < -4){
                                                            $cacc = ' dov';
                                                        }
                                                        echo '<section class="comm step_2" id="cmt-'.$rplRow['num'].'">';
                                                            echo '<div class="cimg">
                                                                <img src="';
                                                                echo get_gravatar($rplRow['mail'], 56, 'identicon', 'pg');
                                                            echo '"></div>';
                                                        echo '<div class="card">
                                                            <header>
                                                                <span class="subInfo">';
                                                                if($rplRow['parentNum'] != $rplRow['childOf']){
                                                                    echo '<a onclick="cmtHR(\''.$rplRow['childOf'].'\')"><i class="icofont-share-alt"></i></a> ';
                                                                }
                                                                    echo '&nbsp;<i class="icofont-user-alt-7"></i>
                                                                    <a class="muted" href="/u/'.$rplRow['id'].'">'.$rplRow['name'].'</a><h-d><br></h-d>&nbsp;';
                                                                    echo '<i class="icofont-clock-time"></i> '.get_timeFlies($rplRow['at']);
                                                                if($rplRow['isEdited']){
                                                                    echo ' <span data-tooltip="'.$rplRow['isEdited'].' / '.$rplRow['whoEdited'].'"
                                                                    class="tooltip-bottom"><i class="icofont-eraser"></i> 수정됨</span>';
                                                                }
                                                                echo '</span></header>
                                                            <section class="conText">';
                                                                if($rplRow['type'] == 'FNBCON_REP'){
                                                                    echo '<img height="100" onclick="viewFNBCON(\''.$rplRow['content'].'\')" src="/fnbcon/'.$rplRow['content'].'">';
                                                                }else{
                                                                    echo textAlter(nl2br($rplRow['content']));
                                                                }
                                                            echo '</section>
                                                            <footer><form method="post" action="/comment.php?m=edit">';
                                                            if($isMe){
                                                                echo '<button onclick="editC('.$rplRow['num'].')" id="ediB'.$rplRow['num'].'"
                                                                style="background:#a8a8a8" type="button"><i class="icofont-eraser"></i><h-m> 수정</h-m></button> ';
                                                            }elseif($isStaff){
                                                                echo '<button class="error" type="submit" formaction="/php/blame.php"><i class="icofont-ban"></i> 차단</button> ';
                                                                echo '<input type="hidden" name="board" value="'.$board.'"><input type="hidden" name="target" value="'.$rplRow['id'].'">';
                                                            }
                                                                echo '<button class="warning" formaction="/php/push.php?mode=un&n='.$rplRow['num'].'"><i class="icofont-thumbs-down"></i> '.$rplRow['voteCount_Down'].'</button>
                                                                <button class="success" formaction="/php/push.php?n='.$rplRow['num'].'"><i class="icofont-thumbs-up"></i> '.$rplRow['voteCount_Up'].'</button>
                                                                <span class="right">
                                                                <button onclick="addRp('.$rplRow['num'].')" id="addR'.$rplRow['num'].'" type="button"><i class="icofont-comment"></i><h-m> 답글 달기</h-m></button>
                                                                </span></form>
                                                            </footer>
                                                        </div>
                                                    </section>';
                                                        //답글 창 로딩
                                                        echo '<section class="comm step_3" id="reply-'.$rplRow['num'].'" style="display:none"><form method="post" action="/comment.php?m=reply">
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
                                                                    <span><textarea maxlength="1000" style="height:5em" onkeydown="ctrSM('.$rplRow['num'].')" id="txtA'.$rplRow['num'].'" name="reply" placeholder="댓글 작성"></textarea></span>
                                                                    <input type="hidden" name="childOf" value="'.$rplRow['num'].'">
                                                                    <input type="hidden" name="parentNum" value="'.$parNum.'">
                                                                    <input type="hidden" name="n" value="'.$pgNum.'">
                                                                </section>
                                                                <footer>
                                                                    <button style="width:100%;background:green" type="submit" id="addB'.$rplRow['num'].'"
                                                                    data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top"><i class="icofont-check"></i> 작성 완료</button>
                                                                    <span class="subInfo">이 단계부터는 들여쓰기가 적용되지 않습니다.</span>
                                                                    <span id="fnbCA'.$rplRow['num'].'"></span>
                                                                </footer>
                                                            </div>
                                                            <input type="hidden" name="t" value="'.$pgContent['title'].'">
                                                            <input type="hidden" name="i" value="'.$rplRow['id'].'">
                                                            <input type="hidden" name="v" value="b>'.$board.'>'.$pgNum.'-'.$pgPage.'">
                                                        </form></section>';
                                                    if($isMe){
                                                        //수정 창 
                                                        $ccnt = NULL;
                                                        $ccnt = preg_replace('/(<a href=".*">|<\/a>)/', '', $rplRow['content']);
                                                        echo '<section class="comm step_3" id="editC-'.$rplRow['num'].'" style="display:none"><form method="post" action="/comment.php?m=edit">
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
                                                                    <textarea maxlength="1000" style="height:5em" onkeydown="ctrSMe('.$rplRow['num'].')" id="txtE'.$rplRow['num'].'" name="reply" placeholder="댓글 작성">'.$ccnt.'</textarea>
                                                                    <input type="hidden" name="n" value="'.$rplRow['num'].'">
                                                                    <input type="hidden" name="fn" value="'.$pgNum.'">
                                                                </section>
                                                                <footer>
                                                                    <button style="width:100%;background:#a8a8a8" type="submit" id="edcB'.$rplRow['num'].'"
                                                                    data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top"><i class="icofont-check"></i> 작성 완료</button>
                                                                </footer>
                                                            </div>
                                                        </form></section>';
                                                }
                                            }
                                        }
                                    }
                                }
                        }
                    ?>
<!-- 댓글 남기기 -->
                    </a><hr>
                <?php  
                }
                $sql = "SELECT * FROM `_comment` WHERE `from` = '$pgNum'";
                $cmtfResult = mysqli_query($conn, $sql);
                if(mysqli_num_rows($cmtfResult) >= 150){
                    echo '<span class="subInfo center">더 이상 새로운 댓글을 달 수 없습니다.</span></div>';
                }else{
                ?>
                    <footer>
                        <form method="post" action="/comment.php">
                            <textarea onkeydown="ctrSM(0)" id="txtA0" name="c" value="<?=htmlspecialchars($_COOKIE['commBU'])?>" maxlength="1000" placeholder="댓글 작성 (1000자 이내)"></textarea>
                            <div id="fnbcon" class="flex" style="display:none">
                            <?php
                        $fcSql = "SELECT * FROM `_userSet` WHERE `id` = '".$_SESSION['fnUserId']."'";
                        $fcResult = mysqli_query($conn, $fcSql);
                        $fcRow = mysqli_fetch_assoc($fcResult);
                        $fcList = explode(',', $fcRow['fnbcon']);
                        if(mysqli_num_rows($fcResult) !== 0){
                            foreach ($fcList as $value){
                                $fbSql = "SELECT `count`, `ext` FROM `_fnbcon` WHERE `folder` = '$value'";
                                $fbResult = mysqli_query($conn, $fbSql);
                                $fbRow = mysqli_fetch_assoc($fbResult);

                                $value = trim($value);
                                echo '<div style="float:left" onclick="selectFNBCON(\''.$value.'_ico\')"><span class="fnbcImgBox"><img style="max-width:100px" height="100" src="/fnbcon/'.$value.'/main.png"></span>
                                <span id="'.$value.'_ico" class="ico" style="display:none">';
                                $i = 0;
                                while($i < $fbRow['count']){
                                    $i++;
                                    echo '<img height="80"  onclick="FNBCON(\''.$value.'/icon_'.$i.'.'.$fbRow['ext'].'\')" src="/fnbcon/'.$value.'/icon_'.$i.'.'.$fbRow['ext'].'">&nbsp; ';
                                }
                                echo '</span></div>';
                                
                            }
                        }
                            ?>
                                <a href="/emoticon" style="float:left"><i class="icofont-plus-square" style="font-size: 100px"></i></a>
                            </div>
                            <button onclick="openFNBCON()" id="fnbcB" class="default full" style="font-size:0.8em" type="button">
                            <i class="icofont-simple-smile"></i> 픈비콘 사용</button><span id="fnbcI"></span>
                            <input type="hidden" name="n" value="<?=$pgNum?>">
                            <input type="hidden" name="t" value="<?=$pgContent['title']?>">
                            <input type="hidden" name="i" value="<?=$pgContent['id']?>">
                            <input type="hidden" name="v" value="b><?=$board.'>'.$pgNum.'-'.$pgPage?>">
                            <div style="border:1px dashed gray;display:block;display:none;opacity:0.7;" id="fnbcPreDiv"><br>
                            <img src="" id="fnbcPreview" style="height:200px;width:auto;display: block;margin-left:auto;margin-right:auto;"><br></div>
                            <button style="width:100%;background:green" id="addB0" type="submit" data-tooltip="(PC) Ctrl + Enter로 작성 완료 가능" class="tooltip-top">
                            <i class="icofont-check"></i> 작성 완료</button>
                        </form>
                    </footer>
                </div>
                <hr>
            <?php
            }
        }
            ?>
    <div class="modal">
        <input id="infoModal" type="checkbox" />
        <label for="infoModal" class="overlay"></label>
        <article>
            <header>
            <h3>글 정보</h3>
            <label for="infoModal" class="close">&times;</label>
            </header>
            <section class="content">
                <span class="subInfo">등급: 
                <?php
                    if($pgContent['rate'] == 'PG'){
                        echo '<blue id="rate">PG</blue> ';
                    }elseif($pgContent['rate'] == 'R'){
                        echo '<red id="rate">R</red> ';
                    }else{
                        echo '<green id="rate">G</green> ';
                    }

                    if(empty($so) or $so == '0'){
                        $so = '<green>전체공개</green>';
                    }
                ?>| 작성 일시: <?=$at?><br> 열람 허가 대상: <?=$so?></span><br>
                <span class="muted">추천: <blue id="up"><?=$uv?></blue> / 비추천: <red id="down"><?=$dv?></red> / 조회수: <green id="count"><?=$vc?></green>
                </span>
                <br><hr>
                <?php
                    if($iE){
                        echo '<span class="muted">수정 일시 : '.$iE.' / 편집자: '.$eU.'</span>';
                    }
                ?>
                <br>
            </section>
        </article>
    </div>
<?php
    if($isLogged){
        if($_GET['ment']){
            $num = filt($_GET['ment'], '123');
            $sql = "SELECT `target` FROM `_ment` WHERE `num` = '$num'";
            $result = mysqli_query($conn, $sql);
            if(mysqli_num_rows($result) == 1){
                $sql = "UPDATE `_ment` SET `isSuccess` = 1 WHERE `num` = '$num'";
                $result = mysqli_query($conn, $sql);
                if(!$result){
                    echo '데이터베이스 연결 실패';
                }
            }
        }
    }
?>