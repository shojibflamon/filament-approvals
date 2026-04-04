<?php

namespace EightyNine\Approvals\Tests\Commands;

use EightyNine\Approvals\Commands\PublishCommand;
use EightyNine\Approvals\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class PublishCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Clean up any existing published files
        $this->cleanupPublishedFiles();
    }

    protected function tearDown(): void
    {
        // Clean up published files after each test
        $this->cleanupPublishedFiles();
        
        parent::tearDown();
    }

    /** @test */
    public function it_can_publish_config_files()
    {
        $configPath = config_path('approvals.php');
        
        // Ensure config doesn't exist
        if (File::exists($configPath)) {
            File::delete($configPath);
        }
        
        $this->artisan('approvals:publish', ['--config' => true])
            ->expectsOutput('📄 Config file published')
            ->expectsOutput('✅ Publishing completed successfully!')
            ->assertExitCode(0);
            
        $this->assertFileExists($configPath);
        
        // Verify config content has expected structure
        $config = include $configPath;
        $this->assertIsArray($config);
        $this->assertArrayHasKey('role_model', $config);
        $this->assertArrayHasKey('navigation', $config);
        $this->assertArrayHasKey('enable_approval_comments', $config);
    }

    /** @test */
    public function it_can_publish_view_files()
    {
        $viewsPath = resource_path('views/vendor/filament-approvals');
        
        $this->artisan('approvals:publish', ['--views' => true])
            ->expectsOutput('👀 View files published to resources/views/vendor/filament-approvals/')
            ->expectsOutput('✅ Publishing completed successfully!')
            ->assertExitCode(0);
            
        $this->assertDirectoryExists($viewsPath);
        $this->assertFileExists($viewsPath . '/tables/columns/approval-status-column.blade.php');
        $this->assertFileExists($viewsPath . '/tables/columns/approval-status-column-action-view.blade.php');
    }

    /** @test */
    public function it_can_publish_filament_resources()
    {
        $resourcesPath = app_path('Filament/Resources');
        
        $this->artisan('approvals:publish', ['--resources' => true])
            ->expectsOutput('🎯 Filament resources published to app/Filament/Resources/')
            ->expectsOutput('✅ Publishing completed successfully!')
            ->assertExitCode(0);
            
        // Check if ApprovalFlowResource was published
        $this->assertFileExists($resourcesPath . '/ApprovalFlowResource.php');
    }

    /** @test */
    public function it_can_publish_form_and_table_components()
    {
        $formsPath = app_path('Forms/Approvals');
        $tablesPath = app_path('Tables/Approvals');
        
        $this->artisan('approvals:publish', ['--components' => true])
            ->expectsOutput('🧩 Form and table components published to app/Forms/Approvals/ and app/Tables/Approvals/')
            ->expectsOutput('✅ Publishing completed successfully!')
            ->assertExitCode(0);
            
        $this->assertDirectoryExists($formsPath);
        $this->assertDirectoryExists($tablesPath);
    }

    /** @test */
    public function it_can_publish_translation_files()
    {
        $translationsPath = resource_path('lang/vendor/filament-approvals');
        
        $this->artisan('approvals:publish', ['--translations' => true])
            ->expectsOutput('🌐 Translation files published to resources/lang/vendor/filament-approvals/')
            ->expectsOutput('✅ Publishing completed successfully!')
            ->assertExitCode(0);
            
        $this->assertDirectoryExists($translationsPath);
        $this->assertFileExists($translationsPath . '/en/approvals.php');
    }

    /** @test */
    public function it_can_publish_stub_files()
    {
        $stubsPath = base_path('stubs/filament-approvals');
        
        $this->artisan('approvals:publish', ['--stubs' => true])
            ->expectsOutput('📝 Stub files published to stubs/filament-approvals/')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_can_publish_all_files_at_once()
    {
        $this->artisan('approvals:publish', ['--all' => true])
            ->expectsOutput('📦 Publishing all customizable files...')
            ->expectsOutput('📄 Config file published')
            ->expectsOutput('👀 View files published to resources/views/vendor/filament-approvals/')
            ->expectsOutput('🎯 Filament resources published to app/Filament/Resources/')
            ->expectsOutput('🧩 Form and table components published to app/Forms/Approvals/ and app/Tables/Approvals/')
            ->expectsOutput('🌐 Translation files published to resources/lang/vendor/filament-approvals/')
            ->expectsOutput('📝 Stub files published to stubs/filament-approvals/')
            ->assertExitCode(0);
            
        // Verify all files were published
        $this->assertFileExists(config_path('approvals.php'));
        $this->assertDirectoryExists(resource_path('views/vendor/filament-approvals'));
        $this->assertFileExists(app_path('Filament/Resources/ApprovalFlowResource.php'));
        $this->assertDirectoryExists(app_path('Forms/Approvals'));
        $this->assertDirectoryExists(app_path('Tables/Approvals'));
        $this->assertDirectoryExists(resource_path('lang/vendor/filament-approvals'));
    }

    /** @test */
    public function it_can_publish_multiple_specific_components()
    {
        $this->artisan('approvals:publish', [
            '--config' => true,
            '--views' => true,
        ])
            ->expectsOutput('📄 Config file published')
            ->expectsOutput('👀 View files published to resources/views/vendor/filament-approvals/')
            ->expectsOutput('✅ Publishing completed successfully!')
            ->assertExitCode(0);
            
        $this->assertFileExists(config_path('approvals.php'));
        $this->assertDirectoryExists(resource_path('views/vendor/filament-approvals'));
    }

    /** @test */
    public function it_shows_interactive_menu_when_no_options_provided()
    {
        $this->artisan('approvals:publish')
            ->expectsQuestion('Select what to publish:', 'Configuration file (config/approvals.php)')
            ->expectsOutput('📄 Config file published')
            ->expectsOutput('✅ Publishing completed successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_interactive_menu_selection_for_all()
    {
        $this->artisan('approvals:publish')
            ->expectsQuestion('Select what to publish:', 'All of the above')
            ->expectsOutput('📦 Publishing all customizable files...')
            ->expectsOutput('✅ Publishing completed successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_registers_command_in_service_provider()
    {
        $commands = Artisan::all();
        
        $this->assertArrayHasKey('approvals:publish', $commands);
        $this->assertInstanceOf(PublishCommand::class, $commands['approvals:publish']);
    }

    /** @test */
    public function it_has_correct_command_signature()
    {
        $command = new PublishCommand();
        
        $this->assertEquals('approvals:publish', $command->getName());
        $this->assertEquals('Publish Filament Approvals assets for customization', $command->getDescription());
    }

    /** @test */
    public function it_validates_command_options()
    {
        $command = new PublishCommand();
        $definition = $command->getDefinition();
        
        $this->assertTrue($definition->hasOption('config'));
        $this->assertTrue($definition->hasOption('views'));
        $this->assertTrue($definition->hasOption('resources'));
        $this->assertTrue($definition->hasOption('components'));
        $this->assertTrue($definition->hasOption('translations'));
        $this->assertTrue($definition->hasOption('stubs'));
        $this->assertTrue($definition->hasOption('all'));
    }

    /** @test */
    public function published_config_has_correct_structure()
    {
        $this->artisan('approvals:publish', ['--config' => true]);
        
        $config = include config_path('approvals.php');
        
        // Test main configuration keys
        $this->assertArrayHasKey('role_model', $config);
        $this->assertArrayHasKey('navigation', $config);
        $this->assertArrayHasKey('enable_approval_comments', $config);
        $this->assertArrayHasKey('enable_rejection_comments', $config);
        $this->assertArrayHasKey('ui', $config);
        $this->assertArrayHasKey('security', $config);
        $this->assertArrayHasKey('notifications', $config);
        
        // Test navigation structure
        $this->assertArrayHasKey('should_register_navigation', $config['navigation']);
        $this->assertArrayHasKey('icon', $config['navigation']);
        $this->assertArrayHasKey('sort', $config['navigation']);
        
        // Test UI customization options
        $this->assertArrayHasKey('show_approval_history', $config['ui']);
        $this->assertArrayHasKey('status_colors', $config['ui']);
        $this->assertIsArray($config['ui']['status_colors']);
        
        // Test security options
        $this->assertArrayHasKey('prevent_self_approval', $config['security']);
        $this->assertArrayHasKey('audit_approvals', $config['security']);
    }

    /** @test */
    public function published_views_contain_required_elements()
    {
        $this->artisan('approvals:publish', ['--views' => true]);
        
        $statusColumnPath = resource_path('views/vendor/filament-approvals/tables/columns/approval-status-column.blade.php');
        $actionViewPath = resource_path('views/vendor/filament-approvals/tables/columns/approval-status-column-action-view.blade.php');
        
        $this->assertFileExists($statusColumnPath);
        $this->assertFileExists($actionViewPath);
        
        $statusContent = File::get($statusColumnPath);
        $actionContent = File::get($actionViewPath);
        
        // Check for key elements in approval status column
        $this->assertStringContainsString('$getRecord()->approvalStatus', $statusContent);
        $this->assertStringContainsString('isApprovalCompleted()', $statusContent);
        
        // Check for key elements in action view
        $this->assertStringContainsString('$data as $', $actionContent);
        $this->assertStringContainsString('approval_action', $actionContent);
    }

    protected function cleanupPublishedFiles(): void
    {
        $paths = [
            config_path('approvals.php'),
            resource_path('views/vendor/filament-approvals'),
            app_path('Filament/Resources/ApprovalFlowResource.php'),
            app_path('Forms/Approvals'),
            app_path('Tables/Approvals'),
            resource_path('lang/vendor/filament-approvals'),
            base_path('stubs/filament-approvals'),
        ];
        
        foreach ($paths as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            } elseif (File::exists($path)) {
                File::delete($path);
            }
        }
    }
}
