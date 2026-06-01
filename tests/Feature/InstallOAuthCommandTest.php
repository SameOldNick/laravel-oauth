<?php

namespace SameOldNick\OAuth\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use SameOldNick\OAuth\Tests\TestCase;

class InstallOAuthCommandTest extends TestCase
{
    protected Filesystem $files;

    protected string $providersFile;

    protected string $providersBackup;

    protected string $defaultOAuthPath;

    protected string $providerPath;

    protected string $customOutputPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem;
        $this->providersFile = base_path('bootstrap/providers.php');
        $this->providersBackup = $this->files->get($this->providersFile);
        $this->defaultOAuthPath = app_path('OAuth');
        $this->providerPath = app_path('Providers/OAuthServiceProvider.php');
        $this->customOutputPath = base_path('storage/framework/testing/oauth-install');

        $this->resetGeneratedFiles();
        $this->resetProvidersFile();
    }

    protected function tearDown(): void
    {
        $this->resetGeneratedFiles();
        $this->files->put($this->providersFile, $this->providersBackup);

        parent::tearDown();
    }

    public function test_install_command_generates_fortify_stack_and_registers_provider(): void
    {
        $this->artisan('oauth:install', [
            '--no-interaction' => true,
            '--force' => true,
            '--stack' => 'fortify',
        ])->assertExitCode(0);

        $servicePath = app_path('OAuth/Fortify/Services/OAuthAccountAssociator.php');

        $this->assertFileExists($servicePath);
        $this->assertStringContainsString(
            'namespace App\\OAuth\\Fortify\\Services;',
            $this->files->get($servicePath)
        );

        $this->assertFileExists($this->providerPath);
        $this->assertStringContainsString(
            'App\\Providers\\OAuthServiceProvider::class',
            $this->files->get($this->providersFile)
        );
    }

    public function test_install_command_honors_skip_provider_and_skip_registration_options(): void
    {
        $this->artisan('oauth:install', [
            '--no-interaction' => true,
            '--force' => true,
            '--stack' => 'fortify',
            '--skip-provider' => true,
            '--skip-registration' => true,
        ])->assertExitCode(0);

        $this->assertFileExists(app_path('OAuth/Fortify/Services/OAuthAccountAssociator.php'));
        $this->assertFileDoesNotExist($this->providerPath);
        $this->assertStringNotContainsString(
            'App\\Providers\\OAuthServiceProvider::class',
            $this->files->get($this->providersFile)
        );
    }

    public function test_install_command_supports_custom_stack_namespace_and_path(): void
    {
        $this->artisan('oauth:install', [
            '--no-interaction' => true,
            '--force' => true,
            '--stack' => 'custom',
            '--path' => $this->customOutputPath,
            '--app-namespace' => 'Workbench\\App',
            '--skip-provider' => true,
            '--skip-registration' => true,
        ])->assertExitCode(0);

        $servicePath = $this->customOutputPath.'/Custom/Services/OAuthAccountAssociator.php';

        $this->assertFileExists($servicePath);
        $this->assertStringContainsString(
            'namespace Workbench\\App\\OAuth\\Custom\\Services;',
            $this->files->get($servicePath)
        );

        $this->assertFileDoesNotExist($this->providerPath);
    }

    public function test_install_command_does_not_duplicate_provider_registration(): void
    {
        $this->artisan('oauth:install', [
            '--no-interaction' => true,
            '--force' => true,
            '--stack' => 'fortify',
        ])->assertExitCode(0);

        $this->artisan('oauth:install', [
            '--no-interaction' => true,
            '--stack' => 'fortify',
        ])->assertExitCode(0);

        $contents = $this->files->get($this->providersFile);

        $this->assertSame(1, substr_count($contents, 'App\\Providers\\OAuthServiceProvider::class'));
    }

    protected function resetGeneratedFiles(): void
    {
        $this->files->deleteDirectory($this->defaultOAuthPath);
        $this->files->deleteDirectory($this->customOutputPath);
        $this->files->delete($this->providerPath);
    }

    protected function resetProvidersFile(): void
    {
        $contents = $this->providersBackup;
        $contents = preg_replace('/^\s*App\\\\Providers\\\\OAuthServiceProvider::class,\R/m', '', $contents) ?? $contents;

        $this->files->put($this->providersFile, $contents);
    }
}
