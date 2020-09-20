<?php
    $fnMultiNum = 2;
    include_once 'setting.php';
    include_once 'func.php';
    $fnwTitle = filt(urldecode($_GET['title']), 'htm');
    include_once 'wiki_p.php';
    $fnwTitle = myUrlDecode($fnwTitle);
        $fnwTitle = myUrlDecode($fnwTitle);
    
    if(empty($fnwTitle) and $fnwTitle != '0'){
        die('<script>alert("값이 비어있습니다.");history.back()</script>');
    }

    $sql = "SELECT * FROM `_article` WHERE `title` = '$fnwTitle'";
    $result = mysqli_query($conn, $sql);
    $document = mysqli_fetch_assoc($result);

        //ACL
        $sqla = "SELECT `isAdmin`, `canUpload` FROM `_account` WHERE `id` = '$id'";
        $resulta = mysqli_query($conn, $sqla);
        $iA = mysqli_fetch_assoc($resulta);
        $cU = $iA['canUpload'];
        $iA = $iA['isAdmin'];

        if($document['ACL'] == 'all'){
            $canEdit = TRUE;
            $aclText = '전체 개방';
        }elseif($document['ACL'] == 'none'){
            $canEdit = FALSE;
            $aclText = '모두 거부';
        }elseif($document['ACL'] == 'user' || $document['ACL'] == NULL){
            if($id){
                $canEdit = TRUE;
            }else{
                $canEdit = FALSE;
            }
            $aclText = '회원 수정';
        }else{
            $canEdit = FALSE;
            $aclText = '보호 문서';
        }

        if(!$iA){
            $canManage = FALSE;
        }else{
            $canEdit = TRUE;
            $canManage = TRUE;
        }

        if($document['namespace'] != ''){
            $namespace = documentRender($document['namespace'], TRUE);
        }else{
            $namespace = '<span class="muted">없음</span>';
        }
    
    if(!empty($_GET['mode']) and $_GET['mode'] != '0'){
        if($_GET['mode'] == 'modify'){
            if($canEdit){
                if(!empty($_POST['moveTitle']) and $_POST['moveTitle'] != '0'){
                    $title = filt($_POST['moveTitle'], 'htm');
                    $sql = "SELECT `title` FROM `_article` WHERE `title` = '$title'";
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) < 1){
                        $sql = "INSERT INTO `_history` (`title`, `id`, `name`, `at`, `modify?`) VALUES ('$fnwTitle', '$id', '$name', NOW(), 'MOVE_TITLE')";
                        $result = mysqli_query($conn, $sql);
                        $sql = "UPDATE `_article` SET `title` = '$title', `lastEdit` = NOW(), `whoEdited` = '$id' WHERE `title` = '$fnwTitle'";
                        $result = mysqli_query($conn, $sql);
                        $sql = "UPDATE `_history` SET `title` = '$title' WHERE `title` = '$fnwTitle'";
                        $result = mysqli_query($conn, $sql);
                        $sql = "UPDATE `_discuss` SET `title` = '$title' WHERE `title` = '$fnwTitle'";
                        $result = mysqli_query($conn, $sql);
                        $sql = "INSERT INTO `_article` (`type`, `at`, `title`, `namespace`, `content`, `lastEdit`, `whoEdited`, `viewCount`, `ACL`, `execute`)
                        VALUES ('COMMON', NOW(), '$fnwTitle', NULL, '#redirect $title', NOW(), '$id', '0', NULL, NULL)";
                        $result = mysqli_query($conn, $sql);
                    }else{
                        die('<script>alert("이미 있는 문서 이름입니다.");history.back()</script>');
                    }
                }
            }
        }else{
            if($canManage){
                if($_GET['mode'] == 'manage'){
                    if(!empty($_POST['acl_perm']) and $_POST['acl_perm'] != '0'){
                        $perm = filt($_POST['acl_perm'], 'htm');
                        $sql = "UPDATE `_article` SET `ACL` = '$perm' WHERE `title` = '$fnwTitle'";
                        $result = mysqli_query($conn, $sql);
                    }if(!empty($_POST['namespace']) and $_POST['namespace'] != '0'){
                        $ns = filt($_POST['namespace'], 'htm');
                        if($ns == 'none') {
                            $sql = "UPDATE `_article` SET `namespace` = NULL WHERE `title` = '$fnwTitle'";
                        }else{
                            $sql = "UPDATE `_article` SET `namespace` = '$ns' WHERE `title` = '$fnwTitle'";
                        }
                        $result = mysqli_query($conn, $sql);
                    }
                }else{
                    $sql = "INSERT INTO `_history` (`title`, `id`, `name`, `at`, `modify?`) VALUES ('$fnwTitle', '$id', '$name', NOW(), 'DELETE')";
                    $result = mysqli_query($conn, $sql);
                    $sql = "DELETE FROM `_article` WHERE `title` = '$fnwTitle'";
                    $result = mysqli_query($conn, $sql);
                }
            }
        }

        die('<script>alert("요청하신 동작을 수행했습니다.");history.back()</script>');
    }

    echo '<h3>문서 정보</h3>';
    echo '<table><tbody>';
    echo '<tr><td>ACL</td><td>'.$aclText.'</td></tr>';
    echo '<tr><td>이름공간</td><td>'.$namespace.'</td></tr>';
    echo '<tr><td>문서 조회수</td><td>'.$document['viewCount'].'</td></tr>';
    echo '<tr><td>마지막 편집</td><td>'.$document['whoEdited'].' / '.$document['lastEdit'].'</td></tr>';
    echo '</tbody></table>';

    if($canEdit && $cU){
        echo '<br><form method="post" action="/wiki_m.php?mode=modify&title='.myUrlEncode($fnwTitle).'"><h3>문서 이동</h3>';
        echo '<strong><red>경고!</red> 적절한 이유 없이 문서를 옮기지 마세요!</strong><br>';
        echo '<input type="text" name="moveTitle" placeholder="바꿀 문서 이름">';
        echo '<span class="subInfo">가급적이면 토론으로 결론을 지은 뒤 이동해주세요.</span><br>
        <span class="subInfo"><a href="https://namu.wiki/w/문서%20삭제식%20이동">문서 삭제식 이동</a>과 같은 부적절한 권한 행사는 광역 차단 사유가 될 수 있습니다!</span></form>';
    }

    if($canManage){
        echo '<br><br><form method="post" action="/wiki_m.php?mode=manage&title='.myUrlEncode($fnwTitle).'"><h3>ACL 조정</h3>';
        echo '<strong><red>경고!</red> 잘못된 권한 설정은 중대한 손실을 초래할 수 있습니다.</strong><br>';
        echo '<select name="acl_perm">
            <option value="all">all (전체 개방)</option>
            <option value="user" selected>user (회원 수정)</option>
            <option value="admin">admin (보호; 관리자만)</option>
            <option value="none">none (모두 거부)</option>
        </select>';
        echo '<button type="submit" class="success full"><i class="icofont-diskette"></i> 적용하기</button>';
        echo '<span class="subInfo">문서 수정, 토론 참가와 같은 거의 모든 권한이 ACL에 연관되어있습니다.</span><br></form>';

        echo '<br><br>';

        echo '<br><br><form method="post" action="/wiki_m.php?mode=manage&title='.myUrlEncode($fnwTitle).'"><h3>이름 공간 추가</h3>';
        echo '<select name="namespace">
            <option value="___SITENAME___" selected>사이트 이름 ('.$fnTitle.')</option>
            <option value="프로젝트">프로젝트</option>
            <option value="none">설정 안 함</option>
        </select>';
        echo '<button type="submit" class="success full"><i class="icofont-diskette"></i> 적용하기</button></form>';

        echo '<br><br>';

        echo '<form method="post" action="/wiki_m.php?mode=delete&title='.myUrlEncode($fnwTitle).'"><h3>문서 삭제</h3>';
        echo '<strong><red>경고!</red> 삭제 권한 남용은 중대 규칙 위반 행위입니다!</strong><br>';
        echo '<button type="submit" class="error full"><i class="icofont-bin"></i> 삭제하기</button>';
        echo '<span class="subInfo">국내법에 의거해 삭제가 요청된 문서 또는 반달리즘 행위로 인해 생성된 문서가 아닌 이상, 토론을 거치십시오.</span><br>
        <span class="subInfo">이용 규칙에 위배되는 부적절한 권한 행사는 광역 차단 사유가 될 수 있습니다!</span></form>';
    }
?>