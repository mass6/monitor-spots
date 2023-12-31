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

    private string $url = '';

    public function handle()
    {
        $this->url = config('app.event_url');
        $previousSpots = Redis::get('previous_spots', 'No data');
        $spotsAvailable = $this->getSpotsAvailable();
        logger()->info('Current number of entrants: ' . $spotsAvailable);

        if ($spotsAvailable !== null) {
            $this->info($spotsAvailable);
            if ($spotsAvailable !== $previousSpots || config('app.force_update')) {
                Redis::set('previous_spots', $spotsAvailable);
                $this->notifyUser("Current number of entrants: $spotsAvailable");
            }
        } else {
            logger()->error('Failed to fetch spots information.');
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
            $spotsElement = $crawler->filter("span#ContentPlaceHolder1_lblCount");
            if ($spotsElement->count() > 0) {
                return trim($spotsElement->text());
            }
            return null;
        } catch (Exception $e) {
            logger()->error('Failed to fetch spots information: ' . $e->getMessage());
            return null;
        }
    }

    private function notifyUser($message)
    {
        $emailAddress = config('mail.notify-address');

        try {
            Mail::to($emailAddress)->send(new StaticEmail($message));
            logger()->info('Email sent successfully to ' . $emailAddress);
            $this->info('Email sent successfully to ' . $emailAddress);
        } catch (Exception $e) {
            logger()->error('Error sending email: ' . $e->getMessage());
            $this->error('Error sending email: ' . $e->getMessage());
        }
    }
}
