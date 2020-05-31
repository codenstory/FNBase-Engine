<?php
    $isNot = TRUE;
    include '../setting.php';

    $n = preg_replace('/[^0-9]/', '', $_POST['n']);
    $m = preg_replace('/[^a-zA-Z0-9ㄱ-ㅎ가-힣]/', '', $_POST['m']);
    $kt = preg_replace('/[^0-9]/', '', $_POST['kT']);
    $b = preg_replace('/[^a-zA-Z0-9ㄱ-ㅎ가-힣]/', '', $_POST['b']);
    $i = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['i']);

    $id = $_SESSION['fnUserId'];
    $name = $_SESSION['fnUserName'];
    $ip = get_client_ip();

    //관리자 권한 확인
    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    $iA = mysqli_fetch_assoc($result);
        if(!$iA['isAdmin']){
            $sql = "SELECT a.`isAdmin`, b.`id`, b.`keeper`
            FROM `_account` AS a
            JOIN `_board` AS b
            WHERE a.`id` = b.`id` and b.`slug` = '$b'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if($row['isAdmin'] !== 1){
                if($row['id'] !== $id){
                    if(mb_strpos($row['keeper'], $id) === FALSE){
                        die('권한 없음.');
                    }
                }
            }
        }else{
            $isAdmin = TRUE;
        }

    //실행
    if($_GET['mode'] == 'R'){ #R등급
        $sql = "SELECT `rate` FROM `_content` WHERE `num` = '$n'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if($row['rate'] == 'R'){
            $sql = "UPDATE `_content` SET `rate` = 'PG' WHERE `num` = '$n'";
        }else{
            $sql = "UPDATE `_content` SET `rate` = 'R' WHERE `num` = '$n'";
        }
    }elseif($_GET['mode'] == 'B'){ #블라인드
        $sql = "UPDATE `_content` SET `board` = 'trash', `boardName` = '$b' WHERE `num` = '$n'";
    }elseif($_GET['mode'] == 'M'){ #이동
        $sql = "SELECT `title` FROM `_board` WHERE `slug` = '$m'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $bn = $row['nickTitle'];
        $sql = "UPDATE `_content` SET `board` = '$m', `boardName` = '$bn' WHERE `num` = '$n'";
    }elseif($_GET['mode'] == 'T'){ #블라인드 게시글 삭제
        if($b == 'trash'){
            $sql = "DELETE FROM `_content` WHERE `board` = 'trash'";
        }elseif($b == 'mafia'){
            $sql = "UPDATE `_content` SET `staffOnly` = NULL WHERE `board` = 'mafia'";
        }
    }elseif($_GET['mode'] == 'OFF'){ #사이트 전원 끄기
        if($isAdmin){
            $sql = "UPDATE `_setting` SET `type` = 'OFF' WHERE `num` = $fnMultiNum";
        }
    }elseif($_GET['mode'] == 'ON'){ #사이트 전원 켜기
        if($isAdmin){
            $sql = "UPDATE `_setting` SET `type` = 'board' WHERE `num` = $fnMultiNum";
        }
    }elseif($_GET['mode'] == 'K'){ #사용자 차단
        if(empty($kt)){
            die('시간 값 없음');
        }
        $sql = "SELECT `kicked` FROM `_board` WHERE `slug` = '$b'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if(mb_strpos($row['kicked'], $i) === FALSE){ #추방
            if(empty($row['kicked'])){
                $s = $i;
            }else{
                $s = $row['kicked'].','.$i;
            }

            $ktc = date("Y-m-d H:i:s", strtotime('+'.$kt.' minutes', time()));
            $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `at`, `value`, `target`, `ip`, `isSuccess`)
            VALUES ('$id', '$name', 'BOARD_KICK', '$ktc', '$b', '$i', '$ip', '1')";
            $result = mysqli_query($conn, $sql);

            $s = preg_replace('/[,]{2,}/m', ',', $s);
            $s = preg_replace('/^,/', '', $s);
            $s = preg_replace('/,$/', '', $s);
            $sql = "UPDATE `_board` SET `kicked` = '$s' WHERE `slug` = '$b'";
        }else{ #추방 취소
            $s = $row['kicked'];
            $s = str_ireplace($i, '', $s);
            $s = preg_replace('/[,]{2,}/m', ',', $s);
            $s = preg_replace('/^,/', '', $s);
            $s = preg_replace('/,$/', '', $s);
            $sql = "UPDATE `_othFunc` SET `isSuccess` = '0' WHERE `type` = 'BOARD_KICK' and `value` = '$b' and `target` = '$i'";
            $result = mysqli_query($conn, $sql);
            $sql = "UPDATE `_board` SET `kicked` = '$s' WHERE `slug` = '$b'";
        }
    }else{ #공지
        $sql = "SELECT `category` FROM `_content` WHERE `num` = '$n'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if($row['category'] == '공지'){
            $sql = "UPDATE `_content` SET `category` = '기본' WHERE `num` = '$n'";
        }else{
            $sql = "UPDATE `_content` SET `category` = '공지' WHERE `num` = '$n'";
        }
    }

    $result = mysqli_query($conn, $sql);
    if($result){
        die('<script>window.location.href = document.referrer;</script>');
    }
?>