<?php
ini_set("default_socket_timeout", 30);
ini_set('mbstring.substitute_character', "none");
include './vendor/autoload.php';

use Ujsstudio\App\SearchAD;
use Illuminate\Container\Container;

$config = parse_ini_file("env.ini");
$app = new Container();
$myCrawler = $app->make(SearchAD::class);
$DEBUG = false;
$myCrawler->init($config['BASE_URL'], $config['API_KEY'], $config['SECRET_KEY'], $config['CUSTOMER_ID']);

if(isset($_REQUEST['act'])){
    header('Content-Type: application/json; charset=UTF-8');
    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    switch($_REQUEST['act']){
        case 'keywordList':
            $params = [
                'siteId' => 'bsn-a001-00-000000004370037',
                'biztpId' => 1,
                'hintKeywords' => $keyword,
                'event' => 1,
                'month' => date('n'),
                'showDetail' => 1
            ];
            $result = $myCrawler->GET('/keywordstool', $params);
            $json = json_encode($result, JSON_UNESCAPED_UNICODE);
            $json = str_replace('< ', '', $json);
//            $myCrawler->dd($result);
//            $myCrawler->dd($json);
            break;
        case 'crawlNaverShopping':
            $crawlData = $myCrawler->crawlNaverShopping($keyword);
            $json =  html_entity_decode($crawlData[0]);
            break;
    }

    echo $json;
    exit;
}
?>
<!doctype html>
<html lang="en">
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
    <!-- datatables-->
    <link rel="stylesheet" href="./lib/DataTables/datatables.min.css">
    <script src="./lib/DataTables/datatables.min.js"></script>
</head>
<style>
    .table td,
    .table td{
        word-break: keep-all;
    }
</style>
<body>
<div class="container-fluid">
    <div class="border my-5">
        <form action="" class="my-3" id="search-form">
            <div class="row g-3 justify-content-center">
                <div class="col-auto">
                    <input type="text" name="keyword" value="감자" placeholder="키워드 검색" class="form-control">
                </div>
                <div class="col-auto">
                    <input type="submit" value="검색" class="btn btn-primary btn-search">
                </div>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <h1>키워드 검색결과</h1>
        <table class="table table-bordered table-striped table-hover" style="font-size: 12px;" id="dataTable">
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
                <td>상품수</td>
                <td>추가정보</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<script>
    $(document).ready(function() {
        var tb;
        function childData(data, _this){
            var total = data.props.pageProps.initialState.products.total;
            var tr = _this.closest('tr');
            var cell = tb.cell(tr, 10);
            cell.data(total);
            return '<div>'+total+'</div>';
        }

        function initDataTable(el, data){
            var dtOpt = {
                paging: false,
                stateSave: true,
                fixedHeader: {
                    header: true,
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                data: data,
                columns: [
                    {data: 'idx'},
                    {data: 'relKeyword'},
                    {data: 'monthlyPcQcCnt'},
                    {data: 'monthlyMobileQcCnt'},
                    {data: 'monthlyAvePcClkCnt'},
                    {data: 'monthlyAveMobileClkCnt'},
                    {data: 'monthlyAvePcCtr'},
                    {data: 'monthlyAveMobileCtr'},
                    {data: 'plAvgDepth'},
                    {data: 'compIdx'},
                    {data: 'total'},
                    {defaultContent: '<input type="button" value="추가정보" class="btn btn-secondary btn-sm btn-more" />'},
                ]
            };

            return el.DataTable(dtOpt);
        }

        function getKeywordList(keyword){
            axios.get('./', {
                params: {
                    act: 'keywordList',
                    keyword: keyword,
                }
            }).then(function(response){
                var data = response.data;
                for (var i = 0; i < data.keywordList.length; i++) {
                    data.keywordList[i].idx = i+1;
                    data.keywordList[i].total = '';
                }
                tb = initDataTable($("#dataTable"), data.keywordList);
            }).catch(function(error){
                console.log(error);
            })
        }

        function getCrawlNaverShopping(_this){
            var keyword = _this.closest('tr').find('td').eq(1).text();
            startLoading(true);
            axios.get('./', {
                params : {
                    act: 'crawlNaverShopping',
                    keyword: keyword,
                }
            })
            .then(function(response) {
                var data = response.data;
                // var products = data.props.pageProps.initialState.products;
                // console.log(products);
                _this.attr('data-response', JSON.stringify(data));
                drawChild(_this, data);
                startLoading(false);
            })
            .catch(function(error) {
                console.log(error);
            });
        }

        function startLoading(start){
            if(start){
                $('html').css('cursor', 'progress');
            } else {
                $('html').css('cursor', 'default');
            }
        }

        function drawChild(_this, data)
        {
            var tr = _this.closest('tr');
            var row = tb.row( tr );

            if ( row.child.isShown() ) {
                row.child.hide();
                tr.removeClass('shown');
            }
            else {
                if(data){
                    row.child( childData(data, _this) ).show();
                } else {
                    row.child.show();
                }
                tr.addClass('shown');
            }
        }

        $(".btn-search").click(function(event){
            event.preventDefault();
            var keyword = $(this).closest("form").find("input[name=keyword]").val();
            getKeywordList(keyword);
        });

        $("html").on("click", ".btn-more", function(){
            var hasReponse = $(this).attr('data-response');
            if(hasReponse){
                drawChild($(this), false);
            } else {
                getCrawlNaverShopping($(this));
            }
        });

        $("#exampleModal").on("show.bs.modal", function(event){
            console.log(event);
        });

        $("#exampleModal").on("hidden.bs.modal", function(event){
            console.log(event);
        });
    });
</script>
</body>
</html>

