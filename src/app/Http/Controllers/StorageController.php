<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StorageService;

class StorageController extends Controller {
 
    private $storageService;
 
    /**
     * ApiController constructor.
     *
     * @param StorageService $storageService
     */
    public function __construct( StorageService $storageService ) {
        $this->middleware(["auth", "default-password"]);
        $this->storageService = $storageService;
    }
 
    /**
     * List of directory content into a view
     *
     * @param Sring $path the directory path to get its content
     *
     * @return mixed the view
     */
    function listView( String $path = '.') {
        $content = $this->storageService->list( $request, $path );
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

}
