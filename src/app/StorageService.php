<?php

namespace App;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use League\Flysystem\NotSupportedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Uploader;
use \Illuminate\Contracts\Filesystem\FileNotFoundException;
use App\Http\Requests\StorageUploadRequest;

class StorageService {
 
    /**
     * @var Storage
     */
    private $storage;

    /**
     * StorageService constructor
     *
     * @param FilesystemAdapter $storage
     */
    public function __construct(FilesystemAdapter $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Get directory content.
     *
     * @param String $path  The directory path to get its content
     * @return array        The data needed about the directory
     */
    
    public function list( String $path ) {
        $cantBeSeen  = false;
        $dirInfo     = $this->pathInfo( $path );
        $dirList     = [];
        $filesList   = [];
        if (!$this->storage->exists($path)) {
            // send root if the path does not exist
            $path = '.';
        }
        try {
            $dirList     = $this->directories( $path );
            $filesList   = $this->files( $path );
        } catch(NotSupportedException $exc) {
            $cantBeSeen  = true;
        }
        $qNavigation = $this->quickNavigation( $path );
    
        return [
                'info'       => [
                'dirPath'    => $path,
                'dirName'    => $dirInfo[ 'basename' ] === '.' ? 'Root' : $dirInfo[ 'basename' ],
                'dirCount'   => count( $dirList ),
                'filesCount' => count( $filesList ),
            ],
    
            'directories'      => $dirList,
            'files'            => $filesList,
            'quick_navigation' => $qNavigation,
            'cannot_be_seen'   => $cantBeSeen,
        ];
    }

    /**
     * Get path info.
     *
     * @param String $path  The path
     * @return array        The info
     */
    
    private function pathInfo( $path ) {
        $pathInfo = pathinfo( $path );
    
        return [
            'path'      => $path,
            'basename'  => $pathInfo[ 'basename' ],
            'dirname'   => $pathInfo[ 'dirname' ],
            'filename'  => $pathInfo[ 'filename' ],
            'extension' => $pathInfo[ 'extension' ] ?? '',
        ];
    
    }
    
    /**
     * Get directories list.
     *
     * @param String $directory The directory
     * @return array            The list.
     */
    
    private function directories($directory) {
        $diodeDirectories = $this->storage->directories( $directory );
    
        $directoriesList = [];
    
        foreach ( $diodeDirectories    as  $diodeDirectory ) {
            $dirInfo           = pathinfo( $diodeDirectory );
            $directoriesList[] = [
                'name' => $dirInfo[ 'basename' ],
                'path' => $diodeDirectory,
            ];
        }
        return $directoriesList;
    }
    
    /**
     * Get files list for directory.
     *
     * @param String $directory The directory
    * @return array             The files lits
     */    
    private function files( $directory ) {
        $diodeFiles = $this->storage->files( $directory );
    
        $filesList = [];
        foreach ( $diodeFiles  as  $diodeFile ) {
            $fileInfo = pathinfo( $diodeFile );
    
            $filesList[] = [
                'name' => $fileInfo[ 'basename' ],
                'path' => $diodeFile,
            ];
        }
    
        return $filesList;
    }
    
    /**
     * Get list of path parts for navigation.
     *
     * @param String $directory The directory
     * @return array            The list of files    
     */
    private function quickNavigation( $directory ) {
        $directoriesList = [];
    
        if ( $directory === '.' ) {
            return $directoriesList;
        }
    
        $directoriesList = [
            [
                'name' => 'Root',
                'path' => '.',
            ],
        ];
    
        $pathParts = explode( '/', $directory );
    
        if ( count( $pathParts ) === 1 ) {
            return $directoriesList;
        }
    
        $path = '/';
    
        foreach ( $pathParts as $diodeDirectory ) {
    
            if ( $diodeDirectory === '' ) {
                continue;
            }
    
            $path .= $diodeDirectory;
    
            $directoriesList[] = [
                'name' => $diodeDirectory,
                'path' => $path,
            ];
    
            $path .= '/';
        }
    
        return array_slice(
            $directoriesList, 0, -1
        );
    }

    /**
     * Download a file.
     *
     * @param String $path              The file path
     * @return StreamedResponse         The requested file
     * @throws FileNotFoundException    The eventual exception concerning the file path
     */
    public function download(String $path)
    {        
        if (!$this->storage->exists($path)) {
            throw new FileNotFoundException();
        }
        return $this->storage->download($path);
    }

    /**
     * Upload file(s) in the 'diode_local' filesystem under
     * the uploader's folder.
     *
     * @param StorageUploadRequest  The request made by the user
     * @param  Uploader $uploader   The uploader
     * @return Boolean              True if the upload happend without error, false otherwise
     */
    public function upload(StorageUploadRequest $request, Uploader $uploader)
    {
        $file = $request->file('input_file');
        $fullPath = $request->input('input_file_full_path');
        try {
            $this->uploadFile($file, $fullPath, $uploader->name);
        } catch (Exception $exception) {
            return false;
        }
        $cmd = "sudo python /var/www/data-diode/uploadersScripts/db_uploaders_clie.py update ";
        $cmd .= $uploader->name . " 1";
        $process = new Process($cmd);
        try {
            $process->mustRun();
            return true;
        } catch (ProcessFailedException $exception) {
            return false;
        }
    }
    
    /**
     * Upload a file.
     *
     * @param UploadedFile $file    The file
     * @param String $path          The file path
     * @param STring $uploaderName  The name of the uploader
     */
    private function uploadFile($file, $path, $uploaderName)
    {        
        $this->storage->putFileAs($uploaderName . '/', $file, $path);
    }

}