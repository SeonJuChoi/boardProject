<?php

session_start();

include 'db.php';

// <-- db Connect
$dbConn = mysqli_connect(SERVERADDR, USERID, USERPW);

// <-- db Select
mysqli_select_db($dbConn,USEDDB);

// <-- Page Setting (페이지네이션 설정)
// 현재 페이지 정보가 없을 경우 현재 페이지는 1페이지.
if (isset($_GET['currentPageNum']))
    $currentPage    = $_GET['currentPageNum'];
else
    $currentPage    = 1;

$numOfContents      = 5; // 보여지는 게시글 갯수

$startContentsNum   = ($currentPage - 1) * $numOfContents; // sql 게시글 갯수 설정

// <-- search 관련 설정
$searchMode         = false; // 검색모드 (기본 값 false)
$keyword            = ""; // 키워드
$option             = ""; // 검색 옵션

// <-- 검색 키워드 O -> 검색, 키워드 X -> 게시판 그냥 보기
if (isset($_GET['searchKeyword'])) {
    $keyword        = $_GET['searchKeyword']; // 검색 키워드
    $option         = $_GET['searchOp']; // 검색 옵션
    $searchMode     = true; // 검색모드로 변경
}

// <-- Query  설정 (검색모드, 검색모드 X) (덧글 X -> 일반 게시글만 조회
if ($searchMode == true) {

$query = "select * from board where board_pid = 0 and ";

// 검색 옵션에 따른 Query 설정
switch ($option) {
    case 'title': {
        $query     .= "title like '%" . $keyword . "%'";
        break;
    }
    case 'contents': {
        $query     .= "contents like '%" . $keyword . "%'";
        break;
    }
    case 'writer': {
        $query     .= "user_alias like '". $keyword . "'";
        break;
    }
    case 'titleContents' : {
        $query     .= "(title like '%" . $keyword . "%' and contents like '%" . $keyword . "%')";
        break;
    }
}
    // 검색 쿼리 실행
    $rowResult = mysqli_query($dbConn, $query);

    $row = mysqli_num_rows($rowResult);

    // 레코드가 없을 경우 (검색의 경우)
    if ($row == 0) {
        echo "<script>alert('검색결과가 없습니다!')</script>";
        echo "<script>location.href='articleList.php?';</script>";
    }

    // 전체 페이지 계산
    $allPage     = ceil($row / $numOfContents); // 전체 페이지 갯수

    // 쿼리 설정 (날짜로 정렬, 표시 게시글 설정)
    $query      .= " order by reg_date desc limit " . $startContentsNum . ", " . $numOfContents;

}
// <-- 검색이 아닐 경우
else {
    // 쿼리 설정
    $query       = "select * from board where board_pid = 0";

    // 쿼리 실행
    $rowResult   = mysqli_query($dbConn, $query);

    $row         = mysqli_num_rows($rowResult);

    // 전체 페이지 계산
    $allPage     = ceil($row / $numOfContents);

    $query       = "select * from board where board_pid = 0 order by reg_date desc limit " .
                    $startContentsNum . ", " . $numOfContents;

}

// <-- 쿼리 실행
$queryResult    = mysqli_query($dbConn, $query);

$currentRow     = mysqli_num_rows($queryResult);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link href="css/bootstrap.css" rel="stylesheet">
</head>
<style>
    #loginBar {
        background-color: skyblue;
        height: 50px;
        display:table;
    }

    #loginBox {
        text-align:center;
        vertical-align:middle;
        display:table-cell;
    }

    #idBox, #pwBox {
        width: 100px;
        height: 20px;
    }

    #boardTable tr, th, td{
       text-align: center;
    }

    #boardTable {
        table-layout: fixed;
        margin: 0 auto;
    }

    #searchOp {
        margin-left: 200px;
        width: 112px;
    }

    #searchBtn {
        margin-left: 5px;
    }

    #searchText {
        width: 250px;
    }

    #titleBox, #writerBox, #textBox {
        width: 800px;
    }

    #textBox {
        resize: none;
    }

    #pageArea {
        margin-bottom: -13px;
        margin-left: 5px;
        margin-right: 5px;
    }

    #articleTitle {
        padding-top: 5px;
        vertical-align: top;
    }

    #currentPage {
        background-color: #5bc0de;
        color: white;
    }

    #page {
        color:black;
    }

    a {
        color: black;
        text-decoration: none;
    }

    a:link {
        color: black;
        text-decoration: none;
    }

    a:visited {
        color: black;
        text-decoration: none;
    }

    a:hover {
        color: black;
        text-decoration: none;
    }

    #boardTable th {
        border-bottom: 2px solid lightgray;
    }

    #boardTable tr {
        border-bottom: 1px solid lightgray;
    }

    #writerBox {
        border: none;
    }

    #memberTable td input {
        margin-bottom: 5px;
        height: 30px;
    }

    #memberTable td {
        text-align: right;
    }

    #check {
        margin-left: 5px;
    }

