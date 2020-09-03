<?php
    $fnMultiNum = 2;
    include_once 'setting.php';
    include_once 'func.php';
    $fnwTitle = filt($_GET['title'], 'htm');
    include_once 'wiki_p.php';

    $fnwTitle = documentRender($fnwTitle, TRUE);

    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
    $result = mysqli_query($conn, $sql);
    $iA = mysqli_fetch_assoc($result);
        if($iA['isAdmin']){
            $isAdmin = TRUE;
        }

if($_GET['mode'] == 'view'){
    $num = filt($_GET['num'], 'htm');
    if(empty($num) or $num == '0'){
        die('번호가 비어있습니다.');
    }

    $sql = "SELECT `rev`, `comment`, `id`, `name`, `modify?` FROM `_history` WHERE `num` = '$num'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    if($row['modify?'] != ''){
        echo '<div style="border: 1px solid gainsboro;color:gray;border-radius:5px">'.$row['modify?'].'<br>
        <a href="/u/'.$row['id'].'" class="right muted">-- <i class="icofont-user-alt-7"></i> '.$row['name'].'</a><br></div>';
    }elseif($row['comment'] != ''){
        echo '<div style="border: 1px solid gainsboro;color:gray;border-radius:5px">'.$row['comment'].'<br>
        <a href="/u/'.$row['id'].'" class="right muted">-- <i class="icofont-user-alt-7"></i> '.$row['name'].'</a><br></div>';
    }
    $content = nl2br(documentRender($row['rev']));
    echo preg_replace('/<br( \/)*>\n<hr>/m', '<hr>', preg_replace('/(src="|<hr>)(.*)<br( \/)*>/m', '$1$2', preg_replace('/<\/h(\d)><br \/>/m', '</h$1>', $content)));
}elseif($_GET['mode'] == 'raw'){
    $num = filt($_GET['num'], 'htm');
    if(empty($num) or $num == '0'){
        die('번호가 비어있습니다.');
    }

    $sql = "SELECT `rev` FROM `_history` WHERE `num` = '$num'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    echo nl2br($row['rev']);  
}else{
    if(empty($fnwTitle) or $fnwTitle == '0'){
        die('제목이 비어있습니다.');
    }

    $sql = "SELECT count(*) as `cnt` FROM `_history` WHERE `title` = '$fnwTitle'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $i = 1;
    $c = $row['cnt'];

    $sql = "SELECT * FROM `_history` WHERE `title` = '$fnwTitle' ORDER BY `num` DESC LIMIT 31";
    $result = mysqli_query($conn, $sql);

    echo '<table class="full"><tbody>';
    while($row = mysqli_fetch_assoc($result)){
        if($i !== 1){
            $strLCac = $prevLen - mb_strlen($row['rev']);
            if($strLCac < 0){
                $color = 'red';
            }elseif($strLCac > 0){
                $strLCac = '+'.$strLCac;
                $color = 'green';
            }else{
                $color = 'blue';
            }
            echo '/ <'.$color.'>('.$strLCac.')</'.$color.'></span></td></tr>';
        }
        
        if($i > 30){
            break;
        }

        $wE = $row['id'];
        $sqln = "SELECT `name` FROM `_account` WHERE `id` = '$wE'";
        $resultn = mysqli_query($conn, $sqln);

        if(mysqli_num_rows($resultn) < 1){
            $name = $wE;

            $icon = 'invisible';
            $href = 'https://fnbase.xyz/misc%3EmanageCenter%3E'.$wE;
            if($isAdmin){
                $href = '/misc>manageCenter>'.$name;
            }
        }else{
            $name = mysqli_fetch_assoc($resultn);
            $name = $name['name'];

            $icon = 'user-alt-7';
            $href = '/u/'.$wE;
        }
        
        echo '<tr><td class="black muted"><a href="javascript:void(0)" onclick="wikiHisRev('.$row['num'].')">#'.$c.'번째 편집 ('.$row['at'].')</a>
        <br><span class="subInfo"><i class="icofont-'.$icon.'"></i><a href="'.$href.'"> '.$name.'</a> ';
        $i++;
        $c--;
        $prevLen = mb_strlen($row['rev']);
    }
    echo '</tbody></table>';
}
?>