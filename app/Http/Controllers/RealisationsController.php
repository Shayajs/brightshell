<?php

namespace App\Http\Controllers;

use App\Support\RealisationsRepository;

class RealisationsController extends Controller
{
    public function __construct(protected RealisationsRepository $repo) {}

    public function index()
    {
        return view('pages.realisations', [
            'websites' => $this->repo->websites(),
            'personal' => $this->repo->personal(),
        ]);
    }
}