</style>
<script src="js/jquery-3.2.1.js"></script>
<script src="js/bootstrap.js"></script>
<script language="JavaScript">
    var newMemberFlag = false; // 중복검사 여부

    // <-- 게시글 유효성 확인
    function articleCheck() {

        var titleObj    = document.getElementById("titleBox");
        var writer      = document.getElementById("writerBox");
        var formObj     = document.getElementById("writeForm");

        if(titleObj.value == "")
            alert("제목을 입력하세요!");
        else if(writer.value == "")
            alert("작성자 이름을 입력하세요!");
        else
            formObj.submit();

    }

    // <-- 회원등록 체크
    function newMemberCheck() {
        var userIdObj       = document.getElementById('userId').value;
        var userPwObj       = document.getElementById('userPw').value;
        var userPwCheckObj  = document.getElementById('userPwCheck').value;
        var userAliasObj    = document.getElementById('userAlias').value;
        var memberForm      = document.getElementById('memberForm');

        if (userIdObj == "" || userPwObj == "" || userPwCheckObj == "" || userAliasObj == "")
            alert('회원가입 정보를 입력하세요!');
        else if (userPwObj != userPwCheckObj)
            alert('입력하신 비밀번호가 서로 일치하지 않습니다.');
        else {
            if (newMemberFlag == true)
                memberForm.submit();
            else
                alert('아이디 중복 검사를 해주세요!');
        }
    }

    // <-- 로그인, 중복확인 요청을 보내기
    function httpRequest() {
        var loginArea       = document.getElementById("loginArea");
        var buttonValue     = document.getElementById(event.target.id).value;
        var param           = ""; // 전송할 파타미터
        var url             = ""; // url

        // <-- 버튼 값에 따라 전송될 파라미터 값 설정
        switch (buttonValue) {
            case '로그인' : {
                // 파라미터 값 설정정
                var id      = document.getElementById("idBox").value;
                var pw      = document.getElementById("pwBox").value;
                param       = "id=" + id + "&passwd=" + pw;
                url         = "login.php"; // 요청을 보낼 url
                break;
            }
            case '로그아웃' : {
                url         = "logout.php";
                break;
            }

            case '중복확인' : {
                url         = "newMemberCheck.php";
                var userId  = document.getElementById('userId').value;
                param       = "userId=" + userId;
                break;
            }
        }

        // 요청을 보내고 응답을 받을 xmlhttpRequest 객체
        var xmlRequestObj   = new XMLHttpRequest();

        if (id === "" || pw === "") {
            alert("ID 또는 비밀번호를 입력하지 않았습니다!");
        }
        else if(userId === "") {
            alert('아이디를 입력하세요!');
        }
        else {
            xmlRequestObj.open('POST', url, true);
            xmlRequestObj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlRequestObj.send(param);
        }

        xmlRequestObj.onreadystatechange = function () {
            if (xmlRequestObj.readyState == 4 && xmlRequestObj.status == 200) {
                if (buttonValue == '로그인') {
                    var loginMsg   = xmlRequestObj.responseText;

                    if (loginMsg == 'loginFailed') {
                        alert('잘못된 아이디나 비밀번호를 입력하셨습니다.');
                    }
                    else {
                        while (loginArea.hasChildNodes()) {
                            loginArea.removeChild(loginArea.firstChild);
                        }
                        location.reload();
                    }
                }
                else if(buttonValue == '로그아웃') {
                    alert('로그아웃 되었습니다.');
                    location.reload();
                }
                else {
                    if (xmlRequestObj.responseText == "true") {
                        newMemberFlag = true;
                        alert('사용하실 수 있는 아이디 입니다.');
                    }
                    else
                        alert('사용하실 수 없는 아이디 입니다.');
                }

            }
        };

    }

    // <-- 검색 옵션이 출력되는 간격 조정
    function searchOpSize() {
        var searchOp                = document.getElementById('searchOp');

        searchOp.style.marginLeft   = "100px";

    }

