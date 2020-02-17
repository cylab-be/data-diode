<?php

namespace App;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use League\Flysystem\NotSupportedException;

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
    public function __construct( FilesystemAdapter $storage )
    {
        $this->storage = $storage;
    }

    /**
     * Get directory content
     *
     * @param String $path the directory path to get its content
     *
     * @return array containing the data needed about the directory
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
     * Get path info
     *
     * @param string $path
     *
     * @return array
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
     * Get directories list
     *
     * @param string $directory
     *
     * @return array
     */
    
    private function directories( $directory ) {
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
     * Get files list for directory
     *
     * @param string $directory
     *
     * @return array
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
     * Get list of path parts for navigation
     *
     * @param string $directory
     *
     * @return array
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

}