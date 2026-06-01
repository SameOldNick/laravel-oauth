<?php

namespace SameOldNick\OAuth\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

class InstallOAuth extends Command
{
    public $signature = 'oauth:install
                        {--force : Overwrite existing files}
                        {--path= : Destination path for generated OAuth classes}
                        {--app-namespace= : Root namespace for generated classes (defaults to application namespace)}
                        {--skip-provider : Skip generation of service provider that binds interfaces to implementations}
                        {--skip-registration : Skip auto-registration of generated service provider in bootstrap/providers.php}
                        {--stack=fortify : Authentication stack preset to scaffold}';

    public $description = 'Scaffold app-level OAuth services and responses so each app can own its implementation.';

    public function __construct(
        protected readonly Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $stack = strtolower((string) $this->option('stack'));

        if (! in_array($stack, ['fortify', 'custom'], true)) {
            throw new InvalidArgumentException("Unsupported stack [{$stack}]. Supported stacks: fortify, custom.");
        }

        $force = (bool) $this->option('force');

        $this->info('Scaffolding OAuth app classes...');

        $generated = 0;
        $skipped = 0;

        foreach ($this->sourceToDestinationMap($stack) as $source => $destination) {
            $result = $this->copyClassFile($source, $destination, $force);

            if ($result === 'generated') {
                $generated++;
            }

            if ($result === 'skipped') {
                $skipped++;
            }
        }

        if (! $this->option('skip-provider')) {
            $providerPath = app_path('Providers/OAuthServiceProvider.php');
            $providerResult = $this->createBindingsProvider($providerPath, $force);

            if ($providerResult === 'generated') {
                $generated++;
            } elseif ($providerResult === 'skipped') {
                $skipped++;
            }
        } else {
            $this->warn('Skipped generation of service provider that binds interfaces to implementations. Remember to create App\\Providers\\OAuthServiceProvider and bind the necessary interfaces to your implementations.');
        }

        $this->newLine();
        $this->info("Generated {$generated} file(s). Skipped {$skipped} existing file(s).\n");

        if (! $this->option('skip-registration')) {
            $this->info('Registering service provider...');

            $providerRegistered = $this->registerProviderInBootstrap();

            if ($providerRegistered) {
                $this->info('Registered App\\Providers\\OAuthServiceProvider in bootstrap/providers.php.');
            } else {
                $this->warn('Could not auto-register provider. Add App\\Providers\\OAuthServiceProvider::class to bootstrap/providers.php manually.');
            }
        } else {
            $this->warn('Skipped auto-registration of service provider in bootstrap/providers.php. Remember to register App\\Providers\\OAuthServiceProvider manually.');
        }

        $this->line('Next steps: customize generated classes under app/OAuth and run your test suite.');

        return self::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    protected function sourceToDestinationMap(string $stack): array
    {
        $baseDestination = $this->resolveDestinationPath((string) $this->option('path'));

        return match ($stack) {
            'fortify' => $this->fortifySourceToDestinationMap($baseDestination),
            'custom' => $this->customSourceToDestinationMap($baseDestination),
            default => [],
        };
    }

    protected function fortifySourceToDestinationMap(string $baseDestination): array
    {
        $baseSource = $this->relativePath($this->resolveStubsRoot().'/stacks/fortify');
        $stackDestination = rtrim($baseDestination, '/\\').'/Fortify';

        return [
            $baseSource.'/Services/OAuthAccountAssociator.php' => $stackDestination.'/Services/OAuthAccountAssociator.php',
            $baseSource.'/Services/OAuthAuthenticationState.php' => $stackDestination.'/Services/OAuthAuthenticationState.php',
            $baseSource.'/Services/OAuthGate.php' => $stackDestination.'/Services/OAuthGate.php',
            $baseSource.'/Services/OAuthUserRegistrar.php' => $stackDestination.'/Services/OAuthUserRegistrar.php',
            $baseSource.'/Services/OAuthUserResolver.php' => $stackDestination.'/Services/OAuthUserResolver.php',
            $baseSource.'/Responses/AuthenticateResponse.php' => $stackDestination.'/Responses/AuthenticateResponse.php',
            $baseSource.'/Responses/LoggedInResponse.php' => $stackDestination.'/Responses/LoggedInResponse.php',
            $baseSource.'/Responses/Errors/AlreadyLinkedErrorResponse.php' => $stackDestination.'/Responses/Errors/AlreadyLinkedErrorResponse.php',
            $baseSource.'/Responses/Errors/CannotLinkResponse.php' => $stackDestination.'/Responses/Errors/CannotLinkResponse.php',
            $baseSource.'/Responses/Errors/LoginNotAllowedResponse.php' => $stackDestination.'/Responses/Errors/LoginNotAllowedResponse.php',
            $baseSource.'/Responses/Errors/MustLoginToLinkResponse.php' => $stackDestination.'/Responses/Errors/MustLoginToLinkResponse.php',
            $baseSource.'/Responses/Errors/RegistrationNotAllowedResponse.php' => $stackDestination.'/Responses/Errors/RegistrationNotAllowedResponse.php',
            $baseSource.'/Responses/Errors/UserTrashedResponse.php' => $stackDestination.'/Responses/Errors/UserTrashedResponse.php',
        ];
    }

    protected function customSourceToDestinationMap(string $baseDestination): array
    {
        $baseSource = $this->relativePath($this->resolveStubsRoot().'/stacks/custom');
        $stackDestination = rtrim($baseDestination, '/\\').'/Custom';

        return [
            $baseSource.'/Services/OAuthAccountAssociator.php' => $stackDestination.'/Services/OAuthAccountAssociator.php',
            $baseSource.'/Services/OAuthAuthenticationState.php' => $stackDestination.'/Services/OAuthAuthenticationState.php',
            $baseSource.'/Services/OAuthGate.php' => $stackDestination.'/Services/OAuthGate.php',
            $baseSource.'/Services/OAuthUserRegistrar.php' => $stackDestination.'/Services/OAuthUserRegistrar.php',
            $baseSource.'/Services/OAuthUserResolver.php' => $stackDestination.'/Services/OAuthUserResolver.php',
            $baseSource.'/Responses/AuthenticateResponse.php' => $stackDestination.'/Responses/AuthenticateResponse.php',
            $baseSource.'/Responses/LoggedInResponse.php' => $stackDestination.'/Responses/LoggedInResponse.php',
            $baseSource.'/Responses/Errors/AlreadyLinkedErrorResponse.php' => $stackDestination.'/Responses/Errors/AlreadyLinkedErrorResponse.php',
            $baseSource.'/Responses/Errors/CannotLinkResponse.php' => $stackDestination.'/Responses/Errors/CannotLinkResponse.php',
            $baseSource.'/Responses/Errors/LoginNotAllowedResponse.php' => $stackDestination.'/Responses/Errors/LoginNotAllowedResponse.php',
            $baseSource.'/Responses/Errors/MustLoginToLinkResponse.php' => $stackDestination.'/Responses/Errors/MustLoginToLinkResponse.php',
            $baseSource.'/Responses/Errors/RegistrationNotAllowedResponse.php' => $stackDestination.'/Responses/Errors/RegistrationNotAllowedResponse.php',
            $baseSource.'/Responses/Errors/UserTrashedResponse.php' => $stackDestination.'/Responses/Errors/UserTrashedResponse.php',
        ];
    }

    protected function resolveStubsRoot(): string
    {
        return dirname(__DIR__).'/../resources/stubs';
    }

    protected function resolveDestinationPath(string $path): string
    {
        $trimmed = trim($path);

        if ($trimmed === '') {
            return app_path('OAuth');
        }

        if ($this->isAbsolutePath($trimmed)) {
            return $trimmed;
        }

        return base_path($trimmed);
    }

    protected function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        // Windows drive path (C:\foo), UNC path (\\server\share), or POSIX absolute path (/foo).
        return (bool) preg_match('/^(?:[A-Za-z]:[\\\\\/]|\\\\\\\\|\/)/', $path);
    }

