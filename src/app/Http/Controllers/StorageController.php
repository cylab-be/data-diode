<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StorageService;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Controller used to browse the data sent by the diode in.
 */
class StorageController extends Controller {
 
    private $storageService;
 
    /**
     * ApiController constructor.
     *
     * @param StorageService the storage service.
     */
    public function __construct( StorageService $storageService ) {
        $this->middleware(["auth", "default-password"]);
        $this->storageService = $storageService;
    }
 
    /**
     * List of directory content into a view.
     *
     * @param Sring $path the directory path to get its content.
     *
     * @return mixed the view.
     */
    public function listView( String $path = '.' ) {
        $content = $this->storageService->list($path );
        $dirPath = $content['info']['dirPath'];
        if ($dirPath != '.') {
            $dirPath = substr($dirPath, 0, 1) == '/' ? $dirPath : '/' . $dirPath;
        }
        return view('storage', [
            'directories'       => $content['directories'],
            'files'             => $content['files'],
            'quick_navigation'  => $content['quick_navigation'],
            'cannot_be_seen'    => $content['cannot_be_seen'],
            'dirPath'           => $dirPath,
        ]);
    }

    /**
     * Gets the view for the file upload page.
     * @return mixed the view.
     */
    public function uploadIndex()
    {
        return view("upload");
    }

    /**
     * Upload file(s).
     * 
     * @param Resquest the request containing the file(s) to upload.
     * 
     * @return mixed a json containing the number of files that have been uploaded.
     */
    public function upload(Request $request) 
    {
        $nb = $this->storageService->upload($request);
        return response()->json(['nbUploads' => $nb]);
    }

    /**
     * Download file
     *
     * @param Request $request
     *
     * @return StreamedResponse
     * @throws StorageException
     */
    public function download( Request $request )
    {
        if ($request->path == null) {
            return response()->json(['message' => 'Path missing.'], 400);
        }
        if ($this::badPath($request->path)) {
            return response()->json(['message' => 'Invalid path.'], 400);
        }
        return $this->storageService->download( $request );
    }    

    public function zip( Request $request ) 
    {
        if ($request->path == null) {
            return response()->json(['message' => 'Path missing.'], 400);
        }
        if ($request->name == null || $request->time == null) {
            return response()->json(['message' => 'Folder name missing.'], 400);
        }        
        if ($this::badPath($request->path)) {
            return response()->json(['message' => 'Invalid path.'], 400);
        }
        if ($this::badName($request->name)) {
            return response()->json(['message' => 'Invalid folder name.'], 400);
        }
        // The double quotes are here to avoid errors with paths containing spaces
        $folderPath = base_path('storage') . '/app/files' . $request->path;        
        $destPath = base_path('storage') . '/app/files/.zips/';
        $destZip = $destPath . $request->name . '_' . $request->time . '.zip';
        $cmd = 'sudo ' . base_path('app/Scripts') . '/zip-folder.sh "' . $folderPath . '" "' . $destZip . '" "' . $request->name . '"';
        $process = new Process($cmd);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => 'Failed to compress ' . $request->name, 'exception' => $exception->getMessage()], 400);
        }
    }

    public function getZip( Request $request ) 
    {
        if ($request->name == null) {
            return response()->json(['message' => 'Folder name missing.'], 400);
        }
        if ($request->time == null) {
            return response()->json(['message' => 'Folder name missing.'], 400);
        }
        if ($this::badName($request->name) || $this::badName($request->time)) {
            return response()->json(['message' => 'Invalid folder name.'], 400);
        }
        return $this->storageService->downloadZippedFolder( '.zips/' . $request->name . '_' . $request->time . '.zip' );
    }

    public function remove( Request $request )
    {
        if ($request->path == null) {
            return response()->json(['message' => 'Path missing.'], 400);
        }
        if ($request->name == null) {
            return response()->json(['message' => 'Target name missing.'], 400);
        }
        if ($this::badPath($request->path) || $this::badName($request->name)) {
            return response()->json(['message' => 'Invalid path.'], 400);
        }
        // The double quotes are here to avoid errors with paths containing spaces
        $cmd = 'sudo ' . base_path('app/Scripts') . '/remove-file-or-folder.sh "' . base_path('storage') . '/app/files' . $request->path . '"';
        if ($request->path == './.zips') {
            $cmd = 'sudo ' . base_path('app/Scripts') . '/remove-file-or-folder.sh ' . base_path('storage') . '/app/files/.zips/*';
        }
        $process = new Process($cmd);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            return response()->json(['message' => 'Failed to remove ' . $request->name, 'exception' => $exception->getMessage()], 400);
        }
    }

    private function badPath( String $path )
    {
        // A path cannot contain '..'
        if (preg_match('/^.*\.\..*$/', $path)){
            return true;
        }
        return false;
    }

    private function badName( String $name )
    {
        // A name cannot contain '..' or '/'
        if (preg_match('/^.*\.\..*$/', $name) || preg_match('/^.*\/.*$/', $name)){
            return true;
        }
        return false;
    }

}
