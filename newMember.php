<?php
include 'db.php';

// <-- db Connect
$dbConn = mysqli_connect(SERVERADDR, USERID, USERPW);

// <-- db Select
mysqli_select_db($dbConn,USEDDB);

// <-- 입력한 회원정보 가져오기
$user_id        = $_POST['userId'];
$user_pw        = $_POST['userPw'];
$user_alias     = $_POST['userAlias'];

// <-- 쿼리 설정
$query          = "insert into user (user_id, user_pw, user_alias) values (".
                    "'". $user_id."', "."'".$user_pw."', "."'".$user_alias."')";

// <-- 쿼리 실행
$result         = mysqli_query($dbConn, $query);

if($result) {
    echo "<script>alert('회원가입이 완료되었습니다!');</script>";
    echo "<script>location.href='articleList.php?';</script>";
}

// <-- db 연결 종료
mysqli_close($dbConn);
?>