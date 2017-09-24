<?php
session_start();
include 'db.php';

// <-- db Connect
$dbConn = mysqli_connect(SERVERADDR, USERID, USERPW);

// <-- db Select
mysqli_select_db($dbConn,USEDDB);

// <-- 현재 페이지와 게시글 번호를 이용해 목록보기 링크 설정
$currentPage    = @$_GET['page']; // 현재 페이지
$articleId      = @$_GET['articleId']; // 게시글 번호
$searchMode     = false; // 검색 했는지 안했는지 플래그
$option         = ""; // 검색 옵션
$keyword        = ""; // 검색 키워드

// <-- 게시글 삭제 링크 설정
$deleteLink     = "articleDelete.php?page=".$currentPage."&articleId=".$articleId;

if(isset($_GET['searchOp']) && isset($_GET['searchKeyword'])) {
    $option     = @$_GET['searchOp'];
    $keyword    = @$_GET['searchKeyword'];
    $searchMode = true;
}

if($searchMode == true)
    $listLink   = 'articleList.php?searchOp='.$option.'&searchKeyword='.
                   $keyword.'&currentPageNum='.$currentPage;
else
    $listLink   = 'articleList.php?currentPageNum='.$currentPage; // 목록보기 링크

// <-- 수정 후는 조회수가 증가 X 글 읽기 모드인 경우에만 조회수가 증가.
if(!isset($_GET['edit'])) {
    // <-- 조회수 조회
   $query       = "select hit from board where board_id=" . $articleId;
   $result      = mysqli_query($dbConn, $query);
   $hitResult   = mysqli_fetch_array($result);
   // <-- 조회수 증가
   $hit         = $hitResult[0];
   $hit++;

    // <-- 조회수 업데이트
    $query = "update board set hit=".$hit." where board_id=".$articleId;
    mysqli_query($dbConn, $query);

}

// <-- 게시물 조회 쿼리 실행
$query          = "select * from board where board_id=".$articleId;

$selectResult   = mysqli_query($dbConn, $query);

$resultData     = mysqli_fetch_row($selectResult);

// <-- 덧글 조회 쿼리 실행

$query          = "select user_alias, contents, reg_date, board_pid ,board_id, user_id from board where board_pid=".$articleId.
                  " and board_cid=0"." order by reg_date asc";


$pResult        = mysqli_query($dbConn, $query);

$pRowCount      = mysqli_num_rows($pResult);

