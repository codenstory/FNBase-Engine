<?php
    include '../setting.php';

    if($_SESSION['fnUserId']){
        die('<script>alert("이미 로그인 되어있음.");history.back()</script>');
    }

    $ip = get_client_ip();
    $uA = $_SERVER['HTTP_USER_AGENT'];
    $sql = "SELECT `id`, `name`, `mail` FROM `_account` WHERE `autoLogin` = '1' and `type` not like 'QUIT' and `lastIp` = '$ip' and `userAgent` = '$uA'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if(mysqli_num_rows($result) == 1){
        $id = $row['id'];
        $name = $row['name'];
        $mail = $row['mail'];
        $_SESSION['fnUserId'] = $id;
        $_SESSION['fnUserName'] = $name;
        $_SESSION['fnUserMail'] = $mail;
        $sql = "
        INSERT INTO `_log`
        (`id`,`name`,`type`,`at`,`ip`,`isSuccess`)
        VALUES(
            '{$id}',
            '{$name}',
            'AUTO_LOGIN',
            NOW(),
            '{$ip}',
            1
        )
        ";
        $result = mysqli_query($conn, $sql);
        if($result === false){
            echo '데이터베이스 연결 실패';
        }else{
            die('<script>history.go(-2)</script>');
        }
    }else{
        $sql = "
        INSERT INTO `_log`
        (`id`,`name`,`type`,`at`,`ip`,`isSuccess`)
        VALUES(
            '$ip',
            '$uA',
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
        echo '<h3>로그인 실패</h3><br>
        <p>자동 로그인을 활성화하지 않으셨거나,<br>
        마지막으로 사용하신 ip / 브라우저 정보와 일치하지 않습니다.</p>
        <span style="color:gray;font-size:0.9em">내 정보에서 ip / 브라우저(User Agent) 를 기억시키세요.</span>';
        exit;
    }