<?php

namespace App\Http\Controllers;

use App\Support\CvDataRepository;
use Illuminate\Http\Request;

class CvController extends Controller
{
    protected CvDataRepository $cvData;

    public function __construct(CvDataRepository $cvData)
    {
        $this->cvData = $cvData;
    }

    public function index()
    {
        $data = $this->cvData->getAllData();
        
        return view('pages.cv', $data);
    }
}
