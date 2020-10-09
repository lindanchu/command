<?php

namespace Linhdanchu\Artisan\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServicePovider extends ServiceProvider
{
    protected $commands = [
        'Linhdanchu\Artisan\Commands\CreateRepository',
    ];

    public function boot(){

    }

    public function register(){
        $this->commands($this->commands);
    }
}