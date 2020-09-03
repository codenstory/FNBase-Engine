<!-- FNBase Engine 2 -->
<!DOCTYPE html>
<html lang="ko-KR">
  <head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <meta name="author" content="FNBase Team">
    <meta name="theme-color" content="#5998d6">
    <meta name="classification" content="html">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 정보 -->
    <title>假國志記</title>
    <meta name="description" content="假象國家 채널의 昭詳한 內容을 事實的으로 記述하였다.">

    <style>
        body, td, .card{
            text-align: center !important;
        }
        table {
            width: 100%;
        }
        span {
            color: gray !important;
        }
        h2, h5, tr, td, mark, footer {
            padding: 0px !important;
            color: black;
        }
        .lit {
            font-size: 0.55em;
        }
        input, select {
            width: 7em !important;
        }
        input.full, textarea, .card {
            width: 70% !important;
            margin: auto;
        }
        input.half {
            width: 34.8% !important;
        }
        select {
            background: 0 0 !important;
        }
        .card {
            color: gray;
            font-weight: lighter !important;
        }
    </style>
    <link rel="stylesheet" href="../default.css">
    <link rel="stylesheet" href="../picnic.css">
      </head>
  <body>
      <header>
          <a href="./nl"><h2>假國志記</h2></a>
          <h5>假象國家 채널의 昭詳한 內容을 事實的으로 記述하였읍니다.</h5>
      </header>
      <hr>
      <article>
        <table><tr></tr>
        <?php
            $l = preg_replace('/[^0-9]/', '', $_GET['l']);
            $n = preg_replace('/[^0-9]/', '', $_GET['n']);
            $b = preg_replace('/[^a-z]/', '', strtolower($_GET['b']));
            include '../setting.php';
            if(empty($l) or $l == '0'){
                $l = 25;
            }elseif($l > 100){
                $l = 100;
            }
            if(empty($n) or $n == '0'){
                $n = 0;
            }
            if(empty($b) or $b == '0'){
                $b = 'main';
            }

            switch ($b) {
                case 'main':
                    $bn = '本記 (본기)';
                    break;
                case 'doc':
                    $bn = '書 (서)';
                    break;
                case 'list':
                    $bn = '表 (표)';
                    break;
                case 'sub':
                    $bn = '列傳 (열전)';
                    break;
                case 'others':
                    $bn = '載記 (재기)';
                    break;
                
                default:
                    $bn = '(미상)';
                    break;
            }
                
            echo '<mark><b>'.$bn.'</b></mark><br>';
            $sql = "SELECT * FROM `아카이브` WHERE `board` = '$b' and `no` > $n ORDER BY `no` LIMIT $l";
            $result = mysqli_query($conn, $sql);
            while($row = mysqli_fetch_assoc($result)){
                echo '<tr><td>
                <b><span class="lit">'.$row['no'].'. </span>'.$row['title'].'</b>
                <span class="lit"> - '.$row['name'].'</span><br>
                <article class="card">'.nl2br($row['comment']).'</article>
                </td></tr>';
            }
            $sql = "SELECT `no` FROM `아카이브` WHERE `board` = '$b' ORDER BY `no` DESC LIMIT 1";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            $no = $row['no'] + 1;
        ?>
        </table>
        <br>
            <form method="get" action="./nl">
                <input type="number" name="n" placeholder="시작 번호">
                <input type="number" name="l" placeholder="표시 개수" max="100">
                <select name="b">
                    <option value="doc">書 (서)</option>
                    <option value="list">表 (표)</option>
                    <option value="sub">列傳 (열전)</option>
                    <option value="others">載記 (재기)</option>
                    <option value="main" selected>本記 (본기)</option>
                </select>
                <input type="submit" value="閱覽">
            </form>
      </article>
      <hr>
      <footer>
        載記는 所謂 '亞流챈'에 대한 簡略한 記錄입니다.<br>
        本記는 '總權者'와 '局長'을. 列傳은 그 外 이름난 名士들을 記述합니다.<br>
        表는 곧 '年表'입니다. 書는 곧 '志'입니다. 風習과 名文, 其他 雜說을 모아놓았읍니다.
      </footer>
      <?php
        $sql = "SELECT `isAdmin` FROM `_account` WHERE `id` = \"".$_SESSION['fnUserId'].'"';
        $result = mysqli_query($conn, $sql);
        $iA = mysqli_fetch_assoc($result);
            if($iA['isAdmin']){
                echo '<hr><h3>등록</h3>';
                ?>
                <form action="/sub/arsave.php" method="POST">
                    <input class="full" placeholder="제목" name="title">
                    <textarea name="comm" placeholder="내용 (가급적 100자 이내)"></textarea>
                    <input class="half" type="number" placeholder="번호" value="<?=$no?>" name="num">
                    <input class="half" placeholder="작성자" value="<?=$_SESSION['fnUserName']?>" name="name">
                    <input class="full" type="submit" value="등록">
                    <input type="hidden" value="<?=$b?>" name="board">
                    <input type="hidden" value="<?=$bn?>" name="from">
                </form>
                <p>
                    수정은 글 번호 맞춰서, 제목이나 내용은 처음부터 다시 써야함.<br>
                    ex) 46번 수정 원함 -> 글 번호에 46번 넣고 제목, 내용 입력
                </p>
                <?php
            }
      ?>
      <hr>
            (c) 2020 假國志記 (가국지기)
  </body>
</html>