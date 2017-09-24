<?php
session_start();
include 'db.php';

// <-- db Connect
$dbConn         = mysqli_connect(SERVERADDR, USERID, USERPW);

// <-- db Select
mysqli_select_db($dbConn,USEDDB);

// <-- 글 변경 정보와 게시글id, 페이지 정보를 가져오기
$title          = $_POST['title'];
$contents       = $_POST['contents'];
$articleId      = $_POST['articleId'];
$currentPage    = $_POST['currentPage'];

// <-- 수정 후 돌아 갈 페이지 링크 설정 (수정 후는 조회수 증가 X)
$articleLink    = 'articleRead.php?page='.$currentPage.'&articleId='.$articleId.'&edit=true';

// <-- 글 제목, 내용 변환
$title          = htmlspecialchars($title, ENT_QUOTES);
$contents       = htmlspecialchars($contents, ENT_QUOTES);
$contents       = str_replace(" ", "&nbsp;",$contents);

// <-- 쿼리 설정
$query          = "update board set title="."'".$title."',"."contents="."'".$contents."' where board_id=".$articleId;

// <-- 쿼리 실행
$result         = mysqli_query($dbConn, $query);

echo $query;

if($result) {
    echo "<script>alert('수정되었습니다.')</script>";
    echo "<script>location.href='$articleLink'</script>";
}

// <-- db연결 종료
mysqli_close($dbConn);

?>