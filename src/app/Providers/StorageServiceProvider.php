<?php

namespace App\Providers;

use App;
use Illuminate\Support\ServiceProvider;
use App\StorageService;
use Illuminate\Support\Facades\Storage;

class StorageServiceProvider extends ServiceProvider {
 
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
 
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        App::bind(
            StorageService::class, function ( $app ) {
                $storage = Storage::disk( 'diode_local' );
                return new StorageService( $storage );
            }
        );
    }

}