</script>
<body>
<div id="loginBar" class="container">
<div id="loginBox">
<table id="loginAreaTable">
<tr>
    <?
    echo "<div id='loginArea'>";

    // 로그인 상태가 아닌 경우
    if (!isset($_SESSION['user_id'])) {
        echo "<td>&nbsp;아이디&nbsp;</td>";
        echo "<td><input type='text' class='form-control' id='idBox'></td>";
        echo "<td>&nbsp;비밀번호&nbsp;</td>";
        echo "<td><input type='password' class='form-control' id='pwBox'></td>";
        echo "<td>&nbsp;<input type='button' class='btn btn-info' id='loginBt' value='로그인' onclick='httpRequest()'></td>";
        echo "<td>&nbsp;<input type='button' class='btn btn-info' value='회원등록' data-toggle='modal' data-target='#newMemberTemplate'></td>";
    }
    // 로그인 했을 경우
    else {
        echo "<td>" . $_SESSION['user_alias'] . "님 환영합니다. &nbsp;</td>";
        echo "<td><input type='button' class='btn btn-info' value='로그아웃' id='logoutBt' onclick='httpRequest()'></td>";
    }

    echo "</div>";
    echo "<td>";
    echo "<form action='articleList.php' method='get'>";
    echo "<select id='searchOp' name='searchOp' class='form-control'>";
    // <-- 검색모드일 경우 선택했던 옵션과 검색했던 키워드 표시
    if ($searchMode == true) {
        switch ($option) {
            // 제목
            case "title" : {
                echo "<option value='title' selected='selected'>제목</option>";
                echo "<option value='contents'>내용</option>";
                echo "<option value='writer'>작성자</option>";
                echo "<option value='titleContents'>제목+내용</option>";
                break;
            }
            // 내용
            case "contents" : {
                echo "<option value='title'>제목</option>";
                echo "<option value='contents' selected='selected'>내용</option>";
                echo "<option value='writer'>작성자</option>";
                echo "<option value='titleContents'>제목+내용</option>";
                break;
            }
            // 글쓴이
            case "writer" : {
                echo "<option value='title'>제목</option>";
                echo "<option value='contents'>내용</option>";
                echo "<option value='writer' selected='selected'>작성자</option>";
                echo "<option value='titleContents'>제목+내용</option>";
                break;
            }
            // 제목 + 내용
            case "titleContents" : {
                echo "<option value='title'>제목</option>";
                echo "<option value='contents'>내용</option>";
                echo "<option value='writer'>작성자</option>";
                echo "<option value='titleContents' selected='selected'>제목+내용</option>";
                break;
            }
        }
        echo "</select>";
        echo "</td>";
        echo "<td><input type='text' id='searchText' value='$keyword' class='form-control' name='searchKeyword'></td>";
    }
    // <-- 검색모드가 아닐경우 검색 옵션 표시
    else {
        echo "<option value='title' selected='selected'>제목</option>";
        echo "<option value='contents'>내용</option>";
        echo "<option value='writer'>작성자</option>";
        echo "<option value='titleContents'>제목+내용</option>";
        echo "</select>";
        echo "</td>";
        echo "<td><input type='text' id='searchText' class='form-control' name='searchKeyword'></td>";
    }

    ?>
    <td><input type="submit" class="btn btn-info" value="검색" id="searchBtn"> </td>
    </form>
</tr>
</table>
</div>
</div>

<div id="articleListArea" class="container">
<h1>자유게시판</h1>

<div id="boardList">
    <table id="boardTable" class="table table-hover">
        <thead>
        <tr>
            <th width="70px">글 번호</th>
            <th width="400px">제목</th>
            <th width="100px">작성자</th>
            <th width="70px">조회수</th>
            <th width="70px">덧글수</th>
            <th width="200px">작성일</th>
        </tr>
        </thead>
        <?

            for ($num = 0; $num < $currentRow; $num++) {
                $row            = mysqli_fetch_row($queryResult);

                // <-- 덧글수 조회
                $query          = "select * from board where board_pid=".$row[0];
                $commentResult  = mysqli_query($dbConn, $query);
                $commentCount   = mysqli_num_rows($commentResult);

                $articleID      = $row[0];

                // <-- 검색모드일 경우 글 링크 설정
                if ($searchMode == true)
                    $articleLink = 'articleRead.php?page=' . $currentPage . '&articleId=' . $articleID .
                        '&searchOp=' . $option . '&searchKeyword=' . $keyword;
                // <-- 검색 모드가 아닐 경우 글 링크 설정
                else
                    $articleLink = 'articleRead.php?page=' . $currentPage . '&articleId=' . $articleID;

                echo "<tr>";
                echo "<td >$row[0]</td>";
                echo "<td ><a href=$articleLink>$row[2]</a></td>";
                echo "<td>$row[5]</td>";
                echo "<td>$row[6]</td>";
                echo "<td>$commentCount</td>";

                // <-- 작성일이 오늘일 경우는 시간까지 출력, 그 이외는 날짜만 출력
                $today      = date('Y-m-d');
                $dateArr    = explode(" ", $row[7]);

                if($dateArr[0] != $today)
                    echo "<td>$dateArr[0]</td>";
                else
                    echo "<td>$row[7]</td>";


                echo "</tr>";
            }

            // <-- db 연결 종료
            mysqli_close($dbConn);
        ?>
    </table>
