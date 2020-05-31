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

if(isTitCh){
    document.title += " - FNBase"
}