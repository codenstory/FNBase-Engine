<?php
include_once '../setting.php';
if(!$board){
    $board = preg_replace('/[^a-z0-9 ]/mu', '', $_GET['b']);
}
$sql = "SELECT * FROM `_board` WHERE `slug` = '$board';"; #게시판 설정 로드
$lsResult = mysqli_query($conn, $sql);
if($lsResult === FALSE){
    die('데이터베이스 오류');
}
if(mysqli_num_rows($lsResult) == 1){
    $board = mysqli_fetch_assoc($lsResult);
}else{
    die('게시판 아이디 값이 바르지 않습니다.');
}

$ownerId = $board['id'];
$ownerName = $board['name'];
$boardName = $board['title'];
$board = $board['slug'];
        echo '<form method="POST" action="/php/blame.php">
            <table class="black noGray">
                <input type="hidden" name="board" value="'.$board.'">';

    $sql = "SELECT `id`, `name`, `value`, `target`, `at` FROM `_othFunc` WHERE `at` > NOW() and `target` not like '' and `isSuccess` = 1 and `type` = 'BOARD_KICK' and `value` = '$board' ORDER BY `at` ASC LIMIT 200";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result)){
        while($row = mysqli_fetch_assoc($result)){
            if($ownerName == $row['name']){
                $style = ' style="background:yellow"';
            }else{
                unset($style);
            }
            $s = "SELECT `name` FROM `_account` WHERE `id` = '".$row['target']."'";
            $res = mysqli_query($conn, $s);
            $r = mysqli_fetch_assoc($res);
            if(empty($r['name']) or $r['name'] == '0'){
                break;
            }
            echo '<tr>
                <td><a href="/u/'.$row['id'].'"><b'.$style.'>'.$row['name'].'</b></a><br>
                <a href="/u/'.$row['target'].'">'.$r['name'].'</a><br>
                '.$row['at'].'까지<br>';
            if($ownerId == $id){
                echo '<button class="error" name="target" value="'.$row['target'].'">차단 해제</button></td>';
            }
            echo '</tr>';
        }
        echo '<tr><td><span class="subInfo">200개까지 보여지며, 굵은 글씨가 행위자, 일반 글씨가 대상자, 노란색 강조 표시는 채널 소유주입니다.</span></td></tr>';
    }else{
        echo '<tr>
            <td>차단된 사용자가 없습니다.</td>
        </tr>';
    }
        echo '</table></form>';
?>