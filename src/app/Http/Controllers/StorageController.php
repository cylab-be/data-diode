<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\StorageService;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use \Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Uploader;
use App\Http\Requests\StorageUploadRequest;
use App\Http\Requests\StorageDownloadRequest;
use App\Http\Requests\StorageZipRequest;
use App\Http\Requests\StorageGetZipRequest;
use App\Http\Requests\StorageRemoveRequest;

/**
 * Controller used to browse the data sent by the diode in.
 */
class StorageController extends Controller {
 
    private $storageService;
 
    /**
     * StorageController constructor.
     *
     * @param StorageService the storage service.
     */
    public function __construct( StorageService $storageService ) {
        $this->middleware(["auth", "default-password"]);
        $this->storageService = $storageService;
    }
 
    /**
     * List the content of a directory into a view.
     *
     * @param String $path  The directory path to get its content
     * @return mixed        The view
     */
    public function listView(String $path = '.') {
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
     * Upload a file in the 'diode_local' filesystem under
     * the uploader's folder.
     * 
     * @param StorageUploadRequest  The request made by the user
     * @param Uploader $uploader    The uploader
     * @return mixed                The json response containing the number of files
     *                              that have been uploaded
     */
    public function upload(StorageUploadRequest $request, Uploader $uploader) 
    {
        $success = $this->storageService->upload($request, $uploader);
        $file = $request->input('input_file');
        response()->json(['filename' => $file['name']]);
        if (!$success) {
            response()->json(['message' => 'Failed to upload.'], 400);
        }
    }

    /**
     * Download a file.
     *
     * @param StorageDownloadRequest $request   The request made by the user
     * @return StreamedResponse                 The requested file
     * @throws FileNotFoundException            The eventual exception concerning the file path
     */
    public function download(StorageDownloadRequest $request)
    {
        try {
            $path = $request->input('path');
            return $this->storageService->download($path);
        } catch (FileNotFoundException $exception) {
            return response()->json(['message' => 'File not found.'], 404);
        }
    }

    /**
     * Zip a folder into the .zips folder.
     *
     * @param StorageZipRequest $request    The request made by the user
     * @return mixed                        The json response
     */
    public function zip(StorageZipRequest $request) 
    {        
        $folderPath = base_path('storage') . '/app/files' . $request->input('path');
        $destPath = base_path('storage') . '/app/files/.zips/';
        $destZip = $destPath . $request->input('name') . '_' . $request->input('time') . '.zip';
        // The double quotes are here to avoid errors with paths containing spaces
        $cmd = 'sudo ' . base_path('app/Scripts') . '/zip-folder.sh "';
        $cmd .= $folderPath . '" "' . $destZip . '" "' . $request->input('name') . '"';
        $process = new Process($cmd);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            return response()->json([
                'message' => 'Failed to compress ' . $request->input('name'),
                'exception' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Get a previously zipped folder
     *
     * @param StorageZipRequest $request    The request made by the user
     * @return StreamedResponse             The requested zip file
     * @throws FileNotFoundException        The eventual exception concerning the file path
     */
    public function getZip(StorageGetZipRequest $request)
    {
        try {
            $path = '.zips/' . $request->input('name') . '_' . $request->input('time') . '.zip';
            return $this->storageService->download($path);
        } catch (FileNotFoundException $exception) {
            return response()->json(['message' => 'File not found.'], 404);
        }
    }

    /**
     * Remove a folder or a file.
     *
     * @param StorageRemoveRequest $request The request made by the user
     * @return mixed                        The json response
     */
    public function remove(StorageRemoveRequest $request)
    {
        // The double quotes are here to avoid errors with paths containing spaces
        $cmd = 'sudo ' . base_path('app/Scripts') . '/remove-file-or-folder.sh "';
        $cmd .= base_path('storage') . '/app/files' . $request->input('path') . '"';
        if ($request->input('path') == './.zips') {
            $cmd = 'sudo ' . base_path('app/Scripts') . '/remove-file-or-folder.sh ';
            $cmd .= base_path('storage') . '/app/files/.zips/*';
        }
        $process = new Process($cmd);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            return response()->json([
                'message' => 'Failed to remove ' . $request->input('name'),
                'exception' => $exception->getMessage()
            ], 400);
        }
    }
}
