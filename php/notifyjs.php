<?php
    require_once '../setting.php';

    if(!empty($id) or $id == '0'){
        $sql = "SELECT `type` FROM `_ment` WHERE `target` = '$id' and `isSuccess` = 0";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) > 0){
            $cmC = 0;
            $mtC = 0;
            while($row = mysqli_fetch_assoc($result)){
                $i++;
                if($row['type'] == 'NOFI_CMMNT' || $row['type'] == 'NOFI_REPLY'){
                    $cmC++;
                }else{
                    $mtC++;
                }
            }
            echo '호출 '.$mtC.'건, 댓글 '.$cmC.'건';
        }else{
            echo '0';
        }
    }else{
        echo 0;
    }
?>