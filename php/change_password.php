<?php
    $mail = filt($_GET['mail'], 'mail');
    $val = GenStr(16);
    $pw = password_hash($val, PASSWORD_DEFAULT);

    $sql = "SELECT `type` FROM `_auth` WHERE `key` = '$mail' and `type` = 'password' and `end` > NOW()";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) != 0){
        $sql = "UPDATE `_auth` SET `type` = 'complete' WHERE `key` = '$mail'";
        $result = mysqli_query($conn, $sql);
        $sql = "UPDATE `_account` SET `password` = '$pw' WHERE `mail` = '$mail'";
        $result = mysqli_query($conn, $sql);
        if($result === false){
        echo '데이터베이스 연결 실패';
        }
        
        echo '임시 비밀번호가 "'.$val.'"로 설정되었습니다.<br>';
        echo '"내 정보" 페이지에서 비밀번호를 변경해주세요.';
    }else{
        die('<script>alert("키가 일치하지 않습니다.")</script>');
    }
?>