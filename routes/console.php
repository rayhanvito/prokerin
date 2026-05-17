<?php

use App\Jobs\AutoArchiveKepanitiaanJob;
use App\Jobs\CheckOverdueInventoryLoansJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new AutoArchiveKepanitiaanJob)->daily();
Schedule::job(new CheckOverdueInventoryLoansJob)->hourly();
