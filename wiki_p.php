<?php
    ini_set('pcre.backtrack_limit', '3000000000000000');
    require_once 'setting.php';
    function myUrlEncode($string) {
        $replacements = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%2523', '%5B', '%5D');
        $entities = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        return preg_replace('/(amp%253B)+(apos|quot)%253B/', "&$2;", str_ireplace('%2F', '/', str_replace($entities, $replacements, $string)));
    }
    function myUrlDecode($string) {
        $replacements = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%2523', '%5B', '%5D');
        $entities = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
        return urldecode(preg_replace('/(&|%2526)(amp(%253B|;))+(apos|quot)(%253B|;)/', "&$4;", str_ireplace('%2F', '/', str_replace($replacements, $entities, $string))));
    }

    function documentRender($doc, $isTitle = FALSE, $isIncluded = FALSE, $depth = 0){
        if ($depth > 10) return "<red>오류!</red> 순환 끼워넣기가 발견되었습니다: <b>".$doc."</b>.";
        //매직 워드
        global $fnwTitle;
                //표시 문구
                $doc = str_ireplace('___404TEXT___',
                '<span class="muted">"</span>___PAGERAWNAME___<span class="muted">"이라는 문서는 없습니다.<br><a onclick="wikiEdit(\''.$fnwTitle.'\')">새로 만들거나</a>, 다시 검색해보세요.</span>', $doc); #찾을 수 없음
                $doc = str_ireplace('___COPYDEL\((.+)\)___',
                '<span class="muted">"</span>___PAGERAWNAME___<span class="muted">"의 이전 버전 문서가 "$1"의 내용을 도용한 것으로 확인되어 삭제되었습니다.<br>
                작성이 금지된 것이 아니오니, 새 문서를 작성하실 수 있습니다.</span>', $doc); #저작권 위반 문서 삭제
                $doc = str_ireplace('___DONOT\((.+)\)___',
                '<span class="muted">"</span>___PAGERAWNAME___<span class="muted">"은 작성하거나 편집할 수 있는 문서가 아닙니다.<br>
                사유 : $1</span>', $doc); #부적절한 문서 삭제
        $doc = str_ireplace('___PAGERAWNAME___', $fnwTitle, $doc);

        global $document;
        $doc = str_ireplace('___PAGENAME___', $document['title'], $doc);
        $doc = str_ireplace('___PAGECREATED___', $document['at'], $doc);
        $doc = str_ireplace('___PAGERATE___', $document['rate'], $doc);
        $doc = str_ireplace('___PAGEVIEW___', $document['viewcount'], $doc);
        $doc = str_ireplace('___PAGECAT___', $document['category'], $doc);
        if(preg_match('/(.+)\/(.+)/mu', $document['title'])){
            $parent = explode('/', $document['title']);
            $doc = str_ireplace('___PARENT___', $parent[0], $doc);
        }else{
            $doc = str_ireplace('___PARENT___', '', $doc);
        }
        $doc = str_ireplace('___PAGEACL___', $document['ACL'], $doc);

        global $fnVersion;
        $doc = str_ireplace('___VERSION___', $fnVersion, $doc);
        global $fnTitle;
        $doc = str_ireplace('___SITENAME___', $fnTitle, $doc);
        global $fnTz;
        $doc = str_ireplace('___TIMEZONE___', $fnTz, $doc);
        global $fnLang;
        $doc = str_ireplace('___LANGUAGE___', $fnLang, $doc);

        $doc = str_ireplace('___BR___', '<br>', $doc);
        $doc = str_ireplace('___NOW___', time(), $doc);
        $doc = str_ireplace('___DATETIME___', date('Y-m-d H:i:s'), $doc);
        $doc = str_ireplace('___ADDRESS___', get_client_ip(), $doc);

        if($_SESSION['fnUserId']){
            $doc = str_ireplace('___YOU___', $_SESSION['fnUserName'], $doc);
        }else{
            $doc = str_ireplace('___YOU___', get_client_ip(), $doc);
        }

        //내용 처리
        if($isTitle === 'discuss'){
            $doc = preg_replace('/\#([1-9][0-9]*)/mu', '<a href="#discuss_$1">#$1</a>', $doc);
            $doc = preg_replace("/\[\[([^\[]+)(\||\[\]|\[or\])([^\[]+)\]\]/mu", '<a href="/w/$1">$3</a>', $doc);
            $doc = preg_replace("/\[\[([^\[]+)\]\]/mu", '<a href="/w/$1">$1</a>', $doc);
        }elseif($isTitle){
            $doc = preg_replace('/___SPECIAL___/mu', '특수', $doc);
            $doc = preg_replace('/___DISCUSS___/mu', '토론', $doc);
        }else{
            $doc = preg_replace('/^ ?## *([^\n]+)/mu', '', $doc);

            global $conn;
            if(preg_match_all('/([^\{\n]|^|<\/h[1-5]>){{([^\{\}\|]+)\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*}}/mu', $doc, $title)) {
                for($i = 0; $i < count($title[0]); $i++) {
                    $incT = '틀/'.$title[2][$i];
                    $inc1 = $title[3][$i];
                    $inc2 = $title[4][$i];
                    $inc3 = $title[5][$i];
                    $inc4 = $title[6][$i];
                    $inc5 = $title[7][$i];
                    $inc6 = $title[8][$i];
                    $inc7 = $title[9][$i];
                    $inc8 = $title[10][$i];
                    $inc9 = $title[11][$i];
                    if($title[12][$i]){
                        $inc10 = $title[12][$i];
                        $inc11 = $title[13][$i];
                        $inc12 = $title[14][$i];
                        $inc13 = $title[15][$i];
                        $inc14 = $title[16][$i];
                        $inc15 = $title[17][$i];
                        $inc16 = $title[18][$i];
                        if($title[19][$i]){
                            $inc17 = $title[19][$i];
                            $inc18 = $title[20][$i];
                            $inc19 = $title[21][$i];
                            $inc20 = $title[22][$i];
                        }
                    }
                    if ($incT == $document['title']) {
                        continue;
                    }

                    $incA = $title[0][$i];

                    $sql = "SELECT `content` FROM `_article` WHERE `title` = '$incT'";
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) !== 1){
                        echo '<strong>끼워넣은 문서가 존재하지 않습니다!</strong><br><a href="/e/'.myUrlEncode($incT).'">'.$incT.'</a><br>';
                        continue;
                    }
                    $row = mysqli_fetch_assoc($result);
                    $incCon = documentRender($row['content'], FALSE, TRUE, $depth+1);
                    if($inc10){
                        $incCon = str_ireplace('$10', $inc10, $incCon);
                        $incCon = str_ireplace('$11', $inc11, $incCon);
                        $incCon = str_ireplace('$12', $inc12, $incCon);
                        $incCon = str_ireplace('$13', $inc13, $incCon);
                        $incCon = str_ireplace('$14', $inc14, $incCon);
                        $incCon = str_ireplace('$15', $inc15, $incCon);
                        $incCon = str_ireplace('$16', $inc16, $incCon);
                        if($inc17){
                            $incCon = str_ireplace('$17', $inc17, $incCon);
                            $incCon = str_ireplace('$18', $inc18, $incCon);
                            $incCon = str_ireplace('$19', $inc19, $incCon);
                            $incCon = str_ireplace('$20', $inc20, $incCon);
                        }
                    }
                    $incCon = str_ireplace('$1', $inc1, $incCon);
                    $incCon = str_ireplace('$2', $inc2, $incCon);
                    $incCon = str_ireplace('$3', $inc3, $incCon);
                    $incCon = str_ireplace('$4', $inc4, $incCon);
                    $incCon = str_ireplace('$5', $inc5, $incCon);
                    $incCon = str_ireplace('$6', $inc6, $incCon);
                    $incCon = str_ireplace('$7', $inc7, $incCon);
                    $incCon = str_ireplace('$8', $inc8, $incCon);
                    $incCon = str_ireplace('$9', $inc9, $incCon);

                    $incCon = preg_replace("/\[\[분류\/([^\[]+)\]\]/mu", '', $incCon);
                    $doc = str_ireplace($incA, $incCon, $doc);

                    if ($i > 300) {
                        die('첨부한 끼워넣기가 너무 많습니다!');
                    }
                }
            }

            if($isIncluded){
                $doc = preg_replace('/\[noinclude\](.|\n)*\[\/noinclude\]/mu', '', $doc);
            }else{
                $doc = preg_replace('/\[includeonly\](.|\n)*\[\/includeonly\]/mu', '', $doc);
            }

            $doc = str_ireplace('[includeonly]', '', $doc);
            $doc = str_ireplace('[/includeonly]', '', $doc);
            $doc = str_ireplace('[noinclude]', '', $doc);
            $doc = str_ireplace('[/noinclude]', '', $doc);

            $doc = preg_replace("/----/m", '<hr>', $doc);

                if(!$isTitle){
                    $aH = 'class="aHeading"';
                }
            $doc = preg_replace('/(\n|^) (\*\*\*) *(.+)/mu', '<ul><ul><ul><li>$3</li></ul></ul></ul>', $doc);
            $doc = preg_replace('/(\n|^) (\*\*) *(.+)/mu', '<ul><ul><li>$3</li></ul></ul>', $doc);
            $doc = preg_replace('/(\n|^) (\*) *(.+)/mu', '<ul><li>$3</li></ul>', $doc);

            $doc = preg_replace('/(\n|^|\()({-)/mu', '<ol>', $doc);
            $doc = preg_replace('/(\n|^|\))(-})/mu', '</ol>', $doc);
            $doc = preg_replace('/(\n|^|\()({=)/mu', '<ul>', $doc);
            $doc = preg_replace('/(\n|^|\))(=})/mu', '</ul>', $doc);
            $doc = preg_replace('/(\n|^) (-) *(.+)/mu', '<li>$3</li>', $doc);

            $doc = preg_replace('/(\n|^) *::: *(.+)/mu', '<indent><indent><indent>$2</indent></indent></indent><br>', $doc);
            $doc = preg_replace('/(\n|^) *:: *(.+)/mu', '<indent><indent>$2</indent></indent><br>', $doc);
            $doc = preg_replace('/(\n|^) *: *(.+)/mu', '<indent>$2</indent><br>', $doc);

            $doc = preg_replace('/(\n|^) *(>|&gt;) *([^\n]+)/mu', '<blockquote>$3</blockquote>', $doc);

            $doc = preg_replace('/{{{#!wiki style="([^}]+)"\s([^}]+)}}}/mu', '<div style="$1">$2</div>', $doc);
            $doc = preg_replace('/{{{#(\w{3,6}) ([^}]+)}}}/mu', '<span style="color:#$1">$2</span>', $doc);
            $doc = preg_replace('/{{{\+([1-5]) ([^}]+)}}}/mu', '<span class="size_p_$1">$2</span>', $doc);
            $doc = preg_replace('/{{{-([1-5]) ([^}]+)}}}/mu', '<span class="size_m_$1">$2</span>', $doc);
            $doc = preg_replace('/{{{([^#+]+)}}}/mu', '<pre>$1</pre>', $doc);

            $doc = preg_replace('/\(\(\(#!wiki style="([^}]+)"\s([^}]+)\)\)\)/mu', '<div style="$1">$2</div>', $doc);
            $doc = preg_replace('/\(\(\(#(\w{3,6}) ([^}]+)\)\)\)/mu', '<span style="color:#$1">$2</span>', $doc);
            $doc = preg_replace('/\(\(\(\+([1-5]) ([^}]+)\)\)\)/mu', '<span class="size_p_$1">$2</span>', $doc);
            $doc = preg_replace('/\(\(\(-([1-5]) ([^}]+)\)\)\)/mu', '<span class="size_m_$1">$2</span>', $doc);
            $doc = preg_replace('/\(\(\(([^#+]+)\)\)\)/mu', '<pre>$1</pre>', $doc);

            $doc = preg_replace("/\[\[(이미지|사진|파일):([^\[]+\.(png|jpg|jpeg|gif|svg|webp))\]\]/mu", '<img style="max-width:100%" src="$2">', $doc);
            $doc = preg_replace("/\[\[(이미지|사진|파일):([^\[]+\.(png|jpg|jpeg|gif|svg|webp))\|\]\]/mu", '<img style="width:100%" src="$2">', $doc);
            $doc = preg_replace("/\[\[(이미지|사진|파일):([^\[]+\.(png|jpg|jpeg|gif|svg|webp))\|([0-9a-z-:;%]+)\]\]/mu", '<img class="center" style="width:$4" src="$2">', $doc);

            $doc = preg_replace("/\[\[(비디오|동영상):([^\[]+\.(mp4|avi|mkv|mov|wmv|ogg|flv|webm))\]\]/mu", '<video height="240" style="max-width:100%" src="$2" preload="metadata" controls>', $doc);
            $doc = preg_replace("/\[\[(비디오|동영상):([^\[]+\.(mp4|avi|mkv|mov|wmv|ogg|flv|webm))\|([0-9a-z%]+)\]\]/mu", '<video height="$3" style="max-width:100%" src="$2" preload="metadata" controls>', $doc);
            $doc = preg_replace('/\[\[유튜브:(https:\/\/(www\.|m\.)?(youtube\.com\/watch\?v=|youtu\.be))?([^< \n]+)\]\]/mu', '<iframe src="https://youtube.com/embed/$4" height="240" width="100%" allowfullscreen></iframe>',$doc);

            $doc = preg_replace("/\[\[(오디오|녹음|음성 파일):([^\[]+\.(mp3|wav|ogg))\]\]/mu", '<audio controls><source src="$2" type="audio/$3" /></audio>', $doc);

            $doc = preg_replace("/\[\[(외부|밖|바깥):([^\[]+)(\||\[or\]|\[\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="$2"><i class="icofont-external-link"></i>$4</a>', $doc);
            $doc = preg_replace("/\[\[(외부|밖|바깥):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="$2"><i class="icofont-external-link"></i>$2</a>', $doc);

            $doc = preg_replace("/\[\[(백|위키백과|위백):([^\[]+)(\||\[or\]|\[\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://ko.wikipedia.org/wiki/$2"><i class="icofont-link"></i> $4</a>', $doc);
            $doc = preg_replace("/\[\[(백|위키백과|위백):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://ko.wikipedia.org/wiki/$2"><i class="icofont-link"></i> 위키백과:$2</a>', $doc);

            $doc = preg_replace("/\[\[(영|위키피디아|영위백):([^\[]+)(\||\[or\]|\[\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://en.wikipedia.org/wiki/$2"><i class="icofont-link"></i> $4</a>', $doc);
            $doc = preg_replace("/\[\[(영|위키피디아|영위백):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://en.wikipedia.org/wiki/$2"><i class="icofont-link"></i> 위키피디아:$2</a>', $doc);

            $doc = preg_replace("/\[\[(리|리브레|리브레위키):([^\[]+)(\||\[or\]|\[\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://librewiki.net/wiki/$2"><i class="icofont-link"></i> $4</a>', $doc);
            $doc = preg_replace("/\[\[(리|리브레|리브레위키):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://librewiki.net/wiki/$2"><i class="icofont-link"></i> 리브레:$2</a>', $doc);

            $doc = preg_replace("/\[\[(남|나무|남간|나무위키):([^\[]+)(\||\[or\]|\[\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://namu.wiki/w/$2"><i class="icofont-link"></i> $4</a>', $doc);
            $doc = preg_replace("/\[\[(남|나무|남간|나무위키):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://namu.wiki/w/$2"><i class="icofont-link"></i> 나무:$2</a>', $doc);

            $doc = preg_replace("/\[\[(픈|안|FNBase):([^\[]+)(\||\[or\]|\[\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="/$2">$4</a>', $doc);
            $doc = preg_replace("/\[\[(픈|안|FNBase):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="/$2">FNBase:$2</a>', $doc);

            $doc = preg_replace("/\[\[(분류|양식):([^\[]+)(\||\[or\]|\[\])([^\[]+)\]\]/mu", '<a href="/w/$1/$2">$4</a>', $doc);
            $doc = preg_replace("/\[\[(분류|양식):([^\[]+)\]\]/mu", '<a href="/w/$1/$2">$1/$2</a>', $doc);

            if(!$isIncluded){
                if(preg_match_all('/\[\[분류\/([^\[]+)\]\]/mu', $doc, $link)) {
                    $catLink = '<span style="font-size:0.8em">분류: </span>';
                    for($i = 0; $i < count($link[0]); $i++) {
                        unset($link_arr, $isAnchor);
                        $linkT = $link[1][$i];

                        $sql = "SELECT `content`, `namespace` FROM `_article` WHERE `title` = '분류/$linkT'";
                        $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) < 1){
                            $linkC = ' error';
                        }else{
                            $row = mysqli_fetch_assoc($result);
                            $ns = $row['namespace'];
                            if($ns == '___SPECIAL___'){
                                $linkC = ' warning';
                            }elseif($ns == '___SITENAME___'){
                                $linkC = ' success';
                            }else{
                                unset($linkC);
                            }
                        }

                        $catLink .= '<a class="label'.$linkC.'" href="/w/분류/'.$linkT.'">'.$linkT.'</a>';

                        if ($i > 100) {
                            die('분류가 너무 많습니다!');
                        }
                    }
                    $doc = preg_replace("/\[\[분류\/([^\[]+)\]\]/mu", '', $doc);
                    $catLink .= '<hr>';
                    echo $catLink;
                }

                if(preg_match_all('/\[\[([^\|\]\[]+)(\||\[or\]|\[\])?([^\[]*?)\]\]/mu', $doc, $link)) {
                    for($i = 0; $i < count($link[0]); $i++) {
                        unset($link_arr, $isAnchor);
                        $linkA = $link[0][$i];
                        $linkT = $link[1][$i];
                            $linkT = str_replace('</td>', '', $linkT);
                        $linkS = $link[3][$i];
                        if(strpos($linkT, '#')){
                            $link_arr = explode('#', $linkT);
                            $linkT = $link_arr[0];
                            $isAnchor = TRUE;
                        }

                        $sql = "SELECT `num` FROM `_article` WHERE `title` = '$linkT'";
                        $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) < 1){
                            $linkC = 'class="link-red" href="/e/';
                        }else{
                            $linkC = 'href="/w/';
                        }

                        if($isAnchor){
                            $linkT .= '#'.$link_arr[1];
                        }

                        if($linkT == $fnwTitle){
                            $bold = "'''";
                        }else{
                            unset($bold);
                        }

                        $linkT = myUrlEncode($linkT);

                        if($linkS == ''){
                            $doc = str_ireplace($linkA, $bold.'<a '.$linkC.$linkT.'">'.myUrlDecode($linkT).'</a>'.$bold, $doc);
                        }else{
                            $doc = str_ireplace($linkA, $bold.'<a '.$linkC.$linkT.'">'.$linkS.'</a>'.$bold, $doc);
                        }
                        if ($i > 5000) {
                            die('하이퍼링크가 너무 많습니다!');
                        }
                    }
                }
            }

            $doc = preg_replace("/'''(.+?)'''/mu", '<strong>$1</strong>', $doc);
            $doc = preg_replace("/''(.+?)''/mu", '<em>$1</em>', $doc);
            $doc = preg_replace("/--(.+?)--/mu", '<strike>$1</strike>', $doc);
            $doc = preg_replace("/~~(.+?)~~/mu", '<span class="muted">$1</span>', $doc);
            $doc = preg_replace("/\^\^(.+?)\^\^/mu", '<sup>$1</sup>', $doc);
            $doc = preg_replace("/,,(.+?),,/mu", '<sub>$1</sub>', $doc);
            $doc = preg_replace("/__(.+?)__/mu", '<u>$1</u>', $doc);
            $doc = preg_replace("/\[ruby\((.+?), ?ruby=(.+?)\)\]/mu", '<ruby><rb>$1</rb><rp>(</rp><rt>$2</rt><rp>)</rp></ruby>', $doc);

            $doc = preg_replace('/(\n|^|<\/span>)\{\|( class=".+")?( style=".+")?/mu', '<table$2$3><tr>', $doc);
            $doc = preg_replace('/(\n|^|<\/span>)\|\-( style="[^"]+")?( class="[^"]+")?/mu', '</tr><tr$2$3>', $doc);
            $doc = preg_replace('/(\n|^|<\/span>)\|( style="[^"]+")?( class="[^"]+")?( rowspan="[0-9]+")?( colspan="[0-9]+")?([^\|\{\}\n]+)/mu', '<td$2$3$4$5>$6</td>', $doc);
            $doc = preg_replace('/(\n|^|<\/span>)\!( style="[^"]+")?( class="[^"]+")?( rowspan="[0-9]+")?( colspan="[0-9]+")?([^\|\{\}\n]+)/mu', '<th$2$3$4$5>$6</th>', $doc);
            $doc = preg_replace('/(\n|^|<\/span>)\|\}(\s)?/mu', '</tr></table>', $doc);

            //매크로
            if(empty($_GET['redi']) and $_GET['redi'] != '0'){
                $doc = preg_replace("/#redirect (.+)/mu", '<script>location.href = "/wiki/$1?from='.$fnwTitle.'";</script>', $doc);
                $doc = preg_replace("/#넘겨주기 (.+)/mu", '<script>location.href = "/wiki/$1?from='.$fnwTitle.'";</script>', $doc);
            }else{
                $doc = preg_replace("/#redirect (.+)/mu", '넘겨주기: $1', $doc);
                $doc = preg_replace("/#넘겨주기 (.+)/mu", '넘겨주기: $1', $doc);
            }
            $doc = preg_replace("/\[anchor\((.+)\)\]/mu", '<a id="$1"></a>', $doc);
            $doc = preg_replace("/\[br\]/mu", '<br>', $doc);

            $doc = str_ireplace('&amp;&amp;', '', $doc);
        }

        if (!$isIncluded && preg_match_all('/\[\*([^\s]+)? (.+?)\]/mu', $doc, $notes)) {
            for($i = 0; $i < count($notes[0]); $i++) {
                $ntAll = $notes[0][$i];
                $ntTitle = $notes[1][$i];
                if(empty($ntTitle) and $ntTitle != '0'){
                    $ntTitle = $i+1;
                }
                $ntDesc = str_replace("'", "’", $notes[2][$i]);

                $doc = str_ireplace($ntAll, '<span id="fn-'.$i.'" class="hidden">'.$ntDesc.'</span><a onclick="wikiNotes(document.querySelector(\'#fn-'.$i.'\').innerHTML)"><sup>['.$ntTitle.']</sup></a>', $doc);

                if ($i > 2000) {
                    die('일반 각주가 너무 많습니다!');
                }
            }
        }

        $doc = preg_replace('/([^=]|^)======([^=]*.+?)======(\n)?/mu', '<h5 class="muted">$2</h5>', $doc);
        $doc = preg_replace('/([^=]|^)=====([^=]*.+?)=====(\n)?/mu', '<span '.$aH.'><h5>$2</h5></span>', $doc);
        $doc = preg_replace('/([^=]|^)====([^=]*.+?)====(\n)?/mu', '<span '.$aH.'><h4>$2</h4></span>', $doc);
        $doc = preg_replace('/([^=]|^)===([^=]*.+?)===(\n)?/mu', '<span '.$aH.'><h3>$2</h3></span>', $doc);
        $doc = preg_replace('/([^=]|^)==([^=]*.+?)==(\n)?/mu', '<span '.$aH.'><h2>$2</h2></span>', $doc);
        $doc = preg_replace('/{{{#!folding ([^\s]+)\s([^}]+)}}}/mu', '<details><summary style="color:#0074d9">$1</summary>$2</details>', $doc);
        $doc = preg_replace('/\(\(\(#!folding ([^\s]+)\s([^}]+)\)\)\)/mu', '<details><summary style="color:#0074d9">$1</summary>$2</details>', $doc);

        return $doc;
    }
?>
