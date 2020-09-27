<form method="post" action="/registered.php">
<main>
    <div class="flex">
        <!-- 상단 보조메뉴 -->
        <section class="hidMob">
        </section>
        <section id="mainSec" class="half">
        <?php require_once 'alert.php'; ?>
            <?php
                $code = filt($_GET['code'], 'abc');
                $mail = filt($_GET['mail'], 'mail');
                if(preg_match('/(kakao\.com|daum\.net|naver\.com|hanmail\.net|fnbase\.xyz)$/m', $mail) == FALSE){
                    die('일회용 메일은 사용하실 수 없습니다.');
                }
                $sql = "SELECT * FROM `_auth` where `type` = 'mail' and `key` = '$mail' and `end` > NOW()";
                $result = mysqli_query($conn, $sql);

                if(mysqli_num_rows($result) == 1){
                    $sql = "SELECT * FROM `_auth` where `type` = 'mail' and `key` = '$mail' and `value` = '$code' and `end` > NOW()";
                    $result = mysqli_query($conn, $sql);
                        if(mysqli_num_rows($result) == 1){
                            $r2Succ = TRUE;
                        }else{
                            echo '값이 일치하지 않습니다.<br>';
                            echo '메일을 다시 확인해주시고, 계속 진행되지 않을 경우 관리자에게 문의하십시오.<br>';
                            echo '<a href="/main" class="button">메인 페이지로</a>';
                        }
                }else{
                    if(mysqli_num_rows($result) > 1){
                        echo '여러개의 인증 절차를 동시에 진행하실 수 없습니다!<br>';
                        echo '1개 키를 제외한 모든 키가 만료될 때 까지 기다려주십시오.<br>';
                        echo '<a href="/main" class="button">메인 페이지로</a>';
                    }else{
                        echo '인증 키가 만료되었거나 사용할 수 없습니다!<br>';
                        echo '회원가입 절차를 처음부터 다시 진행하여주세요.<br>';
                        echo '<a href="/register" class="button">회원가입 페이지로</a>';
                    }
                }

                if($r2Succ){
            ?>
                <div class="tabs three" style="text-align: center;">
                    <input id="tabC-1" type="radio" name="tabgroupC" checked />
                    <label class="pseudo button toggle" for="tabC-1">1</label> /
                    <input id="tabC-2" type="radio" name="tabgroupC" />
                    <label class="pseudo button toggle" for="tabC-2">2</label> /
                    <input id="tabC-3" type="radio" name="tabgroupC" />
                    <label class="pseudo button toggle" for="tabC-3">3</label>
                <div class="row">
                    <div class="card">
                        <header>
                            <h3>정보 입력</h3>
                        </header>
                        <section>
                            <red>*</red> 표시는 반드시 입력해주세요.<br><br>
                            <div class="flex two">
                                <div>
                                    <label>아이디<red>*</red> <input maxlength="20" type="text" id="userid" class="check" placeholder="영문 소문자 또는 숫자" name="userid" required>
                                    <span class="subInfo">영문 소문자 또는 숫자, 최대 20자리 / </span><span class="subInfo" id="id_check">중복 확인 대기중..</span></label>
                                    <p>주의! 개인을 특정할 수 있는 정보를 입력하면 위험합니다.</p>
                                </div>
                                <div>
                                    <label>닉네임<red>*</red> <input maxlength="20" type="name" id="userck" class="check" placeholder="한글/영문/숫자/_" name="nickname" required>
                                    <span class="subInfo">영문/국문/숫자/_, 최대 20자리 / </span><span class="subInfo" id="name_check">중복 확인 대기중..</span></label>
                                </div>
                            </div>
                            <div class="flex two">
                                <div>
                                    <label>비밀번호<red>*</red> <input maxlength="50" type="password" placeholder="영문/숫자/특문" name="password" required></label>
                                    <span class="subInfo">영문/숫자/특수문자, 최소 6자리 권장. 최대 50자.</span>
                                </div>
                                <div>
                                    <label>이메일<red>*</red> <input name="mail" value="<?=$mail?>" readonly required></label>
                                </div>
                            </div>
                            <div class="flex one">
                                <div>
                                    <textarea placeholder="나를 소개해보세요." name="intro"></textarea>
                                </div>
                            </div>
                        </section>
                        <footer>
                            <label for="tabC-2" class="button">설문조사 단계로</label>
                            <button style="background-color: #6633FF;" class="right" type="submit">됐어요, 이만 끝낼래요.</button>
                        </footer>
                    </div>
                    <div class="card">
                        <header>
                            <h3>몇가지 질문</h3>
                        </header>
                        <section>
                            <br>
                            <label>어떤 경로로 오셨나요?
                                <select name="from" required>
                                    <option value="search">검색을 통해서</option>
                                    <option value="friends">지인 등의 소개를 받아서</option>
                                    <option value="move">다른 커뮤니티에서 이주하기 위해서</option>
                                    <option value="source">다른 커뮤니티로 공유된 게시글의 출처를 통해서</option>
                                    <option value="other">기타</option>
                                </select>
                            </label>
                            <br>
                            <br>
                        </section>
                        <section>
                            혹시, 회원가입 하기 전 부터 활동하고 계셨었나요?<br>
                            <label><input type="radio" name="before" value="yes" aria-hidden="true"><span class="checkable">네, 회원가입 없이 활동했습니다.</span></label><br>
                            <label><input type="radio" name="before" value="sometimes" aria-hidden="true"><span class="checkable">게시글만 가끔 봤습니다.</span></label><br>
                            <label><input type="radio" name="before" value="no" aria-hidden="true"><span class="checkable">아니오. 처음입니다.</span></label>
                            <label><input type="radio" name="before" value="none" aria-hidden="true" checked><span class="checkable muted">-선택해주세요-</span></label>
                            <br>
                            <br>
                        </section>
                        <section>
                            어떻게 활동하실 예정인가요?<br>
                            <label><input type="radio" name="after" value="yes" aria-hidden="true"><span class="checkable">글도 쓰고, 대화도 나눠보죠.</span></label><br>
                            <label><input type="radio" name="after" value="sometimes" aria-hidden="true"><span class="checkable">게시글만 가끔 보겠습니다.</span></label><br>
                            <label><input type="radio" name="after" value="no" aria-hidden="true"><span class="checkable">가입만 해놓겠습니다.</span></label>
                            <label><input type="radio" name="after" value="none" aria-hidden="true" checked><span class="checkable muted">-선택해주세요-</span></label>
                        </section>
                        <footer>
                            <label for="tabC-3" class="button">다음 단계로</label><label for="tabC-1" class="button dangerous">뒤로가기</label>
                        </footer>
                    </div>
                    <div class="card">
                        <header>
                            <h3>가입 절차 끝!</h3>
                        </header>
                        <section>
                            <br>
                            가입을 환영합니다!
                            <br>
                            <span class="subInfo">이후 '계정 활용' 페이지에서 개인 설정을 변경하실 수 있습니다.</span><br>
                            <span class="subInfo">즐거운 시간 되시길 바랍니다.</span>
                            <br>
                            반드시 아래 버튼을 눌러 완료하여주세요.
                        </section>
                        <footer>
                            <button  class="success full" type="submit">완료하기</button>
                        </footer>
                    </div>
                    
                </div>
            </div>
            <?php
                }
            ?>
        </section>
        <aside class="hidMob" id="nofiSec">
        </aside>
    </div>
</main>
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script>
$(document).ready(function(e) { 
	$(".check").on("keyup", function(){ //check라는 클래스에 입력을 감지
		var self = $(this); 
		var userid;
        var vard;

        if(this.id == 'userid'){
            vard = 'id';
        }else{
            vard = 'name';
        }
		
		userid = self.val();
        userid = strtolower(userid);
		
		$.post( //post방식으로 id_check.php에 입력한 userid값을 넘깁니다
			"php/check.php",
			{ 'result' : userid, 'type' : vard }, 
			function(data){ 
				if(data){ //만약 data값이 전송되면
					$("#"+vard+"_check").html(data);
				}
			}
		);
	});
});
</script>