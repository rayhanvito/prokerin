<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

final class LandingController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Landing/Home');
    }

    public function features(): Response
    {
        return Inertia::render('Landing/Features');
    }

    public function pricing(): Response
    {
        return Inertia::render('Landing/Pricing');
    }
}
