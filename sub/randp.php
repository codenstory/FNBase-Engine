<?php
if($board){
    $_SESSION['temp_board'] = $board;
    $sql = "SELECT * FROM `_othFunc` WHERE `type` = 'RANDP_VALS' and `target` = '$board' and `at` > DATE_SUB(NOW(), INTERVAL 3 DAY) ORDER BY `at` ASC";
            ?>
<div class="card">
    <header style="background:#f3f3f3;border-bottom:1px solid #e6e6e6">
        <h3 id="title"><i class="icofont-calculator-alt-2"></i> 임의 결과 추첨</h3><br>
            <span class="subInfo">
                <?=$boardName?>에서 진행된 랜덤 결과 추첨 기록입니다.
            </span><?php
                if($isStaff or $isAdmin){
            ?><a class="right" style="font-size:0.75em" href="/sub/randp.php">작성하기</a><?php
                }
            ?>
    </header>
    <article class="mainCon" id="mainCon">
        <table class="full">
        <?php
            $result = mysqli_query($conn, $sql);
            if(mysqli_num_rows($result) != 0){
                while($row = mysqli_fetch_assoc($result)){
                    if(!$row['reason']){
                        $row['reason'] = '없음.';
                    }
                    echo '<tr><td>'.$row['value'].'</td></tr>';
                    echo '<tr><td class="subInfo"> <a href="/u/'.$row['id'].'">'.$row['name'].'</a> - '.$row['reason'].' ('.get_timeFlies($row['at']).')</td></tr>';
                }
            }else{
                echo '<tr><td>3일 이내 진행 기록이 없습니다.</td></tr>';
            }
        ?>
        </table>
    </article>
</div>
            <?php
}elseif($_POST['vals']){
    include '../setting.php';
    if($id){
        $vals = array_map('trim', array_map('htmlentities', explode(',', $_POST['vals'])));
        $val = $vals[mt_rand(0, count($vals) - 1)];
        $reason = htmlentities($_POST['reason']).' ('.implode(',', $vals).')';
        $bds = preg_replace('/[^a-zA-Z0-9 _]/u', '', $_POST['board']);

        $sql = "SELECT `id`, `keeper` FROM `_board` WHERE `slug` = '$bds';";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if($_SESSION['fnUserId'] === $row['id'] or mb_strpos($row['keeper'], $_SESSION['fnUserId']) !== FALSE){
            $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `value`, `target`, `reason`, `ip`, `isSuccess`)
            VALUES ('$id', '$name', 'RANDP_VALS', '$val', '$bds', '$reason', '$ip', '1')";
            $result = mysqli_query($conn, $sql);
            if($result){
                die('<script>alert("작업 완료");history.go(-2)</script>');
            }else{
                die('작업 실패');
            }
        }else{
            die('실패');
        }
    }
}else{
    session_start()
    ?>
        <style>
            * {
                width: 100%;
            }
            textarea {
                padding: 1em;
            }
        </style><meta name="viewport" content="width=device-width, initial-scale=1.0">
        <form method="POST" action="./randp.php">
            <textarea maxlength="100" name="vals" placeholder="결과. 쉼표로 구분. 최대 100자." required></textarea><br>
            <textarea maxlength="100" name="reason" placeholder="진행 사유. 최대 100자."></textarea><br>
            <input type="text" name="board" placeholder="대상 게시판" value="<?=$_SESSION['temp_board']?>" /><br>
            <input type="submit" value="작성" />
        </form>
    <?php
}
?>