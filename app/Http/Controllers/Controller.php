<?php

namespace App\Http\Controllers;

use App\Support\XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

abstract class Controller
{
    /** Build the workbook in a temp file, send it as a download, then delete the temp file. */
    protected function xlsxResponse(XlsxWriter $xlsx, string $filename): BinaryFileResponse
    {
        // .xlsx files are zips, so the PHP zip extension must be enabled
        abort_unless(class_exists(ZipArchive::class), 503, 'Excel export needs the PHP zip extension.');

        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $xlsx->save($path);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
