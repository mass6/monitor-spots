<?php

namespace App\Console\Commands;

use App\Mail\StaticEmail;
use Exception;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\DomCrawler\Crawler;

class MonitorSpotsCommand extends Command
{
    protected $signature = 'spots:monitor';
    protected $description = 'Monitor spots available on ultrasignup.com';

    private $url = 'https://ultrasignup.com/register.aspx?did=102258';

    public function handle()
    {
        $previousSpots = Redis::get('previous_spots', 'No data');
        $spotsAvailable = $this->getSpotsAvailable();
        if ($spotsAvailable !== null) {
            $this->info($spotsAvailable);
            if ($spotsAvailable !== $previousSpots) {
                $this->notifyUser("Current spots now available: $spotsAvailable");
                Redis::set('previous_spots', $spotsAvailable);
            }
        } else {
            $this->error('Failed to fetch spots information.');
        }
    }

    private function getSpotsAvailable()
    {
        $client = new Client();
        try {
            $response = $client->get($this->url);
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

    private function notifyUser($message)
    {
        $emailAddress = env('NOTIFY_ADDRESS'); // Replace this with your static email address

        try {
            Mail::to($emailAddress)->send(new StaticEmail($message));
            $this->info('Email sent successfully to ' . $emailAddress);
        } catch (Exception $e) {
            $this->error('Error sending email: ' . $e->getMessage());
        }
    }
}
