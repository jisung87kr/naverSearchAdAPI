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

    public function dd($dump)
    {
        echo '<pre>';
        var_dump($dump);
        echo '</pre>';
    }
}