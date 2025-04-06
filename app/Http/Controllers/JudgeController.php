<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class JudgeController extends Controller
{
    public function scoring()
    {
        return Inertia::render('Judge/Scoring');
    }
} 