<?php

namespace App\Http\Controllers;

use App\Support\CvDataRepository;
use App\Support\CvMedia;

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

        $data['cvPhotoUrl'] = CvMedia::profilePhotoUrl($data['contact'] ?? []);

        $data['hobby'] = array_map(static function (array $h): array {
            $h['_image_url'] = isset($h['image']) && is_string($h['image'])
                ? CvMedia::publicAssetUrlIfExists($h['image'])
                : null;

            return $h;
        }, $data['hobby'] ?? []);

        return view('pages.cv', $data);
    }
}
