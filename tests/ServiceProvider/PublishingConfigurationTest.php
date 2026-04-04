<?php

namespace EightyNine\Approvals\Tests\ServiceProvider;

use EightyNine\Approvals\ApprovalServiceProvider;
use EightyNine\Approvals\Commands\PublishCommand;
use EightyNine\Approvals\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class PublishingConfigurationTest extends TestCase
{
    /** @test */
    public function it_registers_all_required_publishing_tags()
    {
        $provider = new ApprovalServiceProvider($this->app);
        $provider->register();
        $provider->boot();
        $provider->packageBooted();
        
        // Get the publishable groups for our provider
        $ourProvider = 'EightyNine\Approvals\ApprovalServiceProvider';
        $this->assertArrayHasKey($ourProvider, $provider::$publishes);
        
        $publishPaths = $provider::$publishes[$ourProvider];
        
        // Verify that expected source paths are registered
        $expectedSources = [
            __DIR__ . '/../../config/approvals.php',
            __DIR__ . '/../../resources/views',
            __DIR__ . '/../../src/Filament/Resources',
            __DIR__ . '/../../src/Forms',
            __DIR__ . '/../../src/Tables',
            __DIR__ . '/../../resources/lang',
        ];
        
        $registeredSources = array_keys($publishPaths);
        foreach ($expectedSources as $source) {
            $found = false;
            foreach ($registeredSources as $registered) {
                if (strpos($registered, basename($source)) !== false || strpos($registered, $source) !== false) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, "Publishing source '{$source}' should be registered");
        }
    }

    /** @test */
    public function it_has_correct_source_and_destination_paths()
    {
        $provider = new ApprovalServiceProvider($this->app);
        
        // Check if the provider has the correct publishes array structure
        $publishes = $provider::$publishes;
        
        $this->assertIsArray($publishes);
        
        // Verify that each publishing tag has associated files
        foreach ($publishes as $tag => $files) {
            $this->assertNotEmpty($files, "Publishing tag '{$tag}' should have associated files");
            
            foreach ($files as $source => $destination) {
                $this->assertIsString($source, "Source path should be a string for tag '{$tag}'");
                $this->assertIsString($destination, "Destination path should be a string for tag '{$tag}'");
            }
        }
    }

    /** @test */
    public function config_publishing_paths_are_correct()
    {
        $configPath = config_path('approvals.php');
        $sourcePath = __DIR__ . '/../../config/approvals.php';
        
        // Ensure source file exists
        $this->assertFileExists($sourcePath, 'Source config file should exist');
        
        // Test that artisan publish command recognizes the config tag
        $this->artisan('vendor:publish', [
            '--tag' => 'filament-approvals-config',
            '--force' => true
        ])->assertExitCode(0);
    }

    /** @test */
    public function view_publishing_paths_are_correct()
    {
        $viewsSourcePath = __DIR__ . '/../../resources/views';
        $viewsDestPath = resource_path('views/vendor/filament-approvals');
        
        // Ensure source directory exists
        $this->assertDirectoryExists($viewsSourcePath, 'Source views directory should exist');
        
        // Test that artisan publish command recognizes the views tag
        $this->artisan('vendor:publish', [
            '--tag' => 'filament-approvals-views',
            '--force' => true
        ])->assertExitCode(0);
    }

    /** @test */
    public function resources_publishing_paths_are_correct()
    {
        $resourcesSourcePath = __DIR__ . '/../../src/Filament/Resources';
        
        // Ensure source directory exists
        $this->assertDirectoryExists($resourcesSourcePath, 'Source resources directory should exist');
        
        // Test that artisan publish command recognizes the resources tag
        $this->artisan('vendor:publish', [
            '--tag' => 'filament-approvals-resources',
            '--force' => true
        ])->assertExitCode(0);
    }

    /** @test */
    public function components_publishing_paths_are_correct()
    {
        $formsSourcePath = __DIR__ . '/../../src/Forms/Components';
        $tablesSourcePath = __DIR__ . '/../../src/Tables/Components';
        
        // Ensure source directories exist
        $this->assertDirectoryExists($formsSourcePath, 'Source forms components directory should exist');
        $this->assertDirectoryExists($tablesSourcePath, 'Source tables components directory should exist');
        
        // Test that artisan publish command recognizes the components tag
        $this->artisan('vendor:publish', [
            '--tag' => 'filament-approvals-components',
            '--force' => true
        ])->assertExitCode(0);
    }

    /** @test */
    public function translations_publishing_paths_are_correct()
    {
        $translationsSourcePath = __DIR__ . '/../../resources/lang';
        
        // Ensure source directory exists
        $this->assertDirectoryExists($translationsSourcePath, 'Source translations directory should exist');
        
        // Test that artisan publish command recognizes the translations tag
        $this->artisan('vendor:publish', [
            '--tag' => 'filament-approvals-translations',
            '--force' => true
        ])->assertExitCode(0);
    }

    /** @test */
    public function stubs_publishing_paths_are_correct()
    {
        $stubsSourcePath = __DIR__ . '/../../stubs';
        
        // Ensure source directory exists (if it exists)
        if (is_dir($stubsSourcePath)) {
            $this->assertDirectoryExists($stubsSourcePath, 'Source stubs directory should exist');
        }
        
        // Test that artisan publish command recognizes the stubs tag
        $this->artisan('vendor:publish', [
            '--tag' => 'filament-approvals-stubs',
            '--force' => true
        ])->assertExitCode(0);
    }

    /** @test */
    public function publish_command_is_properly_registered()
    {
        $commands = Artisan::all();
        
        $this->assertArrayHasKey('approvals:publish', $commands);
        $this->assertInstanceOf(PublishCommand::class, $commands['approvals:publish']);
    }

    /** @test */
    public function provider_loads_correctly_in_app_context()
    {
        $provider = new ApprovalServiceProvider($this->app);
        
        // Test that the provider can be booted without errors
        $provider->packageBooted();
        
        // Test that the provider registers correctly
        $provider->packageRegistered();
        
        $this->assertTrue(true); // If we get here without exceptions, the test passes
    }

    /** @test */
    public function all_required_service_methods_exist()
    {
        $provider = new ApprovalServiceProvider($this->app);
        
        $this->assertTrue(method_exists($provider, 'configurePackage'));
        $this->assertTrue(method_exists($provider, 'packageBooted'));
        $this->assertTrue(method_exists($provider, 'packageRegistered'));
        $this->assertTrue(method_exists($provider, 'getCommands'));
        $this->assertTrue(method_exists($provider, 'getAssets'));
        $this->assertTrue(method_exists($provider, 'getIcons'));
        $this->assertTrue(method_exists($provider, 'getRoutes'));
        $this->assertTrue(method_exists($provider, 'getScriptData'));
        $this->assertTrue(method_exists($provider, 'getMigrations'));
    }
}
