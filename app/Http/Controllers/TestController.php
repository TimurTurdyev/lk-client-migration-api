<?php

namespace App\Http\Controllers;

use App\Main\Import\ImportRepository;
use App\Main\Import\RecursiveIterationData;
use App\Models\LkImportFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public function __invoke()
    {
        $lkImportFile = LkImportFile::findOrFail(1);

        $file = $lkImportFile->attachment->first();

        $content = json_decode(Storage::disk('public')->get($file->physicalPath()), true);

        $recursiveIteration = new RecursiveIterationData(new ImportRepository());
        $recursiveIteration->apply($content, '.');

    }
}
