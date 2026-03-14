<?php

namespace App\Cats;

use App\Models\Service;
use Native\Laravel\Facades\ChildProcess;

class ServiceManager
{
    public function alias(Service $service): string
    {
        return "service-{$service->id}";
    }

    public function start(Service $service): void
    {
        $alias = $this->alias($service);

        if ($this->isRunning($service)) {
            return;
        }

        $logPath = $this->logPath($service);
        $logDir = dirname($logPath);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $escaped = addcslashes($service->command, "'");
        $cmd = "exec {$escaped} >> " . escapeshellarg($logPath) . " 2>&1";

        ChildProcess::start(
            cmd: ['sh', '-c', $cmd],
            alias: $alias,
            cwd: $service->application->path,
            env: $this->processEnv(),
        );
    }

    public function stop(Service $service): void
    {
        ChildProcess::stop($this->alias($service));
    }

    public function restart(Service $service): void
    {
        $this->stop($service);
        $this->start($service);
    }

    public function isRunning(Service $service): bool
    {
        try {
            return ChildProcess::get($this->alias($service)) !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function logPath(Service $service): string
    {
        return storage_path("app/logs/services/{$service->id}.log");
    }

    public function clearLog(Service $service): void
    {
        $path = $this->logPath($service);

        if (file_exists($path)) {
            file_put_contents($path, '');
        }
    }

    protected function processEnv(): array
    {
        $home = getenv('HOME') ?: posix_getpwuid(posix_getuid())['dir'];

        $extraPaths = array_filter([
            '/opt/homebrew/bin',
            '/opt/homebrew/sbin',
            '/usr/local/bin',
            $home . '/Library/Application Support/Herd/bin',
            $home . '/.config/herd-lite/bin',
            $home . '/.composer/vendor/bin',
        ], 'is_dir');

        $currentPath = getenv('PATH') ?: '/usr/bin:/bin:/usr/sbin:/sbin';

        return [
            'PATH' => implode(':', [...$extraPaths, $currentPath]),
            'HOME' => $home,
        ];
    }

    public function startAutoStartServices(): void
    {
        $services = Service::where('auto_start', true)->with('application')->get();

        foreach ($services as $service) {
            $this->start($service);
        }
    }
}
