<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TabulatorController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Tabulator/Dashboard');
    }

    public function judges()
    {
        return Inertia::render('Tabulator/Judges');
    }

    public function scores()
    {
        return Inertia::render('Tabulator/Scores');
    }

    public function results()
    {
        return Inertia::render('Tabulator/Results');
    }

    public function print()
    {
        return Inertia::render('Tabulator/Print');
    }
} 