/*
// <-- db연결 종료
mysqli_close($dbConn);*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<link rel="stylesheet" href="css/bootstrap.css">
<style>
    .commentWriter {
        width: 150px;
        text-align: center;
        text-indent: 30px;
    }

    .commentButton {
        width: 200px;
    }

    #listButton, #rewriteButton, #deleteButton {
        margin-right: 5px;
    }

    #articleTable {
        margin-bottom: 0px;
    }

    #articleTable {
        border-bottom: 2px solid lightgray;
    }

    #comment, #afterComment {
        resize: none;
    }

    #writerBox {
        border: none;
    }

    #textBox {
        resize: none;
    }

    #titleBox, #writerBox, #textBox {
        width: 800px;
    }

    #articleTitle {
        padding-top: 5px;
        vertical-align: top;
    }

    #commentTable td {
        border-bottom: 1px solid lightgray;
    }

    #date {
        font-size: x-small;
    }

    #date a {
        color:black;
        text-decoration : none;
    }

    #date a:link {
        color: black;
        text-decoration: none;
    }

    #date a:visited {
        color: black;
        text-decoration: none;
    }

    #date a:hover {
        color: black;
        text-decoration: none;
    }

    #menuBox {
        margin-top: 10px;
    }

    a {
        color:gray;
        text-decoration : none;
    }

    a:link {
        color: gray;
        text-decoration: none;
    }

    a:visited {
        color: gray;
        text-decoration: none;
    }

    a:hover {
        color: gray;
        text-decoration: none;
    }

    .afterCommentArea {
        text-indent: 30px;
        color: #51bfde;
    }

</style>
<script language="JavaScript" src="js/jquery-3.2.1.js"></script>
<script language="JavaScript" src="js/bootstrap.js"></script>
<script>
    var commentFlag = false; // 답글 플래그
    var row         = 0; // 테이블 행 계산

    // <-- 삭제 확인
    function deleteCheck(link) {
        var delCheck = confirm('게시글을 삭제하시겠습니까?');

        if (delCheck == true)
            location.href = link;
    }

    // <-- 답글 영역 만들기
    function createCommentBox(cid, id, aWriter) {
        // <-- 로그인 안 한 경우 메시지 출력
        if(aWriter == "")
            alert("로그인 한 경우에만 답글 작성이 가능합니다.");
        else {
            // <-- 답글은 한번에 한개씩 가능
            if (commentFlag == false) {

                var commentWriterArea = document.createElement('td'); // 답글 작성자 영역
                var commentContentArea = document.createElement('td'); // 답글 내용 영역
                var commentButtonArea = document.createElement('td'); // 답글 버튼 영역

                var table = document.getElementById('commentTable'); // 덧글 테이블
                var commentContentBox = document.createElement('textarea'); // 답글 내용
                var commentButton = document.createElement('input'); // 답글 달기 버튼
                var cancelButton = document.createElement('input'); // 답글 취소 버튼
                var commentId = document.createElement('input'); // 원 덧글의 게시글 번호

                // 테이블 행 추가 영역 설정
                row = parseInt(id) + 3;
                // 새 행 추가
                var newRow = table.insertRow(row);

                // <-- 영역 스타일 설정
                commentWriterArea.className = 'commentWriter';

                commentButtonArea.className = "commentButton";

                // <-- 요소 설정
                aWriter = "└" + aWriter;

                commentContentBox.className = 'form-control';
                commentContentBox.name = 'afterComment';
                commentContentBox.id = 'afterComment';

                commentButton.type = 'submit';
                commentButton.className = 'btn btn-info';
                commentButton.name = 'cBt';
                commentButton.onclick = commentFunc;
                commentButton.value = '답글 달기';

                cancelButton.type = 'button';
                cancelButton.className = 'btn btn-info';
                cancelButton.name = 'cancelBt';
                cancelButton.value = '취소';
                cancelButton.onclick = cancelComment;

                commentId.type = 'hidden';
                commentId.value = cid;
                commentId.name = 'commentId';

                // <-- 각 영역에 각 요소 추가
                commentWriterArea.appendChild(document.createTextNode(aWriter));
                commentWriterArea.appendChild(commentId);
                commentContentArea.appendChild(commentContentBox);
                commentButtonArea.appendChild(commentButton);
                commentButtonArea.appendChild(cancelButton);

                // <-- 영역을 행에 추가
                newRow.appendChild(commentWriterArea);
                newRow.appendChild(commentContentArea);
                newRow.appendChild(commentButtonArea);

                // <-- 답글 플래그 true로 변경
                commentFlag = true;

            }
            // <-- 답글 창이 이미 띄워져 있을 경우
            else {
                alert('댓글에 대한 답글은 하나씩만 가능합니다')
            }
        }
    }

    // <-- 답글 취소
    function cancelComment() {
        var commentTable = document.getElementById('commentTable');

        commentTable.deleteRow(row);

        commentFlag = false;
    }

    // <-- 댓글, 답글 작성
    function commentFunc() {
       var commentForm =  document.getElementById('commentForm');

       commentForm.submit();
    }
</script>
<body>
<div class="container">
<h2>게시글 보기</h2>
<!-- 게시글 테이블 -->
<table class="table" id="articleTable">
<?
// <-- 게시물 출력

// <-- 제목
echo "<tr>";
echo "<thead>";
echo "<th colspan='5' class='active' id='title'>제목 : ".$resultData[2]."</th>";
echo "</thead>";
echo "</tr>";

echo "<tr>";
// <-- 글 번호
echo "<td>글번호 : ".$resultData[0]."</td>";
// <-- 작성자
echo "<td>작성자 : ".$resultData[5]."</td>";
// <-- 작성일
echo "<td>작성일 : ".$resultData[7]."</td>";
// <-- 조회수
echo "<td>조회수 : ".$resultData[6]."</td>";
// <-- 덧글수
echo "<td>덧글수 : ".$pRowCount."</td>";
echo "</tr>";
// <-- 게시글 내용
echo "<tr>";
$contents = nl2br($resultData[3]);
echo "<td colspan='5'>$contents</td>";
echo "</tr>";


?>
</table>
<div id="menuBox">
    <?

    // <-- 목록 버튼 생성
    echo "<input type='button' id='listButton' class='btn btn-info' value='목록' onclick=location.href='$listLink'>";

    // <-- 자기가 쓴 글이 아닐 경우 수정, 삭제 X
    if(isset($_SESSION['user_id'])){
        if($_SESSION['user_id'] == $resultData[4]){
            // <-- 수정 버튼 생성
            echo "<input type='button' id='rewriteButton' class='btn btn-default' value='수정' data-toggle='modal' data-target='#editTemplate'>";
            // <-- 삭제 버튼 생성
            echo "<input type='button' id='deleteButton' class='btn btn-default' value='삭제' onclick=deleteCheck('$deleteLink')>";
        }
    }
    ?>
</div>
<!-- 댓글 테이블 -->
<form action='articleWrite.php' method='post' id='commentForm'>
<table id="commentTable" class="table">

        <?
        echo "<thead>";
        echo "<tr>";
        echo "<th colspan='5'>덧글</th>";
        echo "</tr>";
        echo "</thead>";


        // <-- 댓글 입력 칸
        echo "<tr class='active'>";

        if(isset($_SESSION['user_alias'])) {
            echo "<td class='commentWriter'>".@$_SESSION['user_alias']."</td>";
            echo "<td><textarea name='pArea' class='form-control' id='comment'></textarea></td>";
            echo "<td class='commentButton'><input type='button' id='pBt' class='btn btn-info' name='pBt' value='덧글 달기' onclick='commentFunc()'></td>";
        }
        // <-- 로그인 안했을시 댓글 X
        else {
            echo "<td class='commentWriter'>"."guest"."</td>";
            echo "<td><textarea name='pArea' class='form-control' id='comment' readonly>로그인 후 작성하실 수 있습니다.</textarea></td>";
            echo "<td class='commentButton'></td>";
        }
        echo "<input type='hidden' name='articleId' value='$articleId'>";
        echo "<input type='hidden' name='currentPage' value='$currentPage'>";

        echo "</tr>";

        // <-- 댓글 출력

        $commentNum = 0;

        if ($pRowCount != 0) {
            for($pRow = 0 ; $pRow < $pRowCount ; $pRow++) {
                $pData          = mysqli_fetch_row($pResult);
                $pContents      = nl2br($pData[1]);
                $deletePLink    = "articleDelete.php?page=".$currentPage."&articleId=".$pData[3]."&pid=".$pData[4];
                echo "<tr id=$pRow>";
                echo "<td align='center'>".$pData[0]."</td>";
                echo "<td colspan='2'>".$pContents;

                $pId = $pData[4];

                // 자기가 쓴 글이 아니면 댓글 삭제 X
                if(isset($_SESSION['user_id'])) {
                    if ($_SESSION['user_id'] == $pData[5]) {
                        echo "<a href='$deletePLink' id='pArea'>&nbsp;&nbsp;X </a>";
                    }
                }
                echo "<div id='date'>";
                echo "작성일 : ".$pData[2];
                $pWriter = @$_SESSION['user_alias'];
                echo "<a onclick=createCommentBox('$pId','$commentNum','$pWriter')> | 답글</a>";
                $commentNum++;
                echo "</div>";
                echo "</td>";
                echo "</tr>";

                // <-- 답글 조회 쿼리
                $query = "select user_alias, contents, reg_date, board_pid ,board_id, board_cid, user_id from board where board_cid=".$pId.
                    " order by reg_date asc";

                $afterResult = mysqli_query($dbConn, $query);

                $afterNum = mysqli_num_rows($afterResult);

                // <-- 답글 출력
                if($afterNum != 0) {

                    for ($afterCount    = 0 ; $afterCount < $afterNum ; $afterCount++) {
                        $aData          = mysqli_fetch_row($afterResult);
                        $aContents      = nl2br($aData[1]);
                        $deleteALink    = "articleDelete.php?page=".$currentPage."&articleId=".$pData[3]
                                            ."&pid=".$pData[4]."&aid=".$aData[4];

                        echo "<tr id=$commentNum>";
                        echo "<td align='center' class='afterCommentArea'>"."└ ".$aData[0]."</td>";
                        echo "<td colspan='2'>".$aContents;

                        $aId            = $aData[4]; // 현재 답글의 id
                        $pid            = $aData[5]; // 원 댓글의 id


                        // 자기가 쓴 글이 아니면 댓글 삭제 X
                        if(isset($_SESSION['user_id'])) {
                            if ($_SESSION['user_id'] == $aData[6]) {
                                echo "<a href='$deleteALink' id='acArea'>&nbsp;&nbsp;X </a>";
                            }
                        }
                        echo "<div id='date'>";
                        echo "작성일 : ".$pData[2];
                        $aWriter = @$_SESSION['user_alias'];
                        echo "<a onclick=createCommentBox('$pId','$commentNum','$aWriter')> | 답글</a>";
                        $commentNum++;
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";

                    }
                }

            }
            // <-- db연결 종료
            mysqli_close($dbConn);

        }
        ?>
</table>
</form>
</div>
<!-- 수정 모달 창 -->
<div class="modal fade" id="editTemplate" role="dialog">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">글 수정하기</h2>
        </div>
        <div class="modal-body">
            <form action="editArticle.php" method="post" id="editForm">
            <table id="editTable">
                <?
                echo "<tr>";
                echo "<td>제목 : </td>";
                echo "<td><input type='text' value=$resultData[2] class='form-control' name='title' id='titleBox'></td>";
                echo "<input type='hidden' name = 'articleId' value=$articleId>";
                echo "<input type='hidden' name = 'currentPage' value=$currentPage>";
                echo "</tr>";

                echo "<tr>";
                echo "<td>작성자 : &nbsp;</td>";

                $writer = $_SESSION['user_alias'];
                echo "<td><input type='text' name='userAlias' id='writerBox' value=$writer readonly ></td>";

                echo "</tr>";

                echo "<tr>";
                echo "<td id='articleTitle' >내용 : </td>";
                echo "<td><textarea name='contents' class='form-control' rows='30' id='textBox'>$resultData[3]</textarea></td>";
                echo "</tr>";

                ?>
            </table>
        </div>
        <div class="modal-footer">
            <input type="submit" id="edit" value="수정하기" class='btn btn-info' onclick="">
            <input type="button" id="cancel" value="취소" class='btn btn-default' data-dismiss="modal">
            </form>
        </div>
    </div>
</div>
</div>
</body>
</html>


