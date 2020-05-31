<?php
    $fnMultiNum = 2;
    include_once './setting.php';
    include_once './func.php';
    $fnwTitle = filt($_POST['title'], 'htm');
    $content = filt($_POST['content'], 'oth');
    $comm = filt($_POST['comment'], 'oth');
    include_once './wiki_p.php';
    
    if(empty($fnwTitle)){
        die('<script>alert("제목이 비어있습니다.");history.back()</script>');
    }elseif(empty($content)){
        die('<script>alert("내용이 비어있습니다.");history.back()</script>');
    }else{
        $sql = "SELECT `content` FROM `_article` WHERE `title` = '$fnwTitle'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if($content == $row['content']){
            die('<script>alert("내용이 그대로입니다.");history.back()</script>');
        }
    }

    $sql = "SELECT * FROM `_article` WHERE `title` = '$fnwTitle'";
    $result = mysqli_query($conn, $sql);
    $document = mysqli_fetch_assoc($result);

        //ACL
        $sqla = "SELECT `isAdmin` FROM `_account` WHERE `id` = '$id'";
        $resulta = mysqli_query($conn, $sqla);
        $iA = mysqli_fetch_assoc($resulta);
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

    if($canEdit){
        if($_SESSION['fnUserId']){
            $id = $_SESSION['fnUserId'];
            $name = $_SESSION['fnUserName'];
            $isAnon = 0;
        }else{
            $id = $ip;
            $name = $ip;
            $isAnon = 1;
        }
        
        if(mysqli_num_rows($result) < 1){ #신규
            $sql = "INSERT INTO `_history` (`title`, `id`, `name`, `rev`, `at`, `comment`) VALUES ('$fnwTitle', '$id', '$name', '$content', NOW(), '$comm')";
            $result = mysqli_query($conn, $sql);
            $sql = "INSERT INTO `_article` (`type`, `at`, `title`, `content`, `lastEdit`, `whoEdited`, `viewCount`, `ACL`)
            VALUES ('COMMON', CURRENT_TIMESTAMP, '$fnwTitle', '$content', NOW(), '$id', '0', NULL)";
            $result = mysqli_query($conn, $sql);
        }elseif(mysqli_num_rows($result) > 1){ #1개 이상 (오류)
            die('오류!');
        }else{ #수정
            $sql = "INSERT INTO `_history` (`title`, `id`, `name`, `rev`, `at`, `comment`) VALUES ('$fnwTitle', '$id', '$name', '$content', NOW(), '$comm')";
            $result = mysqli_query($conn, $sql);
            $sql = "UPDATE `_article` SET `content` = '$content', `lastEdit` = NOW(), `whoEdited` = '$id' WHERE `title` = '$fnwTitle'";
            $result = mysqli_query($conn, $sql);
        }
    }else{
        die('권한 부족');
    }
?>