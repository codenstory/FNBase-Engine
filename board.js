//알림 받아오기
var timeset = 20000;
var noticount = 0;

window.onload = function () {
    if (window.Notification) {
        Notification.requestPermission();
    }
    setTimeout(function setTimeout() {
        notify();
    }, timeset);
}

window.onresize = function () {
    if(document.body.offsetHeight < 450){
        document.getElementById('nofiBox').style.display = 'none';
    }else{
        document.getElementById('nofiBox').style.display = '';
    }
}

function notify() {
    if(noticount < 10){
        fetch('/php/notifyjs.php').then(function(response){ //알림이 있는지 물음
            response.text().then(function(text){
                if(text == 0){
                    timeset += 90000;
                }else{
                    if (Notification.permission !== 'granted') {
                        console.log('알림 수신 거부됨.');
                    }
                    else {
                        var notification = new Notification('FNBase - 새 알림이 있습니다.', {
                            icon: 'https://fnbase.xyz/icon.png',
                            body: text,
                        });
        
                        notification.onclick = function () {
                            window.open('http://fnbase.xyz/nofi', '_self');
                        };
                    }
                }
            })
            if(response.status != '200'){
                console.log('서버 통신 오류');
            }
        })

        setTimeout(function setTimeout() {
            notify();
        }, timeset);
    }
}

function addRp(arg){ //답글 달기
    if(document.getElementById('reply-' + arg).style.display == 'none'){
        document.getElementById('reply-' + arg).style.display = '';
        document.getElementById('addR' + arg).innerHTML = '<i class="icofont-error"></i><h-m> 창 닫기</h-m>';
    }else{
        document.getElementById('reply-' + arg).style.display = 'none';
        document.getElementById('addR' + arg).innerHTML = '<i class="icofont-comment"></i><h-m> 답글 달기</h-m>';
    }
    if(FnbcValRep){
        document.getElementById('txtA' + arg).parentNode.innerHTML = document.getElementById('txtA' + arg).parentNode.innerHTML + '<img height="200" src="/fnbcon/'+FnbcValRep+'">';
        document.getElementById('txtA' + arg).value = FnbcValRep;
        document.getElementById('txtA' + arg).style.display = 'none';
        document.getElementById('fnbCA' + arg).innerHTML = FnbcHidRep;
    }
}

function editC(arg){ //댓글 수정
    if(document.getElementById('editC-' + arg).style.display == 'none'){
        document.getElementById('editC-' + arg).style.display = '';
        document.getElementById('ediB' + arg).innerHTML = '<i class="icofont-error"></i><h-m> 창 닫기</h-m>';
    }else{
        document.getElementById('editC-' + arg).style.display = 'none';
        document.getElementById('ediB' + arg).innerHTML = '<i class="icofont-eraser"></i><h-m> 수정</h-m>';
    }
}

function cmtHR(arg){ //부모 댓글 강조
    if(document.querySelector('#cmt-' + arg + ' div section').style.backgroundColor == 'yellow'){
        document.querySelector('#cmt-' + arg + ' div section').style.backgroundColor = '';
    }else{
        document.querySelector('#cmt-' + arg + ' div section').style.backgroundColor = 'yellow';
    }
}

function ctrSM(arg){ //컨트롤+엔터 폼 전송
    var input = document.getElementById('txtA' + arg);
    input.addEventListener("keydown", function(event) {
        if (event.which == 13 && event.ctrlKey) {
            document.getElementById('addB' + arg).click();
            document.getElementById('addB' + arg).parentElement.innerHTML = '';
        }
    });
}

function ctrSMe(arg){ //컨트롤+엔터 수정 폼 전송
    var input = document.getElementById('txtE' + arg);
    input.addEventListener("keydown", function(event) {
        if (event.which == 13 && event.ctrlKey) {
            document.getElementById('edcB' + arg).click();
            document.getElementById('edcB' + arg).parentElement.innerHTML = '';
        }
    });
}

