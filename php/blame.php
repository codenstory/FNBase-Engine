<?php
    include '../setting.php';
    $b = htmlspecialchars($_POST['board']);
    $t = htmlspecialchars($_POST['target']);

    //관리자 권한 확인
    $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = '$id'";
    $result = mysqli_query($conn, $sql);
    $iA = mysqli_fetch_assoc($result);
        if(!$iA['isAdmin']){
            $sql = "SELECT a.`isAdmin`, b.`id`, b.`keeper`
            FROM `_account` AS a
            JOIN `_board` AS b
            WHERE a.`id` = b.`id` and b.`slug` = '$b'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if($row['isAdmin'] !== 1){
                if($row['id'] !== $id){
                    if(mb_strpos($row['keeper'], $id) === FALSE){
                        die('권한 없음.');
                    }
                }
            }
        }

        $sql = "SELECT `kicked` FROM `_board` WHERE `slug` = '$b'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        if(mb_strpos($row['kicked'], $t) === FALSE){ #추방
            if(empty($row['kicked']) or $row['kicked'] == '0'){
                $s = $t;
            }else{
                $s = $row['kicked'].','.$t;
            }

            $ktc = date("Y-m-d H:i:s", strtotime('+10080 minutes', time()));
            $sql = "INSERT INTO `_othFunc` (`id`, `name`, `type`, `at`, `value`, `target`, `ip`, `isSuccess`)
            VALUES ('$id', '$name', 'BOARD_KICK', '$ktc', '$b', '$t', '$ip', '1')";
            $result = mysqli_query($conn, $sql);

            $s = preg_replace('/[,]{2,}/m', ',', $s);
            $s = preg_replace('/^,/', '', $s);
            $s = preg_replace('/,$/', '', $s);
            $sql = "UPDATE `_board` SET `kicked` = '$s' WHERE `slug` = '$b'";
            $result = mysqli_query($conn, $sql);
        }else{ #추방 취소
            $s = $row['kicked'];
            $s = str_ireplace($t, '', $s);
            $s = preg_replace('/[,]{2,}/m', ',', $s);
            $s = preg_replace('/^,/', '', $s);
            $s = preg_replace('/,$/', '', $s);
            $sql = "UPDATE `_othFunc` SET `isSuccess` = '0' WHERE `type` = 'BOARD_KICK' and `value` = '$b' and `target` = '$t'";
            $result = mysqli_query($conn, $sql);
            $sql = "UPDATE `_board` SET `kicked` = '$s' WHERE `slug` = '$b'";
            $result = mysqli_query($conn, $sql);
        }
    die('<script>history.back()</script>');
?>