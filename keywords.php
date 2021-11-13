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
$result = [];
if(isset($_GET['keywords'])){
    $keywords = $myCrawler->textSeperator($_GET['keywords']);
    $keywordsInfo = $myCrawler->getKeywordsInfo($keywords);
    $result = $myCrawler->mergeKeywordsInfo($keywordsInfo);
}

?>
<!doctype html>
<html lang="en">
<head>
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
</head>
<body>
    <div class="container-fluid">
        <?php include './include/nav.php';?>
        <div class="border my-1 p-5">
            <h1 class="text-center">네이버 검색광고 키워드 분석</h1>
            <form action="" class="my-5">
                <div class="input-group">
                    <input type="text" name="keywords" value="<?php echo ( isset($_GET['keywords']) ) ? htmlspecialchars($_GET['keywords']) : ''; ?>" class="form-control" placeholder="">
                    <input type="submit" class="btn btn-primary">
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" style="font-size: 12px;" id="dataTable">
                <thead>
                <tr>
                    <td>#</td>
                    <td>키워드</td>
                    <td>PC검색</td>
                    <td>MO검색</td>
                    <td>검색량</td>
                    <td>PC평균클릭수</td>
                    <td>MO평균클릭수</td>
                    <td>PC평균클릭률</td>
                    <td>MO평균클릭률</td>
                    <td>월평균노출광고</td>
                    <td>경쟁강도</td>
                    <td>상품수</td>
                    <td>카테고리</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($result as $index => $item) : ?>
                <tr>
                    <td><?= $index?></td>
                    <td><?= $item['relKeyword']?></td>
                    <td><?= number_format($item['monthlyPcQcCnt'])?></td>
                    <td><?= number_format($item['monthlyMobileQcCnt'])?></td>
                    <td><?= number_format($item['monthlyTotalQcCnt'])?></td>
                    <td><?= number_format($item['monthlyAvePcClkCnt'])?></td>
                    <td><?= number_format($item['monthlyAveMobileClkCnt'])?></td>
                    <td><?= number_format($item['monthlyAveTotalClkCnt'])?></td>
                    <td><?= number_format($item['monthlyAvePcCtr'])?></td>
                    <td><?= $item['plAvgDepth']?></td>
                    <td><?= number_format($item['ratio'], 1)?> (<?= $item['compIdx']?>)</td>
                    <td><?= number_format($item['total'])?></td>
                    <td><?= $item['categoryStr']?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            var dtOpt = {
                paging: false,
                fixedHeader: {
                    header: true,
                },
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
            };

            $(".table").DataTable(dtOpt);
        });
    </script>
</body>
</html>