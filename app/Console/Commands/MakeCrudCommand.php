<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCrudCommand extends Command
{
    protected $signature = 'make:crud {name} {--api : Generate API controller} {--migration : Generate migration}';

    protected $description = 'Generate CRUD resources (Model, Migration, Controller, Views, Routes)';

    protected $name;
    protected $modelName;
    protected $controllerName;
    protected $serviceName;
    protected $tableName;
    protected $routeName;

    public function handle()
    {
        $this->name = $this->argument('name');
        $this->modelName = Str::studly($this->name);
        $this->controllerName = $this->modelName . 'Controller';
        $this->serviceName = $this->modelName . 'Service';
        $this->tableName = Str::snake(Str::plural($this->name));
        $this->routeName = Str::kebab(Str::plural($this->name));

        $this->info("Generating CRUD for {$this->modelName}...");

        // Generate Model
        $this->generateModel();

        // Generate Migration
        if ($this->option('migration')) {
            $this->generateMigration();
        }

        // Generate Service
        $this->generateService();

        // Generate Controller
        $this->generateController();

        // Generate Views
        $this->generateViews();

        // Generate Routes
        $this->generateRoutes();

        $this->info("CRUD generated successfully!");
        $this->info("Route: /{$this->routeName}");
    }

    protected function generateModel()
    {
        $stub = File::get(__DIR__ . '/stubs/model.stub');
        $stub = str_replace('{{ModelName}}', $this->modelName, $stub);
        $stub = str_replace('{{tableName}}', $this->tableName, $stub);

        $path = app_path("Models/{$this->modelName}.php");
        File::put($path, $stub);
        $this->info("Model created: {$path}");
    }

    protected function generateMigration()
    {
        $this->call('make:migration', [
            'name' => "create_{$this->tableName}_table",
        ]);
    }

    protected function generateService()
    {
        $stub = File::get(__DIR__ . '/stubs/service.stub');
        $stub = str_replace('{{ServiceName}}', $this->serviceName, $stub);
        $stub = str_replace('{{ModelName}}', $this->modelName, $stub);

        $path = app_path("Services/{$this->serviceName}.php");
        File::put($path, $stub);
        $this->info("Service created: {$path}");
    }

    protected function generateController()
    {
        $stub = $this->option('api')
            ? File::get(__DIR__ . '/stubs/api-controller.stub')
            : File::get(__DIR__ . '/stubs/controller.stub');

        $stub = str_replace('{{ControllerName}}', $this->controllerName, $stub);
        $stub = str_replace('{{ModelName}}', $this->modelName, $stub);
        $stub = str_replace('{{ServiceName}}', $this->serviceName, $stub);
        $stub = str_replace('{{routeName}}', $this->routeName, $stub);
        $stub = str_replace('{{viewPath}}', "features.{$this->routeName}", $stub);

        $path = app_path("Http/Controllers/{$this->controllerName}.php");
        File::put($path, $stub);
        $this->info("Controller created: {$path}");
    }

    protected function generateViews()
    {
        $viewPath = resource_path("views/features/{$this->routeName}");
        File::makeDirectory($viewPath, 0755, true);
        File::makeDirectory("{$viewPath}/partials", 0755, true);

        // Index view
        $indexStub = File::get(__DIR__ . '/stubs/views/index.stub');
        $indexStub = str_replace('{{ModelName}}', $this->modelName, $indexStub);
        $indexStub = str_replace('{{routeName}}', $this->routeName, $indexStub);
        File::put("{$viewPath}/index.blade.php", $indexStub);

        // Form partial
        $formStub = File::get(__DIR__ . '/stubs/views/form.stub');
        $formStub = str_replace('{{routeName}}', $this->routeName, $formStub);
        File::put("{$viewPath}/partials/form.blade.php", $formStub);

        $this->info("Views created: {$viewPath}");
    }

    protected function generateRoutes()
    {
        $routeFile = base_path('routes/web.php');
        $routes = "\n// {$this->modelName} Routes\n";
        $routes .= "Route::resource('{$this->routeName}', App\Http\Controllers\\{$this->controllerName}::class);\n";

        File::append($routeFile, $routes);
        $this->info("Routes added to web.php");
    }
}
