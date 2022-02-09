<?php

namespace App\Http\Controllers;

use App\Main\Export\DeviceToTreeRelationRepository;
use App\Main\Import\ImportRepository;
use App\Main\Import\RecursiveIterationData;
use App\Models\LkImportFile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public function __invoke($tree = 0)
    {
        dd('Oops...');

        $lkImportFile = LkImportFile::findOrFail(2);

        $file = $lkImportFile->attachment->first();

        $content = json_decode(Storage::disk('public')->get($file->physicalPath()), true);

        $importRepository = new ImportRepository();
        $recursiveIteration = new RecursiveIterationData($importRepository);
        $recursiveIteration->apply($content);
        dd($importRepository->getModemsNotFound());
    }
}