</div>
</div>

<div id="boardPage" class="container" align="center">
<?
    // <-- 페이지 네이션

    // <-- 처음 마지막 페이지 링크 설정
    // 검색 모드일 경우 처음 마지막 버튼 페이지 설정
    if ($searchMode == true) {
        $firstPageLink  = 'articleList.php?searchOp=' . $option . '&searchKeyword=' .
                            $keyword . '&currentPageNum=' . '1';
        $lastPageLink   = 'articleList.php?searchOp=' . $option . '&searchKeyword=' .
                            $keyword . '&currentPageNum=' . $allPage;
    }
    // 검색 모드 아닐 경우 처음 마지막 버튼 페이지 설정
    else {
        $firstPageLink  = 'articleList.php?currentPageNum=' . '1'; // 처음 페이지
        $lastPageLink   = 'articleList.php?currentPageNum=' . $allPage; // 마지막 페이지
    }

    // <-- 이전 다음 버튼 페이지 이동 단위 설정
    $pageCount          = 10; // 페이지 셋의 개수
    $allPageSet         = ceil($allPage / $pageCount); // 전체 페이지 셋
    $pageSet            = ceil($currentPage / $pageCount); // 현재 페이지 셋
    $setStart           = (($pageSet - 1) * $pageCount) + 1; // 현재 페이지셋 시작 번호
    $setEnd             = (($pageSet - 1) * $pageCount) + $pageCount; // 현재 페이지 셋 마지막 번호


    // 현재 페이지셋 마지막 번호가 전체 페이지 보다 많을 경우 전체 페이지로 설정
    if ($setEnd > $allPage)
        $setEnd = $allPage;

    // 전체 페이지가 10페이지 이하일 경우 한 페이지 씩 이동
    if ($allPage <= 10) {
        $nextPage       = $currentPage + 1;
        $previous       = $currentPage - 1;

    }
    // 전체 페이지가 11페이지 이상일 경우 한 페이지 셋씩 이동
    else {
        $nextPage       = $setStart + 10; // 다음페이지
        $previous       = $setStart - 1; // 이전 페이지
    }

    // <-- 이전 다음 페이지 링크 설정

    // 검색 모드일 경우 이전 다음 버튼 페이지 링크 설정
    if ($searchMode == true) {
        $previousLink   = 'articleList.php?searchOp=' . $option . '&searchKeyword=' .
                            $keyword . '&currentPageNum=' . $previous;
        $nextPageLink   = 'articleList.php?searchOp=' . $option . '&searchKeyword=' .
                            $keyword . '&currentPageNum=' . $nextPage;
    }
    // 검색모드 아닐경우 이전 다음 버튼 페이지 링크 설정
    else {
        $previousLink   = 'articleList.php?currentPageNum=' . $previous;
        $nextPageLink   = 'articleList.php?currentPageNum=' . $nextPage;
    }

    // <-- 처음, 이전 버튼 출력

    // 1페이지 일경우는 처음 버튼 출력 X
    if ($currentPage != 1)
        echo "<input type='button' class='btn btn-info' value='<<' onclick=location.href='$firstPageLink' >";
    // 10페이지 이하일 경우는 한페이지씩 이동 (1페이지셋)
    // 1페이지셋 이상일 경우페이지셋이 1 페이지 셋일 경우 이전 버튼 출력 X
    if(($currentPage !=  1 && $allPage <= 10) || $pageSet != 1)
        echo "<input type='button' class='btn btn-default' value='<' onclick=location.href='$previousLink' >";

    // <-- 페이지 네이션 출력
    echo "<ul class='pagination' id='pageArea'>";

    for ($page = $setStart; $page <= $setEnd; $page++) {
        // 검색 모드일 경우 페이지 링크설정
        if ($searchMode == true)
            $link = 'articleList.php?searchOp=' . $option . '&searchKeyword=' .
                $keyword . '&currentPageNum=' . $page;
        // 검색 모드 아닐 경우 페이지 링크 설정
        else
            $link = 'articleList.php?currentPageNum=' . $page;

        // 현재 페이지일 경우는 현재페이지로의 이동 X, 다른 페이지로만 이동
        if ($page != $currentPage)
            echo "<li><a href=$link id='page'>$page</a></li>";
        else
            echo "<li><a id='currentPage'>$page</a></li>";
    }
    echo "</ul>";

    // <-- 이전, 다음 버튼 출력

    // 10페이지 이하일 경우는 한페이지씩 이동 (1페이지셋)
    // 1페이지셋 이상일 경우 현재 페이지 셋이 마지막 페이지 셋과 아닐 경우 다음 버튼 출력
    if(($allPage != $currentPage && $allPage <= 10)  || $allPageSet != $pageSet)
        echo "<input type='button' class='btn btn-default' value='>' onclick=location.href='$nextPageLink' >";
    // 현재 페이지가 마지막 페이지가 아닐경우만 마지막 버튼 출력
    if ($currentPage != $allPage)
        echo "<input type='button' class='btn btn-info' value='>>' onclick=location.href='$lastPageLink' >";

