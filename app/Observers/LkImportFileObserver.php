<?php

namespace App\Observers;

use App\Models\LkImportFile;

class LkImportFileObserver
{
    public function deleting(LkImportFile $lkImportFile)
    {
        //load attachment as collection and not query attachment()
        if ($lkImportFile->attachment) {
            $lkImportFile->attachment->each->delete();
        }
    }
}
