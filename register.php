<main>
    <div class="flex">
        <!-- 상단 보조메뉴 -->
        <section class="hidMob">
        </section>
        <section id="mainSec" class="half">
        <?php require_once 'alert.php'; ?>
        <?php if(!empty($_SESSION['fnUserId']) or $_SESSION['fnUserId'] == '0'){ die('<script>alert("이미 로그인 되어있습니다!");history.back()</script>'); } ?>
            <div class="tabs two" style="text-align: center;">
                    <input id="tabC-1" type="radio" name="tabgroupC" checked />
                    <label class="pseudo button toggle" for="tabC-1">1</label> /
                    <input id="tabC-2" type="radio" name="tabgroupC" />
                    <label class="pseudo button toggle" for="tabC-2">2</label>
                <div class="row">
                    <div class="card">
                        <header>
                            <h3>회원가입</h3>
                        </header>
                        <section>
                            모든 질문에 대답해주시길 바랍니다.<br>
                            걱정 마세요. 3분도 안 걸립니다!<br>
                            <br>회원가입시 <a href="/b>fnbase>9" target="_blank"><a href="/terms.html" data-tooltip="새 창에서 보기"><strong>이용약관</strong></a> 에 동의하신 것으로 간주됩니다.
                        </section>
                        <footer>
                            <label for="tabC-2" class="button success">동의합니다!</label>
                            <span class="right hidMob">
                                <label for="loginModalp" class="button">계정이 이미 있습니다.</label>
                            </span>
                        </footer>
                    </div>
                    <form method="post" action="/php/mail.php">
                    <div class="card">
                        <header>
                            <h3>이메일 인증</h3>
                        </header>
                        <section>
                            절차 진행을 위해선 이메일 인증이 필요합니다.<br>
                            <span class="subInfo">무분별한 계정 생성 및 악용을 방지하기 위한 절차이오니, 협조 부탁드립니다.</span><br>
                        </section>
                        <section>
                            <b>자동화된 사이트 공격을 막기 위해, 국내 유력 메일 주소만 가능합니다.</b><br>
                            <span class="subInfo">네이버 메일, 한메일(다음 메일, 카카오 메일 포함)만 가능. <b>구글 메일 안 됨!</b></span><br><br>
                            <span class="subInfo">인증 이메일은 30분간 유효합니다.</span><br>
                            <span class="subInfo">이메일이 도착하지 않았다면, 스팸 메일함을 확인해보세요.</span>
                            <input type="email" name="mail" placeholder="ex) hgd96@naver.com" aria-label="Email" required>
                        </section>
                        <footer>
                            <button class="success" type="submit">이메일 보내기</button>
                            <label for="tabC-1" class="button dangerous">뒤로가기</label>
                        </footer>
                    </div>
                    </form>
                </div>
            </div>
        </section>
        <aside class="hidMob" id="nofiSec">
        </aside>
    </div>
</main>