    protected function copyClassFile(string $source, string $destination, bool $force): string
    {
        if (! $this->files->exists($source)) {
            throw new InvalidArgumentException("Unable to scaffold missing source file [{$source}].");
        }

        if ($this->files->exists($destination) && ! $force) {
            if (! $this->promptForOverwrite($this->relativePath($destination))) {
                $this->line("  <fg=yellow>SKIP</> {$this->relativePath($destination)}");

                return 'skipped';
            }
        }

        $this->files->ensureDirectoryExists(dirname($destination));

        $content = $this->files->get($source);
        $content = $this->transformForApplication($content);

        $this->files->put($destination, $content);

        $this->line("  <fg=green>DONE</> {$this->relativePath($destination)}");

        return 'generated';
    }

    protected function promptForOverwrite(string $filePath): bool
    {
        if ($this->option('no-interaction')) {
            return false;
        }

        return $this->confirm("File already exists at [{$filePath}]. Do you want to overwrite it?", false);
    }

    protected function transformForApplication(string $content): string
    {
        return str_replace(
            [
                'VendorName\\',
                'OAuth\\Fortify\\',
            ],
            [
                $this->option('app-namespace') ? $this->option('app-namespace').'\\' : app()->getNamespace(),
                'OAuth\\'.$this->stackStudly().'\\',
            ],
            $content
        );
    }

    protected function stackStudly(): string
    {
        $stack = strtolower((string) $this->option('stack'));

        return ucfirst($stack);
    }

    protected function createBindingsProvider(string $destination, bool $force): string
    {
        if ($this->files->exists($destination) && ! $force) {
            if (! $this->promptForOverwrite($this->relativePath($destination))) {
                $this->line("  <fg=yellow>SKIP</> {$this->relativePath($destination)}");

                return 'skipped';
            }
        }

        $source = $this->getSourceServiceProviderPath();
        $content = $this->files->get($source);
        $content = $this->transformForApplication($content);

        $this->files->ensureDirectoryExists(dirname($destination));
        $this->files->put($destination, $content);

        $this->line("  <fg=green>DONE</> {$this->relativePath($destination)}");

        return 'generated';
    }

    protected function getSourceServiceProviderPath(): string
    {
        return $this->resolveStubsRoot().'/shared/Providers/OAuthServiceProvider.php';
    }

    protected function registerProviderInBootstrap(): bool
    {
        $providersFile = base_path('bootstrap/providers.php');

        if (! $this->files->exists($providersFile)) {
            return false;
        }

        $providerClass = 'App\\Providers\\OAuthServiceProvider::class';
        $contents = $this->files->get($providersFile);

        if (str_contains($contents, $providerClass)) {
            return true;
        }

        $closingBracketPosition = strrpos($contents, '];');

        if ($closingBracketPosition === false) {
            return false;
        }

        $insertion = "    {$providerClass},\n";

        $updated = substr($contents, 0, $closingBracketPosition)
            .$insertion
            .substr($contents, $closingBracketPosition);

        $this->files->put($providersFile, $updated);

        return true;
    }

    protected function relativePath(string $path): string
    {
        $base = rtrim(base_path(), '\\/').DIRECTORY_SEPARATOR;

        return str_replace('\\', '/', ltrim(str_replace($base, '', $path), '\\/'));
    }
}
