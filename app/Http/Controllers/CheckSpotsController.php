<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\DomCrawler\Crawler;

class CheckSpotsController extends Controller
{
    public function __invoke()
    {
        $spotsAvailable = $this->getSpotsAvailable() ?? 'Error';

        return view('spots', compact('spotsAvailable'));
    }

    private function getSpotsAvailable()
    {
        $url = 'https://ultrasignup.com/register.aspx?did=102258';
        $client = new Client();
        try {
            $response = $client->get($url);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);
            $spotsElement = $crawler->filter("span#ContentPlaceHolder1_PriceList_lblSpots_0");
            if ($spotsElement->count() > 0) {
                return trim($spotsElement->text());
            }
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
}
