<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Ifsnop\Mysqldump as IMysqldump;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:1');
    }

    public function index(Request $request)
    {
        if ($request->download && file_exists(env('BACKUP_DIR').$request->download)) {
            return response()->download(env('BACKUP_DIR') . $request->download);
        }

        $files = scandir(env('BACKUP_DIR'));

        return array_map(function($f) {
            return [
                'name' => $f,
                'size' => round(filesize(env('BACKUP_DIR').$f) / 1024, 2),
                'modified_at' => filemtime(env('BACKUP_DIR') . $f)
            ];
        }, $files);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fileName' => 'required'
        ]);

        // backup file
        $zip = new \ZipArchive;
        $zip->open(env('BACKUP_DIR'). $request->fileName .'.zip', \ZipArchive::CREATE);
        foreach (scandir('snapshot') as $img) {
            if (!is_dir('snapshot/' . $img)) {
                $zip->addFile('snapshot/' . $img);
            }
            // TODO kalau dia directory
        }

        $zip->close();

        try {
            $dump = new IMysqldump\Mysqldump(
                'mysql:host=localhost;dbname=' . env('DB_DATABASE'),
                env('DB_USERNAME'),
                env('DB_PASSWORD'),
                ['add-drop-table' => true]
        );

            $dump->start(env('BACKUP_DIR') . $request->fileName . '.sql');
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 500);
        }

        return ['message' => 'OK'];
    }

    public function destroy(Request $request)
    {
        if ($request->file && file_exists(env('BACKUP_DIR') . $request->file)) {
            unlink(env('BACKUP_DIR') . $request->file);
        }

        return ['message' => $request->file];
    }

    public function restoreDatabase(Request $request)
    {
        DB::unprepared(file_get_contents(env('BACKUP_DIR') . $request->file));
        return ['message' => 'Database telah di restore'];
    }

    public function restoreSnapshot(Request $request)
    {
        // delete files first
        array_map( 'unlink', array_filter((array) glob("snapshot/*") ) );
        // extract files
        $zip = new \ZipArchive;
        if ($zip->open(env('BACKUP_DIR') . $request->file) === true) {
            $zip->extractTo('./');

            for($i=0; $i<$zip->numFiles; $i++){
                touch($zip->statIndex($i)['name'], $zip->statIndex($i)['mtime']);
            }

            $zip->close();
            return ['message' => 'Snapshot berhasil di restore'];
        } else {
            return response(['message' => 'Gagal extract snapshot'], 500);
        }
    }
}
