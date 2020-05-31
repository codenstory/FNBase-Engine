<?php
    ini_set('pcre.backtrack_limit', '300000000');
    require_once './setting.php';
    function documentRender($doc, $isTitle = FALSE){
        //표시 문구
        $doc = preg_replace('/___404TEXT___/mu',
        '<span class="muted">"</span>___PAGERAWNAME___<span class="muted">"이라는 문서는 없습니다.<br>새로 만들거나, 다시 검색해보세요.</span>', $doc); #찾을 수 없음
        $doc = preg_replace('/___COPYDEL\((.+)\)___/mu',
        '<span class="muted">"</span>___PAGERAWNAME___<span class="muted">"의 이전 버전 문서가 "$1"의 내용을 도용한 것으로 확인되어 삭제되었습니다.<br>
        작성이 금지된 것이 아니오니, 새 문서를 작성하실 수 있습니다.</span>', $doc); #저작권 위반 문서 삭제
        $doc = preg_replace('/___DONOT\((.+)\)___/mu',
        '<span class="muted">"</span>___PAGERAWNAME___<span class="muted">"은 작성하거나 편집할 수 있는 문서가 아닙니다.<br>
        사유 : $1</span>', $doc); #부적절한 문서 삭제

        //매직 워드
        global $fnwTitle;
        $doc = preg_replace('/___PAGERAWNAME___/mu', $fnwTitle, $doc);

        global $document;
        $doc = preg_replace('/___PAGENAME___/mu', $document['title'], $doc);
        $doc = preg_replace('/___PAGECREATED___/mu', $document['at'], $doc);
        $doc = preg_replace('/___PAGERATE___/mu', $document['rate'], $doc);
        $doc = preg_replace('/___PAGEVIEW___/mu', $document['viewcount'], $doc);
        $doc = preg_replace('/___PAGECAT___/mu', $document['category'], $doc);
        if(preg_match('/(.+)\/(.+)/mu', $document['title'])){
            $parent = preg_replace('/\/(.+)/mu', '', $document['title']);
            $doc = preg_replace('/___PARENT___/mu', $parent, $doc);
        }else{
            $doc = preg_replace('/___PARENT___/mu', '', $doc);
        }
        $doc = preg_replace('/___PAGEACL___/mu', $document['ACL'], $doc);

        global $fnVersion;
        $doc = preg_replace('/___VERSION___/mu', $fnVersion, $doc);
        global $fnTitle;
        $doc = preg_replace('/___SITENAME___/mu', $fnTitle, $doc);
        global $fnTz;
        $doc = preg_replace('/___TIMEZONE___/mu', $fnTz, $doc);
        global $fnLang;
        $doc = preg_replace('/___LANGUAGE___/mu', $fnLang, $doc);

        $doc = preg_replace('/___BR___/mu', '<br>', $doc);
        $doc = preg_replace('/___NOW___/mu', time(), $doc);
        $doc = preg_replace('/___DATETIME___/mu', date('Y-m-d H:i:s'), $doc);
        $doc = preg_replace('/___ADDRESS___/mu', get_client_ip(), $doc);

        if($id){
            $doc = preg_replace('/___YOU___/mu', $_SESSION['fnUserName'], $doc);
        }else{
            $doc = preg_replace('/___YOU___/mu', get_client_ip(), $doc);
        }

        //내용 처리
        if(!$isTitle){
            $doc = preg_replace('/## *([^<\/>\n]+) *\n/mu', '', $doc);

            global $conn;
            if(preg_match_all('/([^\{\n]|^){{([^\{\}\|]+)\|*([^\|\}]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}\|]+)*\|*([^\}]+)*}}/mu', $doc, $title)) {
                for($i = 0; $i < count($title[0]); $i++) {
                    $incT = '틀/'.$title[2][$i];
                    $inc1 = $title[3][$i];
                    $inc2 = $title[4][$i];
                    $inc3 = $title[5][$i];
                    $inc4 = $title[6][$i];
                    $inc5 = $title[7][$i];

                    $incA = $title[0][$i];

                    $sql = "SELECT `content` FROM `_article` WHERE `title` = '$incT'";
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) !== 1){
                        break;
                    }
                    $row = mysqli_fetch_assoc($result);
                    $incCon = documentRender($row['content']);

                    $incCon = str_ireplace('$1', $inc1, $incCon);
                    $incCon = str_ireplace('$2', $inc2, $incCon);
                    $incCon = str_ireplace('$3', $inc3, $incCon);
                    $incCon = str_ireplace('$4', $inc4, $incCon);
                    $incCon = str_ireplace('$5', $inc5, $incCon);

                    $doc = str_ireplace($incA, $incCon, $doc);

                    if ($i > 300) {
                        die('첨부한 끼워넣기가 너무 많습니다!');
                    }
                }
            }
            
            if($fnwTitle == $incT){
                $doc = preg_replace('/\[includeonly\](.|\n)*\[\/includeonly\]/mu', '', $doc);
            }else{
                $doc = preg_replace('/\[noinclude\](.|\n)*\[\/noinclude\]/mu', '', $doc);
            }

            $doc = str_ireplace('[includeonly]', '', $doc);
            $doc = str_ireplace('[/includeonly]', '', $doc);
            $doc = str_ireplace('[noinclude]', '', $doc);
            $doc = str_ireplace('[/noinclude]', '', $doc);

            $doc = preg_replace("/----/mu", '<hr>', $doc);

            $doc = preg_replace('/((https|http):\/\/)([^< \n]+\/[^< \n]+\.(png|jpg|jpeg|gif|webp|svg))/mu', '<img style="max-width:100%" src="$2://$3">',$doc);
            $doc = preg_replace('/((https|http):\/\/)([^< \n]+\/[^< \n]+\.(mp4|mov|avi|ogg|ogv|flv|3gp|webm|mkv))/mu', '<video height="240" style="max-width:100%" src="$2://$3" preload="metadata" controls>',$doc);
            $doc = preg_replace('/https:\/\/(www\.|m\.){0,1}(youtube\.com\/watch\?v=|youtu\.be)([^< \n]+)/mu', '<iframe src="https://youtube.com/embed/$3" height="240" width="100%" allowfullscreen></iframe>',$doc);
            
                $doc = preg_replace('/([^=]|^)======([^=]+?)======/mu', '<h5 class="muted">$2</h5>', $doc);
                $doc = preg_replace('/([^=]|^)=====([^=]+?)=====/mu', '<h5>$2</h5>', $doc);
                $doc = preg_replace('/([^=]|^)====([^=]+?)====/mu', '<h4>$2</h4>', $doc);
                $doc = preg_replace('/([^=]|^)===([^=]+?)===/mu', '<h3>$2</h3>', $doc);
                $doc = preg_replace('/([^=]|^)==([^=]+?)==/mu', '<h2>$2</h2>', $doc);

            $doc = preg_replace('/(\n|^) (-|\*) *([^<\/>\n]+)/mu', '<li>$3</li>', $doc);

            $doc = preg_replace('/(\n|^) *::: *([^<\/>\n]+)/mu', '<indent><indent><indent>$2</indent></indent></indent><br>', $doc);
            $doc = preg_replace('/(\n|^) *:: *([^<\/>\n]+)/mu', '<indent><indent>$2</indent></indent><br>', $doc);
            $doc = preg_replace('/(\n|^) *: *([^<\/>\n]+)/mu', '<indent>$2</indent><br>', $doc);

            $doc = preg_replace('/(\n|^) *(>>>|&gt;&gt;&gt;) *([^\n]+)/mu', '<blockquote><blockquote><blockquote>$3</blockquote></blockquote></blockquote>', $doc);
            $doc = preg_replace('/(\n|^) *(>>|&gt;&gt;) *([^\n]+)/mu', '<blockquote><blockquote>$3</blockquote></blockquote>', $doc);
            $doc = preg_replace('/(\n|^) *(>|&gt;) *([^\n]+)/mu', '<blockquote>$3</blockquote>', $doc);
            
            $doc = preg_replace('/{{{#!folding ([^\s]+)\s([^}]+)}}}/mu', '<a onclick="foldSpan()" href="javascript:void(0)">$1</a><br><span class="foldSpan" style="display:none">$2</span>', $doc);
            $doc = preg_replace('/{{{#!wiki style="([^}]+)"\s([^}]+)}}}/mu', '<div style="$1">$2</div>', $doc);
            $doc = preg_replace('/{{{#(\w{3,6}) ([^}]+)}}}/mu', '<span style="color:#$1">$2</span>', $doc);
            $doc = preg_replace('/{{{\+([1-5]) ([^}]+)}}}/mu', '<span class="size_p_$1">$2</span>', $doc);
            $doc = preg_replace('/{{{-([1-5]) ([^}]+)}}}/mu', '<span class="size_m_$1">$2</span>', $doc);
            $doc = preg_replace('/{{{([^#+]+)}}}/mu', '<pre>$1</pre>', $doc);

            $doc = preg_replace('/\(\(\(#!folding ([^\s]+)\s([^}]+)\)\)\)/mu', '<a onclick="foldSpan()" href="javascript:void(0)">$1</a><br><span class="foldSpan" style="display:none">$2</span>', $doc);
            $doc = preg_replace('/\(\(\(#!wiki style="([^}]+)"\s([^}]+)\)\)\)/mu', '<div style="$1">$2</div>', $doc);
            $doc = preg_replace('/\(\(\(#(\w{3,6}) ([^}]+)\)\)\)/mu', '<span style="color:#$1">$2</span>', $doc);
            $doc = preg_replace('/\(\(\(\+([1-5]) ([^}]+)\)\)\)/mu', '<span class="size_p_$1">$2</span>', $doc);
            $doc = preg_replace('/\(\(\(-([1-5]) ([^}]+)\)\)\)/mu', '<span class="size_m_$1">$2</span>', $doc);
            $doc = preg_replace('/\(\(\(([^#+]+)\)\)\)/mu', '<pre>$1</pre>', $doc);

            $doc = preg_replace("/\[\[(외부|밖|바깥):([^\[]+)(\||\[or\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="$2"><i class="icofont-external-link"></i> $4</a>', $doc);
            $doc = preg_replace("/\[\[(외부|밖|바깥):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="$2"><i class="icofont-external-link"></i> $2</a>', $doc);

            $doc = preg_replace("/\[\[(백|위키백과|위백):([^\[]+)(\||\[or\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://ko.wikipedia.org/wiki/$2"><i class="icofont-link"></i> $4</a>', $doc);
            $doc = preg_replace("/\[\[(백|위키백과|위백):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://ko.wikipedia.org/wiki/$2"><i class="icofont-link"></i> 위키백과:$2</a>', $doc);

            $doc = preg_replace("/\[\[(영|위키피디아|영위백):([^\[]+)(\||\[or\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://en.wikipedia.org/wiki/$2"><i class="icofont-link"></i> $4</a>', $doc);
            $doc = preg_replace("/\[\[(영|위키피디아|영위백):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://en.wikipedia.org/wiki/$2"><i class="icofont-link"></i> 위키피디아:$2</a>', $doc);

            $doc = preg_replace("/\[\[(리|리브레|리브레위키):([^\[]+)(\||\[or\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://librewiki.net/wiki/$2"><i class="icofont-link"></i> $4</a>', $doc);
            $doc = preg_replace("/\[\[(리|리브레|리브레위키):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://librewiki.net/wiki/$2"><i class="icofont-link"></i> 리브레:$2</a>', $doc);

            $doc = preg_replace("/\[\[(남|나무|남간|나무위키):([^\[]+)(\||\[or\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://namu.wiki/w/$2"><i class="icofont-link"></i> $4</a>', $doc);
            $doc = preg_replace("/\[\[(남|나무|남간|나무위키):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://namu.wiki/w/$2"><i class="icofont-link"></i> 나무:$2</a>', $doc);
            
            $doc = preg_replace("/\[\[(픈|픈비|fnb|FNBase):([^\[]+)(\||\[or\])([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://fnbase.xyz/$2"><i class="icofont-link"></i> $4</a>', $doc);
            $doc = preg_replace("/\[\[(픈|픈비|fnb|FNBase):([^\[]+)\]\]/mu", '<a class="ext-link" target="_blank" href="https://fnbase.xyz/$2"><i class="icofont-link"></i> FNBase:$2</a>', $doc);

            if(preg_match_all('/\[\[([^\|\[]+)(\||\[or\])?([^\|\[]*?)\]\]/mu', $doc, $link)) {
                for($i = 0; $i < count($link[0]); $i++) {
                    unset($link_arr, $isAnchor);
                    $linkA = $link[0][$i];
                    $linkT = $link[1][$i];
                    $linkS = $link[3][$i];
                    if(strpos($linkT, '#')){
                        $link_arr = explode('#', $linkT);
                        $linkT = $link_arr[0];
                        $isAnchor = TRUE;
                    }

                    $sql = "SELECT `content` FROM `_article` WHERE `title` = '$linkT'";
                    $result = mysqli_query($conn, $sql);
                    if(mysqli_num_rows($result) < 1){
                        $linkC = 'class="link-red" ';
                    }else{
                        unset($linkC);
                    }

                    if($isAnchor){
                        $linkT .= '#'.$link_arr[1];
                    }

                    if($linkT == $fnwTitle){
                        $bold = "'''";
                    }else{
                        unset($bold);
                    }

                    if($linkS == ''){
                        $doc = str_ireplace($linkA, $bold.'<a '.$linkC.'href="/w/'.$linkT.'">'.$linkT.'</a>'.$bold, $doc);
                    }else{
                        $doc = str_ireplace($linkA, $bold.'<a '.$linkC.'href="/w/'.$linkT.'">'.$linkS.'</a>'.$bold, $doc);
                    }
                    if ($i > 2000) {
                        die('하이퍼링크가 너무 많습니다!');
                    }
                }
            }

            $doc = preg_replace("/''' *(.+?) *'''/mu", '<strong>$1</strong>', $doc);
            $doc = preg_replace("/'' *(.+?) *''/mu", '<em>$1</em>', $doc);
            $doc = preg_replace("/-- *(.+?) *--/mu", '<strike>$1</strike>', $doc);
            $doc = preg_replace("/~~ *(.+?) *~~/mu", '<span class="muted">$1</span>', $doc);
            $doc = preg_replace("/\^\^ *(.+?) *\^\^/mu", '<sup>$1</sup>', $doc);
            $doc = preg_replace("/,, *(.+?) *,,/mu", '<sub>$1</sub>', $doc);
            $doc = preg_replace("/__ *(.+?) *__/mu", '<u>$1</u>', $doc);

            $doc = preg_replace('/(\n|^)\{\|( class=".+")?( style=".+")?/mu', '<table$2$3><tr>', $doc);
            $doc = preg_replace('/(\n|^)\|\-( style="[^"]+")?( class="[^"]+")?/mu', '</tr><tr$2$3>', $doc);
            $doc = preg_replace('/(\n|^)\|( style="[^"]+")?( class="[^"]+")?( rowspan="[1-9]+")?( colspan="[1-9]+")?([^\|\{\}\-\n]+)/mu', '<td$2$3$4$5>$6</td>', $doc);
            $doc = preg_replace('/(\n|^)\!( style="[^"]+")?( class="[^"]+")?( rowspan="[1-9]+")?( colspan="[1-9]+")?([^\|\{\}\-\n]+)/mu', '<th$2$3$4$5>$6</th>', $doc);
            $doc = preg_replace('/(\n|^)\|\}(\s)?/mu', '</tr></table>', $doc);

            //매크로
            if(empty($_GET['redi'])){
                $doc = preg_replace("/#redirect (.+)/mu", '<script>location.href = "/wiki/$1?from='.$fnwTitle.'";</script>', $doc);
                $doc = preg_replace("/#넘겨주기 (.+)/mu", '<script>location.href = "/wiki/$1?from='.$fnwTitle.'";</script>', $doc);
            }else{
                $doc = preg_replace("/#redirect (.+)/mu", '[넘겨주기: $1]($1)', $doc);
                $doc = preg_replace("/#넘겨주기 (.+)/mu", '[넘겨주기: $1]($1)', $doc);
            }
            $doc = preg_replace("/\[anchor\((.+)\)\]/mu", '<a id="$1"></a>', $doc);
            $doc = preg_replace("/\[br\]/mu", '<br>', $doc);

            $doc = str_ireplace('&amp;&amp;', '', $doc);
        }else{
            $doc = preg_replace('/___SPECIAL___/mu', '특수', $doc);
            $doc = preg_replace('/___DISCUSS___/mu', '토론', $doc);
        }
        $doc = preg_replace('/on(\w)+=/mui', 'on $1', $doc);
        return $doc;
    }
?>