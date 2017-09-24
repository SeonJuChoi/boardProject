<?php
session_start();
include 'db.php';

// <-- db Connect
$dbConn         = mysqli_connect(SERVERADDR, USERID, USERPW);

// <-- db Select
mysqli_select_db($dbConn,USEDDB);

// <-- 현재 페이지, 삭제할 게시글 아이디 가져오기
$articleId      = $_GET['articleId']; // 삭제할 게시글 id
$currentPage    = $_GET['page']; // 현재 페이지

// <-- 삭제 후 돌아갈 게시글 리스트 링크 설정
$listLink = 'articleRead.php?page=' . $currentPage . '&articleId=' . $articleId;

// <-- 답글일 경우
if(isset($_GET['aid'])){
    $aid = $_GET['aid'];
    $query = 'delete from board where board_id='.$aid;
}
// <-- 댓글일 경우
else if(isset($_GET['pid'])) {
    $pid        = $_GET['pid'];
    $query      = 'delete from board where board_id='.$pid;
}
// <-- 댓글이 아닐경우
else {
    // <-- 댓글이 있는지 확인 후 pid를 이용해서 덧글 삭제
    $query      = "select * from board where board_pid=".$articleId;
    $pResult    = mysqli_query($dbConn, $query);
    $pRow       = mysqli_num_rows($pResult);
    $listLink   = "articleList.php?";

    // <-- 해당 게시글의 댓글 전부 삭제
    if($pRow > 0) {
        $query  = "delete from board where board_pid=".$articleId;
        mysqli_query($dbConn, $query);
    }

    // <-- 삭제 쿼리 설정
    $query      = "delete from board where board_id=".$articleId;
}


// <-- 삭제 쿼리 실행
$deleteResult   = mysqli_query($dbConn, $query);

// <-- 삭제 되었을 경우 원래 리스트로 링크 이동
if($deleteResult) {
    echo "<script>alert('삭제되었습니다.')</script>";
    echo "<script>document.location.href = '$listLink';</script>";
}

// <-- db연결 종료
mysqli_close($dbConn);

?>

