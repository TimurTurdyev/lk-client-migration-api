<?php

namespace App\Observers;

use App\Models\MigrateFile;

class LkImportFileObserver
{
    public function deleting(MigrateFile $lkImportFile)
    {
        //load attachment as collection and not query attachment()
        if ($lkImportFile->attachment) {
            $lkImportFile->attachment->each->delete();
        }
    }
}
