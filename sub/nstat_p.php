<?php
    include_once '../setting.php';
    if(!$_GET['when']){
        $where = 'WHERE `기수` = 7';
    }elseif($_GET['when'] == 'all'){
        $where = '';
    }else{
        $when = preg_replace('[^0-9]', '', $_GET['when']);
        $where = 'WHERE `기수` = \''.$when.'\'';
    }

    if($_GET['mode'] == 'info'){
        $pg = preg_replace('[^0-9]', '', $_GET['num']);
        $sql = "SELECT * FROM `가시챈` WHERE `번호` = $pg";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) == 0){
            die('서버 오류!');
        }
        $row = mysqli_fetch_assoc($result);
        echo '<header>
                <h3>'.$row['국명'].' <span class="muted">('.$row['원어'].')</span></h3>
            </header>
            <section class="content">
                <p align="center"><img height="200" src="https://fnbase.xyz/hsls/'.$row['국기'].'"></p>
                <hr>
                <p>'.$row['설명'].'</p>
                <hr>
                <p>우방국 : '.$row['우방'].'</p>
                <p>적대국 : '.$row['적대'].'</p>
                <p>소속 세력 : '.$row['세력'].'</p>
                <p>정치 체제 : '.$row['체제'].'</p>
            </section>
            <footer>
                <label for="listInfoModal" class="button dangerous">
                    닫기
                </label>
            </footer>';
    }elseif($_GET['mode'] == 'all'){
        $sql = "SELECT `번호`,`국명`,`안정`,`민생`,`문화`,`경제`,`군사`,`유저` FROM `가시챈` $where ORDER BY `안정` + `민생` + `문화` + `경제` + `군사` DESC LIMIT 500";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) == 0){
            echo '0';
        }
        while($row = mysqli_fetch_assoc($result)){
            if($row['유저'] != ''){
                if(strstr($row['유저'], ',') == TRUE){
                    $use_arr = array_map('trim',explode(',', $row['유저']));
                    $use_arr_cnt = count($use_arr);
                    $i = 0;
                    $at = '</span> ';
                    while($use_arr_cnt != $i){
                        $at .= '<a style="font-size:.7em;color:green" href="/u_name/'.$use_arr[$i].'">@'.$use_arr[$i].'</a> ';
                        $i++;
                    }
                }else{
                    $at = '</span> <a style="font-size:.7em;color:green" href="/u_name/'.$row['유저'].'">@'.$row['유저'].'</a>';
                }
            }else{
                $at = '</span> <a style="font-size:.7em;color:green" href="/u_name/pv">[NPC]</a>';
            }
            $total = $row['안정'] + $row['민생'] + $row['문화'] + $row['경제'] + $row['군사'];
            include 'nstat_s.php';
            echo '<tr class="black noGray">
                <td><a href="/w/'.$row['국명'].'(가상 시뮬레이션 채널)">'.$row['국명'].''.$lb.'</a><br>
                <span class="subInfo">'.$row['안정'].'/'.$row['민생'].'/'.$row['문화'].'/'.$row['경제'].'/'.$row['군사'].$at.'
                <span id="list_'.$row['번호'].'"></span></td>
                <td>'.$total.'</td>
            </tr>';
        }
    }else{
        $pg = preg_replace('[^0-9]', '', $_GET['pg']);
        $prev = $pg - 10;

        $sql = "SELECT `번호`,`국명`,`안정`,`민생`,`문화`,`경제`,`군사`,`유저` FROM `가시챈` $where ORDER BY `안정` + `민생` + `문화` + `경제` + `군사` DESC LIMIT $prev, 10";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) == 0){
            echo '0';
        }
        while($row = mysqli_fetch_assoc($result)){
            if($row['유저'] != ''){
                if(strstr($row['유저'], ',') == TRUE){
                    $use_arr = array_map('trim',explode(',', $row['유저']));
                    $use_arr_cnt = count($use_arr);
                    $i = 0;
                    $at = '</span> ';
                    while($use_arr_cnt != $i){
                        $at .= '<a style="font-size:.7em;color:green" href="/u_name/'.$use_arr[$i].'">@'.$use_arr[$i].'</a> ';
                        $i++;
                    }
                }else{
                    $at = '</span> <a style="font-size:.7em;color:green" href="/u_name/'.$row['유저'].'">@'.$row['유저'].'</a>';
                }
            }else{
                $at = '</span> <a style="font-size:.7em;color:green" href="/u_name/pv">[NPC]</a>';
            }
            $total = $row['안정'] + $row['민생'] + $row['문화'] + $row['경제'] + $row['군사'];
            include 'nstat_s.php';
            echo '<tr class="black noGray">
                <td><a href="/w/'.$row['국명'].'(가상 시뮬레이션 채널)">'.$row['국명'].''.$lb.'</a><br>
                <span class="subInfo">'.$row['안정'].'/'.$row['민생'].'/'.$row['문화'].'/'.$row['경제'].'/'.$row['군사'].$at.'
                <span id="list_'.$row['번호'].'"></span></td>
                <td>'.$total.'</td>
            </tr>';
        }
    }
?>