<?php
    include '../setting.php';
    require_once '../editor/htmlpurifier/library/HTMLPurifier.auto.php';

    // 기본 문자열 필터링 함수
    function filt($arg, $opt){
            $purifier = new HTMLPurifier();
            $val = $purifier->purify($arg);
            if($val == ''){
                $val = 'NULL';
            }else{
                $val = str_ireplace('"', "'", $val);
                $val = "\"$val\"";
            }
        return $val;
    }

    $t = filt($_POST['t'], 'oth'); #이름
    $n = filt($_POST['nn'], 'oth'); #별명
    $i = filt($_POST['i'], 'oth'); #설명
    $r = filt($_POST['r'], 'oth'); #연관 게시판
    $nt = filt($_POST['nt'], 'oth'); #상단 공지
    $k = filt($_POST['k'], 'oth'); #보조 관리인
    $e = filt($_POST['e'], 'oth'); #이모티콘
    $ty = filt($_POST['ty'], 'oth'); #게시판 타입

    $sql = "SELECT `id`, `num`, `slug` FROM `_board` WHERE `title` = $t";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $nm = $row['num'];
    if($_SESSION['fnUserId'] == $row['id']){
        $sql = "UPDATE `_board` SET `nickTitle` = $n, `boardIntro` = $i, `related` = $r
       , `notice` = $nt, `keeper` = $k, `icon` = $e, `type` = $ty WHERE `num` = '$nm'";
        $result = mysqli_query($conn, $sql);
        if($result){
            die('<script>window.location.href = "../b>'.$row['slug'].'"</script>');
        }
    }
    echo $sql;
?>