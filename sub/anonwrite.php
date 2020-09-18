<?php
    if(!empty($_POST['title']) and $_POST['title'] != '0'){
        include '../setting.php';
        if(empty($_SESSION['fnUserId']) and $_SESSION['fnUserId'] != '0'){
            if(date('H') >= 22 or date('H') <= 17){
                die('어림도 없지ㅋㅋ');
            }
            $sqls = "SELECT * FROM `_ipban` WHERE `ip` = '$ip'";
            $results = mysqli_query($conn, $sqls);
            if(mysqli_num_rows($results) > 0){
                die('부적절한 이용으로 인해 차단됨!');
            }

            $desc = htmlspecialchars($_POST['content']);
            $desc = mb_substr($desc, 0, 500);

            $title = htmlspecialchars($_POST['title']);
            $title = mb_substr($title, 0, 20);

            $name = htmlspecialchars($_POST['nickname']);
            $name = mb_substr($name, 0, 10);
            $_SESSION['fnAnonNick'] = $name;

            $sql = "SELECT Count(*) as `cnt` FROM `_content` WHERE `ip` = '$ip' and `at` > DATE_SUB(NOW(), INTERVAL 20 SECOND)";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            if($row['cnt'] >= 2){
                die('<script>alert("글 작성 빈도가 너무 짧습니다.");history.back()</script>');
            }

            $now = date('Y-m-d H:i:s');

            //글 저장
            $sql = "INSERT INTO `_content` (`id`, `name`, `type`, `at`, `mail`,
            `title`, `content`, `board`, `boardName`, `category`, `rate`, `staffOnly`,
            `ip`, `isEdited`, `whoEdited`, `voteCount_Up`, `voteCount_Down`, `viewCount`, `commentCount`, `isMarkdown`, `isMedia`)
            VALUES ('_anon', '$name', 'ANON_WRITE', '$now', 'anon@fnbase.xyz', '$title', '$desc', 'uita', '자유', '익명', 'PG', NULL,
            '$ip', NULL, NULL, '0', '0', '0', '0', '0', NULL)";

            $result = mysqli_query($conn, $sql);
            if(!$result){
                die($desc);
            }else{
                $sql = "SELECT `num` FROM `_content` WHERE `at` = '$now' and `ip` = '$ip'";
                $outcome = mysqli_query($conn, $sql);
                $outcome = mysqli_fetch_assoc($outcome);

                $n = $outcome['num'];

                $sql = "SELECT Count(*) as `cnt` FROM `_content` WHERE `ip` = '$ip' and `at` > DATE_SUB(NOW(), INTERVAL 60 SECOND)";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                if($row['cnt'] >= 3){
                    $sql = "INSERT INTO `_ipban` (`ip`) VALUES ('$ip');";
                    $result = mysqli_query($conn, $sql);
                    die('<script>alert("귀하께서는 도배로 인하여 광역차단 되셨습니다.");location.href = \'./\'</script>');
                }else{
                    die('<script>location.href = "/b>recent>'.$n.'"</script>');
                }
            }
        }
    }else{
        die('내용이 없거나 로그인되어있음.');
    }
?>