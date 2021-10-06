<?php
ini_set("default_socket_timeout", 30);
include './vendor/autoload.php';

use Ujsstudio\App\SearchAD;
use Illuminate\Container\Container;

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'crawlNaverShopping'){
    $json = file_get_contents('./myfile2.json');
    echo html_entity_decode($json);
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-F3w7mX95PdgyTmZZMECAngseQB83DfGTowi0iMjiWaeVhAn4FJkqJByhZMI3AhiU" crossorigin="anonymous">
    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/bQdsTh/da6pkI1MST/rWKFNjaCP5gBSY4sEBT38Q/9RBh9AH40zEOg7Hlq2THRZ" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
</head>
<style>
    .table td,
    .table td{
        word-break: keep-all;
    }
</style>
<body>
<script>
    function moreData(keyword){
        $.ajax({
           url : './test.php',
           data : {
               keyword: keyword,
               act: 'crawlNaverShopping'
           },
           dataType : 'json',
           success : function(e){
               desc = e.props.initialProps.ogTag.description;
               // alert(desc);
               console.log(e.props.initialProps.ogTag);
           }
        });
    }
</script>
<div class="container-fluid">
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover" style="font-size: 12px;">
            <thead>
            <tr>
                <td>#</td>
                <td>키워드</td>
                <td>PC검색</td>
                <td>MO검색</td>
                <td>PC평균클릭수</td>
                <td>MO평균클릭수</td>
                <td>PC평균클릭률</td>
                <td>MO평균클릭률</td>
                <td>월평균노출광고</td>
                <td>경쟁도</td>
                <td>추가정보</td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>1</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                    <input type="button" value="추가정보" class="btn btn-secondary btn-sm" onclick="moreData('감자')">
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

