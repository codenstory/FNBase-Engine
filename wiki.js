if(screen.width <= 1024){
    var isMobile = true;
}else{
    var isMoblie = false;
}

var prevHisNum = false;

function alertWait(){
    alert('열심히 개발중입니다..\n조금만 기다려주세요.');
}

//알림 받아오기
var timeset = 30000;
var noticount = 0;

window.onload = function () {
    if (window.Notification) {
        Notification.requestPermission();
    }
    notify();
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
                alert('서버 통신 오류')
            }
        })

        setTimeout(function setTimeout() {
            notify();
        }, timeset);
    }
}

function wikiEdit(arg){
    fetch('https://fnbase.xyz/wiki_f.php?title='+arg).then(function(response){
        response.text().then(function(text){
            document.querySelector('#mainContent').style.cssText = 'display:none';
            document.querySelector('#editPlace').style.cssText = '';
            document.querySelector("#editPlaceText").innerHTML = text;
        })
        if(response.status != '200'){
            alert('서버 통신 오류')
        }else{
            document.querySelector("#wikiModeText").innerHTML = " (편집)";
            if(isMobile){
                document.querySelectorAll('#wFClose')[0].click();
            }
        }
    })
}

function wikiHistory(arg){
    fetch('https://fnbase.xyz/wiki_h.php?title='+arg).then(function(response){
        response.text().then(function(text){
            document.querySelector('#mainContent').style.cssText = 'display:none';
            document.querySelector('#editPlace').style.cssText = '';
            document.querySelector("#editPlaceText").innerHTML = text;
        })
        if(response.status != '200'){
            alert('서버 통신 오류')
        }else{
            document.querySelector("#wikiModeText").innerHTML = " (기록)";
            if(isMobile){
                document.querySelectorAll('#wFClose')[0].click();
            }
        }
    })
}

function wikiHisRev(arg){
    fetch('https://fnbase.xyz/wiki_h.php?mode=view&num='+arg).then(function(response){
        response.text().then(function(text){
            document.querySelector("#wHrText").innerHTML = text;
        })
        if(response.status != '200'){
            alert('서버 통신 오류')
        }else{
            document.querySelectorAll('#wHrClose')[0].click();
        }
    })
    prevHisNum = arg;
}

function wikiHisRaw(){
    fetch('https://fnbase.xyz/wiki_h.php?mode=raw&num='+prevHisNum).then(function(response){
        response.text().then(function(text){
            document.querySelector("#wHrText").innerHTML = text;
        })
        if(response.status != '200'){
            alert('서버 통신 오류')
        }
    })
}

function wikiDiscuss(arg){
    fetch('https://fnbase.xyz/wiki_d.php?title='+arg).then(function(response){
        response.text().then(function(text){
            document.querySelector('#mainContent').style.cssText = 'display:none';
            document.querySelector('#editPlace').style.cssText = '';
            document.querySelector("#editPlaceText").innerHTML = text;
        })
        if(response.status != '200'){
            alert('서버 통신 오류')
        }else{
            document.querySelector("#wikiModeText").innerHTML = " (토론)";
            if(isMobile){
                document.querySelectorAll('#wFClose')[0].click();
            }
        }
    })
}

function wikiManage(arg){
    fetch('https://fnbase.xyz/wiki_m.php?title='+arg).then(function(response){
        response.text().then(function(text){
            document.querySelector('#mainContent').style.cssText = 'display:none';
            document.querySelector('#editPlace').style.cssText = '';
            document.querySelector("#editPlaceText").innerHTML = text;
        })
        if(response.status != '200'){
            alert('서버 통신 오류')
        }else{
            document.querySelector("#wikiModeText").innerHTML = " (조정)";
            if(isMobile){
                document.querySelectorAll('#wFClose')[0].click();
            }
        }
    })
}

function wikiPreview(arg){
    const form = document.getElementById('contentForm');
        event.preventDefault();
        const formattedFormData = new FormData(form);
        postData(formattedFormData);

    async function postData(formattedFormData){
        const response = await fetch('https://fnbase.xyz/wiki_v.php?title='+arg,{
            method: 'POST',
            body: formattedFormData
        });
        const data = await response.text();
        document.querySelector("#wPreText").innerHTML = data;
        document.querySelectorAll('#wPreClose')[0].click();
    }
}

function wikiSave(){
    const form = document.getElementById('contentForm');
    form.addEventListener('click', function(event){
        event.preventDefault();
        const formattedFormData = new FormData(form);
        postData(formattedFormData);
    });

    async function postData(formattedFormData){
        const response = await fetch('https://fnbase.xyz/wiki_s.php',{
            method: 'POST',
            body: formattedFormData
        });
        const data = await response.text();
        location.reload();
    }
}

function editCancle(){
    document.querySelector('#mainContent').style.cssText = '';
    document.querySelector('#editPlace').style.cssText = 'display:none';
    document.querySelector("#wikiModeText").innerHTML = '';
}

function foldSpan(){
    if(document.querySelector('.foldSpan').style.cssText == ''){
        document.querySelector('.foldSpan').style.cssText = 'display:none';
    }else{
        document.querySelector('.foldSpan').style.cssText = '';
    }
}

function wikiRollback(arg, num){
    fetch('https://fnbase.xyz/wiki_v.php?mode=rollback&title='+arg+'&num='+num).then(function(response){
        response.text().then(function(text){
            alert(text);
            if(text.length > 10){
                location.href = '/h/'+arg;
            }
        })
        if(response.status != '200'){
            alert('서버 통신 오류')
        }
    })
}

function wikiRollConf(){
    if(prevHisNum != false){
        if(confirm('정말 #'+prevHisNum+' 판으로 되돌리시겠습니까?')){
            wikiRollback(wikiTitle, prevHisNum);
        }
    }
}