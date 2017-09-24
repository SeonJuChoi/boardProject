<?php
include 'db.php';

// <-- 입력받은 유저 아이디 가져오기
$user_id        =  $_POST['userId'];

// <-- db Connect
$dbConn         = mysqli_connect(SERVERADDR, USERID, USERPW);

// <-- db Select
mysqli_select_db($dbConn,USEDDB);

// <-- 쿼리 설정
$query = "select * from user where user_id='".$user_id."'";

// <-- 쿼리 실행
$queryResult    = mysqli_query($dbConn, $query);
$result         = mysqli_num_rows($queryResult);

// <-- 아이디가 있을 경우 false 없을 경우 true 여부를 반환
if($result == 0)
    echo "true";
else
    echo "false";

// <-- db연결 종료
mysqli_close($dbConn);

?>