<?php
require_once 'setting.php';
require_once 'func.php';

if(empty($_POST['userid']) or $_POST['userid'] == '0'){
    exit;
}

$ip = get_client_ip();

$id = strtolower(filt($_POST['userid'], 'abc'));
$name = filt($_POST['nickname'], '영한');
$mail = filt($_POST['mail'], 'mail');
$pw = filt($_POST['password'], 'htm');

$id = mb_substr($id, 0, 19);
$name = mb_substr($name, 0, 19);
$pw = mb_substr($pw, 0, 49);
$mail = mb_substr($mail, 0, 320);

$intro = filt($_POST['intro'], 'htm');

if(empty($_POST['intro']) or $_POST['intro'] == '0'){
    $intro = '<span class="muted">없음.</span>';
}
if(empty($name) or $name == '0'){
    exit;
}if(empty($id) or $id == '0'){
    exit;
}


$from = filt($_POST['from'], 'abc');
$before = filt($_POST['before'], 'abc');
$after = filt($_POST['after'], 'abc');

$value = $from.','.$before.','.$after;
$password_b = password_hash($pw, PASSWORD_DEFAULT);

    $sql = "UPDATE `_auth` SET `type` = 'complete' WHERE `key` = '$mail'";
    $result = mysqli_query($conn, $sql);

    $sql = "INSERT INTO `_account` (`id`, `name`, `type`, `password`, `mail`, `mailAuth`, `lastIp`, `isAdmin`, `userIntro`, `point`)
    VALUES ('$id', '$name', 'COMMON', '$password_b', '$mail', '1', '$ip', '0', '$intro', '10')";
                $result = mysqli_query($conn, $sql);
                if($result === false){
                    echo '데이터베이스 연결 실패';
                }
        
    $sql = "INSERT INTO `_userSet` (`id`, `name`, `type`, `at`, `fnbcon`) VALUES ('$id', '$name', 'COMMON', CURRENT_TIMESTAMP, 'default')";
    $result = mysqli_query($conn, $sql);

    $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `value`, `ip`, `isSuccess`)
    VALUES ('$id', '$name', 'SIGNUP_RES', '$value', '$ip', '1')";
                $result = mysqli_query($conn, $sql);
                if($result === false){
                    echo '데이터베이스 연결 실패';
                }else{
                    echo "<script>location.href='/index.php?alert=plslogin'</script>";
                }
?>