function openFNBCON(){ //픈비콘 열기
    if(document.getElementById('fnbcon').style.display == 'none'){
        document.getElementById('fnbcon').style.display = '';
        document.getElementById('fnbcB').innerHTML = '<i class="icofont-simple-smile"></i> 선택창 닫기';
        document.getElementById('txtA0').style.display = 'none';
        original = document.getElementById('txtA0').value;
        document.getElementById('txtA0').value = '';
    }else{
        document.getElementById('fnbcon').style.display = 'none';
        document.getElementById('fnbcPreDiv').style.display = 'none';
        document.getElementById('fnbcB').innerHTML = '<i class="icofont-simple-smile"></i> 픈비콘 사용';
        document.getElementById('txtA0').value = original;
        document.getElementById('txtA0').style.display = '';
        document.getElementById('fnbcI').innerHTML = '';
    }
}

function selectFNBCON(arg){ //픈비콘 목록 보이기
    if(document.getElementById(arg).style.display == 'none'){
        var divsToHide = document.getElementsByClassName('ico');
        for(var i = 0; i < divsToHide.length; i++){
            divsToHide[i].style.display = "none";
        }
        document.getElementById(arg).style.display = '';
    }else{
        var divsToHide = document.getElementsByClassName('ico');
        for(var i = 0; i < divsToHide.length; i++){
            divsToHide[i].style.display = "none";
        }
    }
}

function viewFNBCON(arg){ //픈비콘 보기
    arg = arg.replace(/\/(.+)/, '');
    location.href = '/emoticon>'+arg;
}

function FNBCON(arg){ //픈비콘 선택
    document.getElementById('txtA0').value = arg;
    document.getElementById('fnbcPreDiv').style.display = '';
    document.getElementById('fnbcPreview').src = '/fnbcon/'+arg;
    document.getElementById('fnbcI').innerHTML = '<input type="hidden" name="fnbcon" value="FNBCON_CMT">';
    FnbcValRep = document.getElementById('txtA0').value;
    FnbcHidRep = '<input type="hidden" name="fnbcon" value="FNBCON_REP">';
}

function viewBoardKick(arg){
    fetch('https://fnbase.xyz/sub/board_kick.php?b='+arg).then(function(response){
        response.text().then(function(text){
            document.querySelector("#viewBoardKick").innerHTML = text;
        })
        if(response.status != '200'){
            console.log('서버 통신 오류');
        }
    })
}

function tempSave(){
    if(document.querySelector('.note-editable')){
        content = document.querySelector('.note-editable').innerHTML;
        value = '.note-editable';
    }else if(document.querySelector('.ql-editor')){
        content = document.querySelector('.ql-editor').innerHTML;
        value = '.ql-editor';
    }else{
        content = document.querySelector('#mainEditor').innerHTML;
        value = '#mainEditor';
    }

    fetch('https://fnbase.xyz/sub/tempsave.php?content='+content).then(function(response){
        if(response.status == '403'){
            alert('로그인 되어있지 않음!');
        }else if(response.status != '200'){
            alert('서버 통신 오류');
        }else if(response.status == '200'){
            alert('저장 완료!')
        }
    })
}
function tempLoad(){
    if(document.querySelector('.note-editable')){
        content = document.querySelector('.note-editable').innerHTML;
        value = '.note-editable';
    }else if(document.querySelector('.ql-editor')){
        content = document.querySelector('.ql-editor').innerHTML;
        value = '.ql-editor';
    }else{
        content = document.querySelector('#mainEditor').innerHTML;
        value = '#mainEditor';
    }

    fetch('https://fnbase.xyz/sub/tempsave.php').then(function(response){
        response.text().then(function(text){
            document.querySelector(value).innerHTML = text;
        })
        if(response.status == '403'){
            alert('로그인 되어있지 않음!');
        }else if(response.status != '200'){
            alert('서버 통신 오류');
        }else if(response.status == '200'){
            alert('불러오기 완료!')
        }
    })
}

if(isTitCh){
    document.title += " - FNBase"
}

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/pwa.js') // serviceWorker 파일 경로
            .then((reg) => {
                console.log('Service worker registered.', reg);
            })
            .catch(e => console.log(e));
    });
}