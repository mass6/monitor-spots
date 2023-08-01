<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\EmailNotification;
use App\Mail\StaticEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class SendEmailNotification extends Command
{
    protected $signature = 'email:send';
    protected $description = 'Send an email to a static email address';

    public function handle()
    {
        $emailAddress = 'static@example.com'; // Replace this with your static email address

        try {
            Mail::to($emailAddress)->send(new StaticEmail());
            $this->info('Email sent successfully to ' . $emailAddress);
        } catch (\Exception $e) {
            $this->error('Error sending email: ' . $e->getMessage());
        }
    }
}
