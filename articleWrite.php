<?php
session_start();

include "db.php";

// <-- db에 연결.
$dbConn = mysqli_connect(SERVERADDR, USERID, USERPW);
// <-- db 선택.
mysqli_select_db($dbConn, USEDDB);

// <-- 작성일 설정
$date               = date("Y-m-d H:i:s");

// <-- 글쓴이 id, 별명 가져오기.
$userId             = $_SESSION['user_id'];
$userAlias          = $_SESSION['user_alias'];

// <-- 답글일 경우
if(isset($_POST['commentId'])){
    $articleId      = $_POST['articleId']; // 현재 게시글 번호
    $currentPage    = $_POST['currentPage']; // 현재 원 게시글의 페이지 번호
    $commentId      = $_POST['commentId']; // 원 댓글의 게시글 번호
    $acContents     = $_POST['afterComment']; // 답글의 내용
    $title          = "afterComment"; // 답글이므로 afterComment 처리

    // <-- 답글 작성 후 다시 해당 답글의 원문 게시글을 출력하기 위한 링크 설정
    $articleLink    = 'articleRead.php?page='.$currentPage.'&articleId='.$articleId;

    // <-- 답글 내용 변환
    $acContents     = htmlspecialchars($acContents, ENT_QUOTES);
    $acContents     = str_replace(" ", "&nbsp;",$acContents);

    // <-- 쿼리 설정
    $query = "insert into board (board_pid, title, user_id, user_alias, contents, reg_date, board_cid) values (".
        $articleId.",'".$title."',"."'".$userId."',"."'".$userAlias."',"."'".$acContents."',".
        "'".$date."', ".$commentId.")";


}
// <-- 덧글일 경우
else if (isset($_POST['pArea'])) {
    $pContents      = $_POST['pArea']; // 덧글 내용
    $articleId      = $_POST['articleId']; // 덧글의 원 게시글 번호
    $title          = 'comment'; // 덧글이므로 comment 처리
    $currentPage    = $_POST['currentPage']; // 현재 원 게시글의 페이지 번호

    // <-- 덧글 작성 후 다시 해당 덧글의 원문 게시글을 출력하기 위한 링크 설정
    $articleLink    = 'articleRead.php?page='.$currentPage.'&articleId='.$articleId;

    // <-- 덧글 내용 변환
    $pContents      = htmlspecialchars($pContents, ENT_QUOTES);
    $pContents      = str_replace(" ", "&nbsp;",$pContents);

    $query = "insert into board (board_pid, title, user_id, user_alias, contents, reg_date) values (".
        $articleId.",'".$title."',"."'".$userId."',"."'".$userAlias."',"."'".$pContents."',"."'".$date."')";
}

else {
    // <-- 글쓰기 정보를 받아오기
    $title = $_POST['title'];
    $contents = $_POST['contents'];

    // <-- 제목, 글 내용 변환
    $title = htmlspecialchars($title, ENT_QUOTES);
    $content = htmlspecialchars($contents, ENT_QUOTES);
    $content = str_replace(" ", "&nbsp;",$content);

    // <-- db query 등록 설정
    $query = "insert into board (title, contents, user_id, user_alias, reg_date) values (".
        "'".$title."',"."'".$content."',"."'".$userId."',"."'".$userAlias."',"."'".$date."')";
}

// <-- 쿼리 실행
$result = mysqli_query($dbConn, $query);

echo $query;

// <-- 쿼리 실행 결과에 따른 등록 알림창
if($result) {
    echo "<script>alert('등록되었습니다.')</script>";
    if($title == 'comment' || $title == 'afterComment')
        echo "<script>location.href='$articleLink'</script>";
    else
        echo "<script>location.href='articleList.php?';</script>";
}

// <-- db 연결 종료
mysqli_close($dbConn);

?>


