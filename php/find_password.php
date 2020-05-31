<?php
    require_once '../setting.php';

    function GenStr($length){
        $characters  = "0123456789";  
        $characters .= "abcdefghijklmnopqrstuvwxyz";  
        $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
          
        $string_generated = "";  
          
        $nmr_loops = $length;  
        while ($nmr_loops--)  
        {  
            $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];  
        }  
          
        return $string_generated;  
    }
    
    function filt($arg, $opt){
        if($opt == 'htm'){ #html특문
            $val = htmlspecialchars($arg);
            $val = str_ireplace('"', '&quot;', $val);
            $val = str_ireplace("'", '&apos;', $val);
        }elseif($opt == 'abc'){ #영어, 숫자만
            $re = '/[^a-zA-Z0-9]+/m';
            $val = preg_replace($re, '', $arg);
        }elseif($opt == '123'){ #숫자만
            $re = '/[^0-9]+/m';
            $val = preg_replace($re, '', $arg);
        }elseif($opt == '영한'){ #영어, 숫자, 한글만
            $re = '/[^a-zA-Z0-9ㄱ-ㅎ가-힣_]+/m';
            $val = preg_replace($re, '', $arg);
        }elseif($opt == 'mail'){ #영어, 숫자, 한글만
            $re = '/[^a-zA-Z0-9@._]+/m';
            $val = preg_replace($re, '', $arg);
        }
        return $val;
    }
    
    $mail = filt($_POST['mail'], 'mail');
    $id = filt($_POST['id'], 'abc');
    $exp = date("Y-m-d H:i:s",strtotime ("+30 minutes"));
    $val = GenStr(20);

    $sql = "SELECT `name` FROM `_account` WHERE `mail` = '$mail' and `id` = '$id'";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1){
        $sql = "INSERT INTO `_auth` (`type`, `key`, `value`, `end`) VALUES ('password', '$mail', '$val', '$exp')";
        $result = mysqli_query($conn, $sql);
        if($result === false){
        echo '데이터베이스 연결 실패';
        }
        
        $subject = "비밀번호 변경 절차";
        $contents = '
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tbody>
                <tr>
                    <td>
                        <div style="color:#fff;text-decoration: none;background:#5998d6;text-align:center">
                            <p><br></p>
                            <h3>이메일 인증이 필요합니다.</h3>
                            <p><br></p>
                            <p>아래&nbsp;주소를 눌러 절차를 계속 진행해봅시다.</p>
                            <p>주소가 눌러지지 않으신다면,</p>
                            <p>아래 주소를 복사하고 브라우저에 붙여넣어 계속 진행하세요.</p>
                            <p><strong>※비밀번호 찾기를 진행하신 적이 없으실 경우, 이 메일을 무시하여주세요.</strong></p>
                            <p><br></p>
                            <p>만료 일시 : '.$exp.'</p>
                            <p>&nbsp;<a style="color:white" href="'.$fnPath.'/mailAuth_'.$val.'_'.$mail.'" target="_blank">'.$fnPath.'/mailAuth_'.$val.'_'.$mail.'</a></p>
                            <p><br></p>
                            <p>'.$fnPFooter.'</p>
                            <p><br></p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        ';
        $headers = "From: noreply@".$_SERVER['SERVER_NAME']."\r\n";
        $headers .= 'Content-Type: text/html; charset=utf-8'."\r\n";

        $mail = mail($mail, '=?UTF-8?B?'.base64_encode($subject).'?=', $contents, $headers);
        if($mail){
            echo '<script>location.href = "../index.php?alert=mailsend"</script>';
        }else{
            echo '<script>location.href = "../index.php?alert=mailfail"</script>';
        }
    }else{
        die('<script>alert("일치하는 사용자가 없습니다!")</script>');
    }
?>