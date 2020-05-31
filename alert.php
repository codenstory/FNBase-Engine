<?php
switch ($idAlert) { #알림창
    case 'mailfail':
        echo '
        <input type="checkbox" id="mailfail">
        <article class="card" style="text-align:center;margin-top:7px">
            <p>메일이 발송되지 않았습니다! 관리자에게 문의해주세요.</p>
            <label for="mailfail" class="close">×</a>
        </article>
        ';
        break;
    case 'plslogin':
        echo '
        <input type="checkbox" id="plslogin">
        <article class="card" style="text-align:center;margin-top:7px">
            <p>로그인 해주세요.</p>
            <label for="plslogin" class="close">×</a>
        </article>
        ';
        break;
    case 'empty':
        echo '
        <input type="checkbox" id="empty">
        <article class="card" style="text-align:center;margin-top:7px">
            <p>표시할 내용이 없습니다..</p>
            <label for="empty" class="close">×</a>
        </article>
        ';
        break;
    case 'wrongContent':
        echo '
        <input type="checkbox" id="wrongContent">
        <article class="card" style="text-align:center;margin-top:7px">
            <p>글이 삭제되었거나, 주소를 잘못 입력하셨습니다.</p>
            <label for="wrongContent" class="close">×</a>
        </article>
        ';
        break;
    case 'readonly':
        echo '
        <input type="checkbox" id="readonly">
        <article class="card" style="text-align:center;margin-top:7px">
            <p>읽기 전용 게시판입니다.</p>
            <label for="readonly" class="close">×</a>
        </article>
        ';
        break;
    case 'disabled':
        echo '
        <input type="checkbox" id="disabled">
        <article class="card" style="text-align:center;margin-top:7px">
            <p>비활성화 된 게시판입니다.</p>
            <label for="disabled" class="close">×</a>
        </article>
        ';
        break;


    case 'kicked':
        echo '
        <input type="checkbox" id="mailfail">
        <article class="card" style="text-align:center;margin-top:7px">
            <p>'.$alTime.' 까지 접근이 제한되셨습니다.</p>
            <label for="mailfail" class="close">×</a>
        </article>
        ';
        break;
    case 'notice':
        require_once './setting.php';
        include_once './func.php';
        $alNotice = textAlter($alNotice, 2);
        echo '
        <input type="checkbox" id="mailfail">
        <article class="card" style="text-align:center;margin-top:7px">
            '.$alNotice.'
            <label for="mailfail" class="close">×</a>
        </article>
        ';
        break;
}
?>