?>
</div>

<div id ="boardBtn" class="container">

   <input type="button" id="writeArticle" class="btn btn-info" value="글쓰기" data-toggle='modal' data-target='#writeTemplate'>
   <input type="button" id="allArticleView" class="btn btn-default" value="전체글보기" onclick="location.href='articleList.php?';">
</div>

<?php
// <-- 로그인시 검색 창 위치 설정
if(isset($_SESSION['user_id']))
    echo "<script>document.getElementById('searchOp').style.marginLeft='400px';</script>";
// <-- 로그인 안했을 경우 글쓰기 버튼이 표시 되지 않도록 하기
if(!isset($_SESSION['user_id']))
    echo "<script>document.getElementById('writeArticle').style.display='none';</script>";
?>

<!-- 모달 창 설정 -->
<!-- 글쓰기 모달 창 -->
<div class="modal fade" id="writeTemplate" role="dialog">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">글쓰기</h2>
        </div>
        <div class="modal-body">
        <form action="articleWrite.php" method="post" id="writeForm">
                <table id="editTable">
                <tr>
                    <td>제목 : </td>
                    <td><input type="text" class='form-control' name="title" id="titleBox"></td>
                </tr>

                <tr>
                    <td>작성자 : &nbsp;</td>
                    <?
                     $writer = $_SESSION['user_alias'];
                    echo "<td><input type='text' name='userAlias' id='writerBox' value=$writer readonly ></td>"
                    ?>
                </tr>

                <tr>
                    <td id="articleTitle" >내용 : </td>
                    <td><textarea name="contents" class='form-control' rows="30" id="textBox"></textarea></td>
                </tr>
                </table>
        </div>
        <div class="modal-footer">
            <input type="button" id="write" value="작성하기" class='btn btn-info' onclick="articleCheck()">
            <input type="button" id="cancel" value="취소" class='btn btn-default' data-dismiss="modal">
        </form>
        </div>
    </div>
    </div>
</div>

<!-- 회원가입 모달창 -->
<div class="modal fade" id="newMemberTemplate" role="dialog">
<div class="modal-dialog">
        <!-- 모달 회원가입 템플릿 -->
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">회원등록</h3>
            </div>
            <div class="modal-body">
                <form action="newMember.php" method="post" id="memberForm">
                    <table id="memberTable">
                    <?

                    echo "<tr>";
                    echo "<td>* 회원등록 정보를 입력 해 주세요.</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<td>아이디 :&nbsp;&nbsp;</td>";
                    echo "<td><input type='text' class='form-control' name='userId' id='userId'></td>";
                    echo "<td><input type='button' id='check' value='중복확인' class='btn btn-info' onclick='httpRequest()'></td>";
                    echo "</tr>";

                    echo "<tr>";
                    echo "<td>비밀번호 :&nbsp;&nbsp;</td>";
                    echo "<td><input type='password' class='form-control' name='userPw' id='userPw'></td>";
                    echo "</tr>";

                    echo "<tr>";
                    echo "<td>비밀번호 확인 :&nbsp;&nbsp;</td>";
                    echo "<td><input type='password' class='form-control' name='userPwCheck' id='userPwCheck'></td>";
                    echo "</tr>";

                    echo "<tr>";
                    echo "<td>닉네임 :&nbsp;&nbsp;</td>";
                    echo "<td><input type='text' class='form-control' name='userAlias' id='userAlias'></td>";
                    echo "</tr>";
                    ?>

                    </table>
            </div>

            <div class="modal-footer">
                <input type="button" id='write' value="등록하기" class='btn btn-info' onclick="newMemberCheck()">
                <input type="button" id='cancel' value="취소" class='btn btn-default' data-dismiss="modal">
                </form>
            </div>
        </div>
</div>
</div>

</body>
</html>
