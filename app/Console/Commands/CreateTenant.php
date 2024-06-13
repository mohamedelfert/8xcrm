<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\Artisan;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {name}';
    protected $description = 'Create a new tenant';

    public function handle()
    {
        $name = $this->argument('name');

        if (empty($name)) {
            $this->error('Tenant name is required');
            return;
        }

        // Create a new website instance
        $website = new Website;
        app(WebsiteRepository::class)->create($website);

        // Associate the website with a hostname
        $hostname = new Hostname;
        $baseUrl = config('app.url_base');
        $hostname->fqdn = "{$name}.{$baseUrl}";
        app(HostnameRepository::class)->attach($hostname, $website);

        // Make the hostname current
        app(Environment::class)->tenant($website);

        // Run the Laravel Passport install command for the tenant
        Artisan::call('passport:install');

        $this->info("Tenant created successfully with FQDN: {$hostname->fqdn}");

        return $hostname;
    }
}
