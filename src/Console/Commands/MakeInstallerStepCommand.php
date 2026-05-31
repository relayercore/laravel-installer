<?php

namespace RelayerCore\LaravelInstaller\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeInstallerStepCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:installer-step';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new installer step class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Installer Step';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../../stubs/installer-step.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Installer\Steps';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        // Calculate a default ID from the class name (e.g., SelectPlan -> plan)
        $className = class_basename($name);
        $id = Str::kebab(str_replace('Step', '', $className));
        
        return str_replace('{{ id }}', $id, $stub);
    }
}
