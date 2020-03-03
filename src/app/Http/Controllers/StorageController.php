<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StorageService;

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


}
