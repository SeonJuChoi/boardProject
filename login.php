<?php
session_start();
include 'db.php';

// <-- db Connect
$dbConn         = mysqli_connect(SERVERADDR, USERID, USERPW);

// <-- db Select
mysqli_select_db($dbConn,USEDDB);

// <-- 입력한 아이디, 비밀번호 받아오기
$user_id        = $_POST['id'];
$user_pw        = $_POST['passwd'];

// <-- 현재 아이디의 정보를 가져 오기.
$query          = "select user_id, user_pw, user_alias  from user where user_id='".$user_id."'";

$loginResult    = mysqli_query($dbConn, $query);

$loginData      = mysqli_fetch_row($loginResult);

$dbUserId       = $loginData[0];
$dbUserPw       = $loginData[1];

// <-- id & pw 확인
if($dbUserId == null || $dbUserPw != $user_pw)
    echo "loginFailed";
// <-- 로그인 완료 되면 세션 생성
else {
    $user_alias = $loginData[2];

    if (!isset($_SESSION))
        session_start();

    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_pw'] = $user_pw;
    $_SESSION['user_alias'] = $user_alias;

}

// <-- db 연결 종료
mysqli_close($dbConn);
?>