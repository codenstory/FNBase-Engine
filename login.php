<?php
$isNot = TRUE;
require_once './setting.php';
require './func.php';

if($_SESSION['fnUserId']){
    session_unset();
    echo '<script>alert("로그아웃 되었습니다.");location.href = "./main"</script>';
    exit;
}

if(empty($_POST['id'])){
    echo '잘못된 접근';
    exit;
}

$ip = get_client_ip();
 
        $connect = $conn;

        //입력 받은 id와 password
        $id = filt($_POST['id'], 'abc');
        $pw = filt($_POST['pw'], 'htm');

        //아이디가 있는지 검사
        $query = "SELECT * from `_account` where id = '$id'";
        $result = $connect->query($query);

 
        //아이디가 있다면 비밀번호 검사
        if(mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                $hash = $row['password'];
                //비밀번호가 맞다면 세션 생성
                if(password_verify($pw, $hash)){
                        //세션에 계정 정보 저장
                        $name = $row['name'];
                        $mail = $row['mail'];
                        $_SESSION['fnUserId'] = $id;
                        $_SESSION['fnUserName'] = $name;
                        $_SESSION['fnUserMail'] = $mail;

                        $lgSucc = TRUE;
                }else{
                    $lgText = '아이디 또는 비밀번호가 일치하지 않습니다.';
                    $lgSucc = FALSE;
                }
        }else{
                $lgText = '아이디 또는 비밀번호가 일치하지 않습니다.';
                $lgSucc = FALSE;
        }

        if($lgSucc){
            $sql = "
                INSERT INTO `_log`
                (`id`,`name`,`type`,`at`,`ip`,`isSuccess`)
                VALUES(
                    '{$id}',
                    '{$name}',
                    'COMMON',
                    NOW(),
                    '{$ip}',
                    1
                )
            ";
            $result = mysqli_query($conn, $sql);
            if($result === false){
                echo '데이터베이스 연결 실패';
            }
        }else{
            $sql = "
                INSERT INTO `_log`
                (`id`,`name`,`type`,`at`,`ip`,`isSuccess`)
                VALUES(
                    '{$id}',
                    '{$name}',
                    'COMMON',
                    NOW(),
                    '{$ip}',
                    0
                )
            ";
            $result = mysqli_query($conn, $sql);
            if($result === false){
                echo '데이터베이스 연결 실패';
            }
            echo '<script>alert("'.$lgText.'");</script>';
            die('<script>location.href = history.go(-1)</script>');
        }

        die('<script>location.href = "/main"</script>');
?>