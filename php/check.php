<?php
include "../setting.php";

if($_REQUEST['type'] == 'id'){
    $p = preg_replace('/[^a-zA-Zㄱ-ㅎ가-힣0-9_- ]/m', '', $_REQUEST['result'], 'abc');
    $sql = "SELECT * FROM `_account` WHERE `id` = '{$p}'";
    $result = mysqli_query($conn, $sql);
	
	if(mysqli_num_rows($result) >= 1){
		echo '<red>사용하실 수 없는 아이디입니다.</red>';
	}elseif($_REQUEST['result'] != ''){
		echo '<green>사용하실 수 있습니다.</green>';
    }   
}else{
    $p = preg_replace('/[^a-zA-Zㄱ-ㅎ가-힣0-9_- ]/m', '', $_REQUEST['result'], '영한');
    $sql = "SELECT * FROM `_account` WHERE `name` = '{$p}'";
    $result = mysqli_query($conn, $sql);
	
	if(mysqli_num_rows($result) >= 1){
		echo '<red>사용하실 수 없는 닉네임입니다.</red>';
	}elseif($_REQUEST['result'] != ''){
        echo '<green>사용하실 수 있습니다.</green>';
    }   
}
?>