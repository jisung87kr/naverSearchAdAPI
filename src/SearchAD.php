<?php
namespace Ujsstudio\App;

use Ujsstudio\App\Lib\RestApi;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;


class SearchAD extends RestApi
{
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function init($baseUrl, $apiKey, $secretKey, $customerId)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->customerId = $customerId;
        $this->setting = [
            'keywordtool' => [
                'siteId' => 'bsn-a001-00-000000004370037',
                'biztpId' => 1,
                'hintKeywords' => null,
                'month' => date('n'),
                'showDetail' => 1
            ]
        ];
    }

    public function crawlNaverShopping($keyword, $returnType=true)
    {
        $url = "https://search.shopping.naver.com/search/all?query=$keyword";
        $res = $this->client->get($url);
        $res = $res->getBody();
        $html = (string)$res; // 문자열로 형변환
        // dom 필터링
        $crawler = new Crawler($html);
        $nodeValues = $crawler->filter("#__NEXT_DATA__")->each(function(Crawler $node, $i){
            return $node->text();
        });

        return $nodeValues;
    }

    /**
     * 제공받은 정보
     * relKeyword = 연관키워드
     * monthlyPcQcCnt = 월간 PC검색량
     * monthlyMobileQcCnt = 월간 MO검색량
     * monthlyAvePcClkCnt = 월간 PC클릭수
     * monthlyAveMobileClkCnt = 월간 MO클릭수
     * monthlyAvePcCtr = 월간 PC클릭률
     * monthlyAveMobileCtr = 월간 MO클릭률
     * plAvgDepth = 월평균노출광고
     * compIdx = 경쟁정도
     * 재가공 정보
     * idx = index
     * total = 키워드의 총 상품수 (crawlNaverShopping 메서드에서 구함 _ 프론트앤드에서 호출)
     * monthlyTotalQcCnt = 월간 검색수
     * monthlyAveTotalClkCnt = 월간 클릭수
     * monthlyAveTotalCtr = 월간 클릭률
     * ratio = 상품수/검색수
     * @param array $result
     * @return string
     */
    public function makeKeywordList(array $result) : string
    {
        $list = [];
        foreach ($result['keywordList'] as $index => $item) {
            $monthlyPcQcCnt         = $this->onlyNum($item['monthlyPcQcCnt']);
            $monthlyMobileQcCnt     = $this->onlyNum($item['monthlyMobileQcCnt']);
            $monthlyAvePcClkCnt     = $this->onlyNum($item['monthlyAvePcClkCnt']);
            $monthlyAveMobileClkCnt = $this->onlyNum($item['monthlyAveMobileClkCnt']);
            $monthlyAvePcCtr        = $this->onlyNum($item['monthlyAvePcCtr']);
            $monthlyAveMobileCtr    = $this->onlyNum($item['monthlyAveMobileCtr']);
            $plAvgDepth             = $this->onlyNum($item['plAvgDepth']);
            $compIdx                = $item['compIdx'];
            $relKeyword             = $item['relKeyword'];


            $list[$index]['monthlyPcQcCnt']         = $monthlyPcQcCnt;
            $list[$index]['monthlyMobileQcCnt']     = $monthlyMobileQcCnt;
            $list[$index]['monthlyAvePcClkCnt']     = $monthlyAvePcClkCnt;
            $list[$index]['monthlyAveMobileClkCnt'] = $monthlyAveMobileClkCnt;
            $list[$index]['monthlyAvePcCtr']        = $monthlyAvePcCtr;
            $list[$index]['monthlyAveMobileCtr']    = $monthlyAveMobileCtr;
            $list[$index]['plAvgDepth']             = $plAvgDepth;
            $list[$index]['compIdx']                = $compIdx;
            $list[$index]['idx']                    = $index;
            $list[$index]['total']                  = ''; // ajax로 값을 얻음. 구조만 생성
            $list[$index]['monthlyTotalQcCnt']      = $monthlyPcQcCnt + $monthlyMobileQcCnt;
            $list[$index]['monthlyAveTotalClkCnt']  = $monthlyAvePcClkCnt + $monthlyAveMobileClkCnt;
            $list[$index]['monthlyAveTotalCtr']     = ($monthlyAvePcCtr + $monthlyAveMobileCtr) / 2;
            $list[$index]['relKeyword']             = $relKeyword;
            $list[$index]['ratio']                  = '';
        }
        $result['keywordList'] = $list;
        $json = json_encode($result, JSON_UNESCAPED_UNICODE);
        return $json;
    }

    public function getKeywordsInfo(array $keywords=[], array $params=[]) : array
    {
        $arr = [];

        $params = ( empty($params) ) ? $this->setting['keywordtool'] : $params;
        foreach ($keywords as $index => $keyword) {
            $params['hintKeywords'] = $keyword;
            $result = $this->GET('/keywordstool', $params);
            $keywordsToolData = json_decode($this->makeKeywordList($result), true);
            $arr[] = $keywordsToolData;
        }

        return $arr;
    }

    public function seperateAd(array $keywordsToolData) : array
    {
        $arr = [
            'normal' => [],
            'ad' => [],
        ];

        foreach ($keywordsToolData as $index => $keywordsToolDatum) {
            $item = $keywordsToolDatum['item'];
            if(isset($item['adId'])){
                $arr['ad'][] = $item;
            } else {
                $arr['normal'][] = $item;
            }
        }

        return $arr;
    }

    public function mergeKeywordsInfo(array $keywordsInfo) : array
    {
        $arr = [];
        $firstItemArr = [];
        foreach ($keywordsInfo as $index => $item){
            $firstItemArr[] = $item['keywordList'][0];
        }

        foreach ($firstItemArr as $index => $item) {
            $keyword = $item['relKeyword'];
            $crawlData = $this->crawlNaverShopping($keyword);
            $crawlDataJson = html_entity_decode($crawlData[0]);
            $crawlData = json_decode($crawlDataJson, true);
            $productList = $crawlData['props']['pageProps']['initialState']['products']['list'];
            $exceptedProductList = $this->seperateAd($productList);
            $normalProductList = $exceptedProductList['normal'];
            $rank1Product = $normalProductList[0];

            $item['total'] = $crawlData['props']['pageProps']['initialState']['products']['total'];
            $item['ratio'] = $item['total'] / $item['monthlyTotalQcCnt'];
            $rank1Product['categoryStr'] = $this->makeProductCategory($rank1Product);
            $mergedData = array_merge($item, $rank1Product);
            $arr[] = $mergedData;
        }
        return $arr;
    }

    public function makeProductCategory(array $product) : string
    {
        $arr = [
            $product['category1Name'],
            $product['category2Name'],
            $product['category3Name'],
            $product['category4Name'],
        ];

        $str = '';

        foreach ($arr as $index => $item) {
            if(!$item){
                continue;
            }

            if($index == 0){
                $str .= $item;
            } else {
                $str .= ' > '.$item;
            }
        }

        return $str;
    }

    public function textSeperator(string $text, string $seperator=',') : array
    {
        if(!$text){
            return false;
        }

        $arr = [];
        $keywords = explode($seperator, $text);
        foreach ($keywords as $index => $keyword) {
            $arr[] = $this->sterilizeText($keyword);
        }

        return $arr;
    }

    public function sterilizeText(string $text) : string
    {
        return trim(str_replace(' ', '', $text));
    }

    public function onlyNum(string $str)
    {
        $num = str_replace('< ', '', $str);
        return (double) $num;
    }

    public function dd($dump)
    {
        echo '<pre>';
        var_dump($dump);
        echo '</pre>';
    }

    public function ddd($dump)
    {
        $this->dd($dump);
        exit;
    }
}