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

$params = [
    'siteId' => 'bsn-a001-00-000000004370037',
    'biztpId' => 1,
    'hintKeywords' => $_GET['keyword'],
    'event' => 1,
    'month' => date('n'),
    'showDetail' => 1
];
$result = $myCrawler->GET('/keywordstool', $params);

if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'crawlNaverShopping'){
    $crawlData = $myCrawler->crawlNaverShopping($_GET['keyword']);
    echo html_entity_decode($crawlData[0]);
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
</head>
<style>
    .table td,
    .table td{
        word-break: keep-all;
    }
</style>
<body>
<div class="container-fluid">
    <div class="table-responsive">
        <h1>'<?= $_GET['keyword'] ?>' 키워드 검색 결과 (<?= count($result['keywordList']); ?>건)</h1>
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
            <?php foreach ($result['keywordList'] as $index => $item) : ?>
                <tr>
                    <td><?= $index ?></td>
                    <td><?= $item['relKeyword'] ?></td>
                    <td><?= $item['monthlyPcQcCnt'] ?></td>
                    <td><?= $item['monthlyMobileQcCnt'] ?></td>
                    <td><?= $item['monthlyAvePcClkCnt'] ?></td>
                    <td><?= $item['monthlyAveMobileClkCnt'] ?></td>
                    <td><?= $item['monthlyAvePcCtr'] ?></td>
                    <td><?= $item['monthlyAveMobileCtr'] ?></td>
                    <td><?= $item['plAvgDepth'] ?></td>
                    <td><?= $item['compIdx'] ?></td>
                    <td>
                        <input type="button" value="추가정보" class="btn btn-secondary btn-sm btn-more" data-keyword="<?= $item['relKeyword'] ?>">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        var myModal = new bootstrap.Modal(document.getElementById('exampleModal'));
        function moreData(_this){
            var keyword = _this.data('keyword');
            startLoading(true);
            axios.get('./', {
                params : {
                    keyword: keyword,
                    act: 'crawlNaverShopping'
                }
            })
            .then(function(response) {
                var data = response.data;
                console.log(_this);
                _this.attr('data-response', JSON.stringify(data));
                console.log(data);
                myModal.show();
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
        $(".btn-more").click(function(){
            moreData($(this));
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

