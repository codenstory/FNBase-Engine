<?php
    require '../setting.php';
    $id = $_SESSION['fnUserId'];
    $sql = "SELECT `password` FROM `_account` WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    if(mysqli_num_rows($result) == 1) {
        $hash = $row['password'];
        if(password_verify($_POST['password'], $hash)){
            $sql = "UPDATE `_account` SET `password` = 'QUIT', `point` = '-2', `type` = 'QUIT', `userIntro` = '<red>탈퇴한 사용자.</red>', `userAgent` = NULL, `lastIp` = '$ip' WHERE `id` = '$id'";
            $result = mysqli_query($conn, $sql);
            die('<script>alert("안녕히가십시오.");location.href = "https://www.google.com/search?q=farewell"</script>');
            $_SESSION = NULL;
        }else{
            die('<script>alert("비밀번호가 일치하지 않습니다.");history.back()</script>');
        }
    }else{
        die('데이터베이스 오류');
    }
?>