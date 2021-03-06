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
//                'event' => 0,
                'month' => date('n'),
                'showDetail' => 1
            ];
            $result = $myCrawler->GET('/keywordstool', $params);
            $json = $myCrawler->makeKeywordList($result);
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
    .item-detail{
        font-size: 14px;
    }

    .item-link{
        display: block;
        position: relative;
        padding-top: 200px;
        overflow: hidden;
    }

    .item-link img{
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        -webkit-transform: translate(-50%, -50%);
    }

    .tpl-box{
        height: 800px;
        overflow: auto;
    }
</style>
<body>
<div class="container-fluid">
    <?php include './include/nav.php';?>
    <div class="border my-1 p-5">
        <h1 class="text-center">????????? ???????????? ????????? ??????</h1>
        <form action="" class="my-3" id="search-form">
            <div class="row g-3 justify-content-center">
                <div class="col-auto">
                    <input type="text" name="keyword" value="" placeholder="????????? ??????" class="form-control">
                </div>
                <div class="col-auto">
                    <input type="submit" value="??????" class="btn btn-primary btn-search">
                </div>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover" style="font-size: 12px;" id="dataTable">
            <thead>
            <tr>
                <td>#</td>
                <td>?????????</td>
                <td>PC??????</td>
                <td>MO??????</td>
                <td>?????????</td>
                <td>PC???????????????</td>
                <td>MO???????????????</td>
                <td>PC???????????????</td>
                <td>MO???????????????</td>
                <td>?????????????????????</td>
                <td>?????????</td>
                <td>????????????</td>
                <td>?????????</td>
                <td>????????????</td>
                <td>????????????</td>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="14" class="text-center">??????????????? ????????????.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="template">
    <div class="item-detail p-3 border d-none">
        <div class="card">
            <div class="row">
                <div class="col-3">
                    <a href="" class="item-link" target="_blank">
                        <img src="https://via.placeholder.com/500" alt="" class="w-100 item-img">
                    </a>
                </div>
                <div class="col-9">
                    <div class="card-body placeholder-glow">
                        <div class="badge-box mb-2">
                            <span class="badge bg-primary item-rank"></span>
                        </div>
                        <h5 class="card-title item-title"></h5>
                        <p class="card-text text-danger item-price fw-bolder"></p>
                        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                            <ol class="breadcrumb item-category">
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="./js/common.js"></script>
<script>
    $(document).ready(function() {
        var tb;
        function childData(data, _this){
            var total = data.props.pageProps.initialState.products.total;
            var tr = _this.closest('tr');
            var totalCell = tb.cell(tr, 12);
            var ratioCell = tb.cell(tr, 11);
            var categoryCell = tb.cell(tr, 13);
            var searchTotalCell = tb.cell(tr, 4);
            var searchTotal = Number($(searchTotalCell.node()).text());
            var tpl = makeTpl(data);
            searchTotal = total/searchTotal;
            totalCell.data(total);
            ratioCell.data(searchTotal.toFixed(2));

            var firstCategory = tpl.find(".item-detail").not(".ad").eq(0).find(".item-category .category-item");
            var categoryText = '';
            for (var i=0; i < firstCategory.length; i++){
                if(i == firstCategory.length-1){
                    categoryText += $(firstCategory[i]).text();
                } else {
                    categoryText += $(firstCategory[i]).text()+' > ';
                }
            }
            categoryCell.data(categoryText);
            return tpl;
        }

        function makeTpl(data){
            var products = data.props.pageProps.initialState.products.list;
            var tplBox = $("<div class='tpl-box'></div>");
            for(var i=0; i < 10; i++){
                var tpl = $(".template .item-detail").clone();
                console.log(tpl);
                var item = products[i].item;
                var isAd = item.adId == undefined ? false : true;
                var itemImgSrc= item.imageUrl;
                var itemTitle = item.productTitle;
                var itemPrice = item.price;
                var itemCategory = makeCategory(item);
                var itemLink = isAd == true ? item.adcrUrl : item.mallProductUrl;
                var itemRank = item.rank;
                if(isAd){
                    tpl.addClass('ad');
                    tpl.find('.badge-box').append('<span class="badge bg-secondary">AD</span>');
                }
                tpl.find(".item-link").attr('href', itemLink);
                tpl.find(".item-img").attr('src', itemImgSrc);
                tpl.find(".item-title").text(itemTitle);
                tpl.find(".item-price").text(itemPrice+'???');
                tpl.find(".item-rank").text(itemRank+'???');
                for(var j=0; j < itemCategory.length; j++){
                    tpl.find(".item-category").append(itemCategory[j]);
                }
                tpl.removeClass("d-none");
                tplBox.append(tpl);
            }
            return tplBox;
        }

        function makeCategory(item){
            var arr = [
                item.category1Name,
                item.category2Name,
                item.category3Name,
                item.category4Name
            ];
            var list = [];
            for(var i=0; i < arr.length; i++){
                if(arr[i] == undefined || arr[i] == ''){
                    continue;
                }
                var tpl = $('<li class="breadcrumb-item category-item"></li>');
                list.push(tpl.text(arr[i]));
            }

            return list;
        }

        function initDataTable(el, data){
            var dtOpt = {
                paging: false,
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
                    {
                        data: 'relKeyword',
                        render: function(data, type, row, meta){
                            return '<a href="https://search.shopping.naver.com/search/all?query='+data+'" target="_blank">'+data+'</a>';
                        }
                    },
                    {data: 'monthlyPcQcCnt'},
                    {data: 'monthlyMobileQcCnt'},
                    {data: 'monthlyTotalQcCnt'},
                    {data: 'monthlyAvePcClkCnt'},
                    {data: 'monthlyAveMobileClkCnt'},
                    {data: 'monthlyAvePcCtr'},
                    {data: 'monthlyAveMobileCtr'},
                    {data: 'plAvgDepth'},
                    {data: 'compIdx'},
                    {data: 'ratio'},
                    {data: 'total'},
                    {defaultContent: ''},
                    {defaultContent: '<input type="button" value="????????????" class="btn btn-secondary btn-sm btn-more" />'},
                ]
            };
            if(tb){
                tb.destroy();
            }
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
            if(keyword.length == 0){
                alert('???????????? ???????????????');
                return false;
            }
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

