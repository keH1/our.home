<?php

namespace App\Providers;

use App\Attributes\RpcProcedure;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use Sajya\Server\Procedure;

class RpcServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        $this->app->bind('rpc.procedures', function () {
            return $this->discoverProceduresFlat();
        });

        $this->app->bind('rpc.procedures.structured', function () {
            return $this->discoverProcedures();
        });
    }

    /**
     * @throws \ReflectionException
     */
    private function discoverProcedures(): array
    {
        $procedures = [];
        $classMap = ClassMapGenerator::createMap(app_path('Http/Procedures'));

        foreach ($classMap as $class => $path) {
            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(RpcProcedure::class);

            if (!empty($attributes) && $reflection->isSubclassOf(Procedure::class) && !$reflection->isAbstract()) {
                $attr = $attributes[0]->newInstance();
                $procedures[$attr->version][$attr->group][] = $class;
            }
        }

        return $procedures;
    }

    /**
     * @throws \ReflectionException
     */
    private function discoverProceduresFlat(): array
    {
        $structuredProcedures = $this->discoverProcedures();
        $flatProcedures = [];

        foreach ($structuredProcedures as $version => $groups) {
            foreach ($groups as $group => $procedures) {
                foreach ($procedures as $procedure) {
                    $flatProcedures[] = $procedure;
                }
            }
        }

        return $flatProcedures;
    }
}
