<?php
if(!$isMain){
    $mainHref = '<a class="right" style="font-size:0.75em" href="/misc>nstat">국가 목록 메인</a>';
}
if($board == 'simul'){
    $isHS = ' selected';
}elseif($board == 'virtualnations'){
    $isVN = ' selected';
}else{
    $isAH = ' selected';
}
                echo '<div class="card">
                    <header style="background:#f3f3f3;border-bottom:1px solid #e6e6e6">
                        <h3 id="title"><i class="icofont-globe"></i> 국가 목록</h3><br>
                        <span class="subInfo">
                        '.$boardName.'에서 현재 운영중인 국가 목록입니다.
                        </span>
                        '.$mainHref.'
                    </header>
                    <article class="mainCon" id="mainCon">
                        <select id="whereList" class="form-control" onchange="nstatClearPrev()">
                            <optgroup label="가상 시뮬레이션 채널">
                                <option value="1">1기 (비톨라)</option>
                                <option value="2">2기 (pv)</option>
                                <option value="3">3기 (빨간고양이)</option>
                                <option value="4">4기 (비톨라)</option>
                                <option value="5">5기 전반 (pv)</option>
                                <option value="6">5기 중반 (pv)</option>
                                <option value="7">5기 후반 (pv)</option>
                                <option value="8">6기 통합 (발카리야)</option>
                                <option value="15">7기 통합 (삼도수군통제사)</option>
                                <option value="20">5-1기 초반 (pv)</option>
                                <option value="21">5-1기 중반 (pv)</option>
                                <option value="22"'.$isHS.'>5-1기 후반 (pv)</option>
                            </optgroup>
                            <optgroup label="국가 시뮬레이션 채널">
                                <option value="98"'.$isVN.'>1-1기 (유카냥이)</option>
                            </optgroup>
                            <optgroup label="가상 역사 채널 (임시)">
                                <option value="99"'.$isAH.'>1기 리부트</option>
                            </optgroup>
                        </select>
                        <table class="full list">
                            <thead>
                                <th>국명</th>
                                <th>총점</th>
                            </thead>
                            <tbody id="fetch_change">
                                <tr>
                                    <td><label for="listInfoModal">나라 이름</label><br>
                                    <span class="subInfo">안정/민생/문화/경제/군사</span></td>
                                    <td>총점</td>
                                </tr>
                            </tbody>
                        </table>
                        <span id="fetch_button"> 
                        <button class="full default" style="font-size:0.75em" onclick="wholeList()">모두 보기</button>
                        <h-d><button class="full" onclick="moreList()">10개 더 보기</button></h-d>
                        </span>
                        <br>
                        <span class="subInfo">나라 이름을 클릭하면 세부 정보가 표시됩니다.
                        <h-m><br>(PC) 아래로 스크롤하시면 계속 보입니다.</h-m></span>
                    </article>
                </div>';

?>
<script>
    mLpg = 10;
    endCount = 0;

    function nstatSel(){
        var sel = document.getElementById("whereList");
        var val = sel.options[sel.selectedIndex].value;
        return val;
    }

    function nstatClearPrev(){
        mLpg = 10;
        endCount = 0;
        document.querySelector('#fetch_change').innerHTML = '';
        document.querySelector('#fetch_button').innerHTML = '<button class="full default" style="font-size:0.75em" onclick="wholeList()">모두 보기</button><h-d><button class="full" onclick="moreList()">10개 더 보기</button></h-d>';
    }

    function moreList(){
        if(endCount == 1){
            return;
        }
        selValue = nstatSel();
      fetch('/sub/nstat_p.php?mode=more&pg='+mLpg+'&when='+selValue).then(function(response){
        response.text().then(function(text){
            if(text == 0){
                document.querySelector('#fetch_change').innerHTML = document.querySelector('#fetch_change').innerHTML+'<tr><td colspan="2">표시할 내용의 끝입니다.</td></tr>';
                document.querySelector('#fetch_button').innerHTML = '<button class="full" style="font-size:0.75em" disabled>모두 표시됨</button>';
                endCount = 1;
            }else{
                document.querySelector('#fetch_change').innerHTML = document.querySelector('#fetch_change').innerHTML+text;
            }
        })
        if(response.status != '200'){
          alert('서버 통신 오류')
        }else{
          mLpg = mLpg + 10;
        }
      })
    }

    function moreInfo(arg){
        fetch('/sub/nstat_p.php?mode=info&num='+arg).then(function(response){
            response.text().then(function(text){
                document.querySelector('#fetch_info').innerHTML = text;
            })
            if(response.status != '200'){
                alert('서버 통신 오류')
            }
        })
    }

    function wholeList(){
        if(endCount == 1){
            return;
        }
        selValue = nstatSel();
      fetch('/sub/nstat_p.php?mode=all&when='+selValue).then(function(response){
        response.text().then(function(text){
            if(text != 0){
                document.querySelector('#fetch_change').innerHTML = text;
            }
            document.querySelector('#fetch_change').innerHTML = document.querySelector('#fetch_change').innerHTML+'<tr><td colspan="2">표시할 내용의 끝입니다.</td></tr>';
            document.querySelector('#fetch_button').innerHTML = '<button class="full" style="font-size:0.75em" disabled>모두 표시됨</button>';
        })
        if(response.status != '200'){
            alert('서버 통신 오류')
        }else{
            endCount = 1;
        }
      })
    }

    window.addEventListener('scroll', () => {
	let scrollLocation = document.documentElement.scrollTop; // 현재 스크롤바 위치
	let windowHeight = window.innerHeight; // 스크린 창
	let fullHeight = document.body.scrollHeight; //  margin 값은 포함 x
        if(scrollLocation + windowHeight >= fullHeight){
            moreList();
        }
    })
</script>
<div class="modal full">
    <input id="listInfoModal" type="checkbox" />
    <label for="listInfoModal" class="overlay"></label>
    <article id="fetch_info">
        <header>
            <h3>나라 이름 <span class="muted">(원어)</span></h3>
        </header>
        <section class="content">
            <p>표시되기까지 1~3초 정도 걸릴 수 있습니다.</p>
        </section>
        <footer>
            <label for="listInfoModal" class="button dangerous">
                닫기
            </label>
        </footer>
    </article>
</div>