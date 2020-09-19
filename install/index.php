<?php
if(file_exists('../setting.php')){
    die('설정 파일이 이미 있습니다.');
}
?>

<!DOCTYPE html>
<html lang="ko-KR">
    <head>
        <meta charset="UTF-8">
        <meta name="robots" content="noarchive">
        <meta name="author" content="FNBase Team">
        <meta name="theme-color" content="#2363ad">
        <meta name="classification" content="html">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
        <!-- 정보 -->
        <title>FNBE Install</title>
        <meta name="description" content="FNBase Engine Install Page">
        <meta property="og:type" content="website">
    
        <!-- 불러오기 -->
        <style>
            @media (min-width: 300px) and (max-width: 1800px) {
                html {
                    scrollbar-width: none;
                    scrollbar-color: gainsboro transparent;
                }
                h-m {
                    display: none;
                }
                .hidMob {
                    display:none;
                }
                .brand-r h-d {
                    font-size: 0.7em;
                }
            }
            @media (min-width: 1900px) {
                html {
                    scrollbar-width: thin;
                    scrollbar-color: gainsboro transparent;
                }
                h-d {
                    display: none;
                }
            }
            html { overflow-y:scroll; word-break: break-word }
            footer { border-top: 1px solid gray }
            article {
                padding: 1em;
            } article p {
                margin: 0;
            }
        </style>
        <link rel="stylesheet" href="/icofont/icofont.min.css">
        <link rel="stylesheet" href="/default.css">
        <link rel="stylesheet" href="/picnic.css">
        <link rel="shortcut icon" href="/icon.png">
    <body style="background:#fff">
        <a id="pgUp"></a>
        <!-- 상단바 #6149ad -->
        <header>
            <nav class="nav" style="position:static;background:#2363ad">
                <a href="https://dev.fnbase.xyz" class="brand">
                    <span style="color:#fff">FNBase</span>
                </a>
                <a class="brand-r">
                    <span style="color:#fff"><i class="icofont-gears"></i><h-m> 설치 페이지</h-m><h-d> 설치중..</h-d></span>
                </a>
            </nav>
            <span style="color:white">
                <div id="topNoticeLine" style="background:#5599e6">
                    <a href="/misc>adv" class="label">알림</a> <a onclick="if(confirm('Github에서 도움말을 보시겠습니까?')){location.href = 'https://github.com/FNBase/FNBase-Engine/wiki'}" style="color:white">설치 가이드 보기</a>
                </div>
            </span>
        </header>

        <article>
          <form method="POST" action="/install/complete.php">
            <h2>간단 설치 페이지</h2>
            <p>아래 양식을 모두 채우면 설치 과정이 완료됩니다.</p>
            <br><br>
            <h3>기본 설정</h3>
            <label><input name="siteTitle" placeholder="사이트 제목" type="text" maxlength="50"></label>
            <label><input name="sitePath" placeholder="사이트 주소" type="text" maxlength="100"></label>
            <label><input name="siteDesc" placeholder="사이트 설명" type="text" maxlength="100"></label>
            <label><input name="siteEmMail" placeholder="관리자 메일" type="email" maxlength="100"></label>
            <label>기본 언어<select name="siteLang">
                <option value="ko-KR">한국어 (Korean)</option>
            </select></label>
            <label>시간대 지정<select name="siteTimezone">
                <option value="UTC">GMT (UTC)</option>
                <option value="Asia/Seoul" selected>Asia/Seoul (UTC+9)</option>
            </select></label>
            <br><br>
            <h3>표시 설정</h3>
            <label><input name="pageColor" placeholder="페이지 메인 색상" type="text" maxlength="7"></label>
            <label><input name="pageSubColor" placeholder="페이지 보조 색상" type="text" maxlength="7"></label>
            <label><input name="pageBgColor" placeholder="페이지 배경 색상" type="text" maxlength="7"></label>
            <label><textarea name="pageHead" placeholder="사이트 상단 코드"></textarea></label>
            <label><textarea name="pageFooter" placeholder="사이트 바닥글"></textarea></label>
            <label>게시판 스킨<select name="siteSkin">
                <option value="Primary">기본 (Primary)</option>
            </select></label>
            <br><br>
            <h3>시작 준비</h3>
            <label><input name="id" placeholder="관리자 아이디" type="text" maxlength="30"></label>
            <label><input name="name" placeholder="관리자 닉네임" type="text" maxlength="30"></label>
            <label><input name="password" placeholder="관리자 비밀번호" type="password" maxlength="50"></label>
            <label>위키를 사용하시겠습니까?<select name="wiki">
                <option value="yes">네</option>
            </select></label>
            <br><br>
            <h3>마지막 단계</h3>
            <label><input name="db" placeholder="데이터베이스 주소" type="text" maxlength="50"></label>
            <label><input name="dbid" placeholder="데이터베이스 유저" type="text" maxlength="100"></label>
            <label><input name="dbpw" placeholder="데이터베이스 비밀번호" type="password" maxlength="100"></label>
            <label><input name="dbname" placeholder="데이터베이스 이름" type="text" maxlength="100"></label>
            <br><br>
            <label><a href="https://github.com/FNBase/FNBase-Engine/blob/master/LICENSE">사용 규약(라이선스)</a>에 동의하십니까?<select name="agree">
                <option value="yes" selected>네</option>
                <option value="no">아니오</option>
            </select></label>
            <input type="submit" style="background: green;width:100%" value="이제 모든 준비가 끝났습니다.">
          </form>
        </article>
        
        <footer class="flex">
            <div>
                <p class="right">FNBase Engine</p>
            </div>
            <div>
                <p class="left muted">
                    <a href="https://github.com/FNBase/FNBase-Engine" target="_blank"><i class="icofont-maximize"></i></a>
                </p>
            </div>
            <br>
        </footer>
  </body>
</html>