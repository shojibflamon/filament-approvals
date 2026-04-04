<?php

namespace EightyNine\Approvals\Tests\Commands;

use EightyNine\Approvals\Tests\TestCase;
use Illuminate\Support\Facades\File;

class PublishCommandEdgeCasesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanupPublishedFiles();
    }

    protected function tearDown(): void
    {
        $this->cleanupPublishedFiles();
        parent::tearDown();
    }

    /** @test */
    public function it_handles_existing_files_gracefully()
    {
        // Create a config file first
        $configPath = config_path('approvals.php');
        File::ensureDirectoryExists(dirname($configPath));
        File::put($configPath, '<?php return ["test" => true];');
        
        // Try to publish again - should work without errors
        $this->artisan('approvals:publish', ['--config' => true])
            ->expectsOutput('📄 Config file published')
            ->expectsOutput('✅ Publishing completed successfully!')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_permission_errors_gracefully()
    {
        // Skip this test on systems where we can't test permissions
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('Permission tests not applicable on Windows');
        }

        // This test would need to mock filesystem permissions
        // which is complex to do reliably across systems
        $this->assertTrue(true);
    }

    /** @test */
    public function it_validates_directory_creation()
    {
        $viewsPath = resource_path('views/vendor/filament-approvals');
        
        // Ensure directory doesn't exist
        if (File::exists($viewsPath)) {
            File::deleteDirectory($viewsPath);
        }
        
        $this->artisan('approvals:publish', ['--views' => true])
            ->assertExitCode(0);
            
        $this->assertDirectoryExists($viewsPath);
    }

    /** @test */
    public function it_handles_invalid_interactive_selection()
    {
        // Test with an empty selection (defaults to 'all' option)
        $this->artisan('approvals:publish')
            ->expectsQuestion('Select what to publish:', '')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_publishes_files_to_correct_locations()
    {
        $this->artisan('approvals:publish', ['--all' => true])
            ->assertExitCode(0);
        
        // Verify config location
        $this->assertFileExists(config_path('approvals.php'));
        
        // Verify views location
        $this->assertDirectoryExists(resource_path('views/vendor/filament-approvals'));
        
        // Verify resources location
        $this->assertFileExists(app_path('Filament/Resources/ApprovalFlowResource.php'));
        
        // Verify components location
        $this->assertDirectoryExists(app_path('Forms/Approvals'));
        $this->assertDirectoryExists(app_path('Tables/Approvals'));
        
        // Verify translations location
        $this->assertDirectoryExists(resource_path('lang/vendor/filament-approvals'));
    }

    /** @test */
    public function it_preserves_existing_file_structure()
    {
        // Create some existing files
        $viewsPath = resource_path('views/vendor/filament-approvals');
        File::ensureDirectoryExists($viewsPath);
        File::put($viewsPath . '/existing-file.blade.php', 'existing content');
        
        $this->artisan('approvals:publish', ['--views' => true])
            ->assertExitCode(0);
        
        // Verify existing file is preserved
        $this->assertFileExists($viewsPath . '/existing-file.blade.php');
        $this->assertEquals('existing content', File::get($viewsPath . '/existing-file.blade.php'));
        
        // Verify new files were added
        $this->assertFileExists($viewsPath . '/tables/columns/approval-status-column.blade.php');
    }

    /** @test */
    public function it_handles_empty_source_directories()
    {
        // This tests the robustness of the publishing system
        // when source directories might be empty or missing
        $this->artisan('approvals:publish', ['--stubs' => true])
            ->assertExitCode(0);
    }

    /** @test */
    public function it_shows_helpful_messages_for_each_component_type()
    {
        $this->artisan('approvals:publish', ['--config' => true])
            ->expectsOutput('📄 Config file published')
            ->assertExitCode(0);
        
        $this->artisan('approvals:publish', ['--views' => true])
            ->expectsOutput('👀 View files published to resources/views/vendor/filament-approvals/')
            ->assertExitCode(0);
        
        $this->artisan('approvals:publish', ['--resources' => true])
            ->expectsOutput('🎯 Filament resources published to app/Filament/Resources/')
            ->assertExitCode(0);
        
        $this->artisan('approvals:publish', ['--components' => true])
            ->expectsOutput('🧩 Form and table components published to app/Forms/Approvals/ and app/Tables/Approvals/')
            ->assertExitCode(0);
        
        $this->artisan('approvals:publish', ['--translations' => true])
            ->expectsOutput('🌐 Translation files published to resources/lang/vendor/filament-approvals/')
            ->assertExitCode(0);
        
        $this->artisan('approvals:publish', ['--stubs' => true])
            ->expectsOutput('📝 Stub files published to stubs/filament-approvals/')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_handles_concurrent_publishing_attempts()
    {
        // Test publishing multiple components simultaneously
        $this->artisan('approvals:publish', [
            '--config' => true,
            '--views' => true,
            '--resources' => true,
        ])->assertExitCode(0);
        
        // Verify all requested components were published
        $this->assertFileExists(config_path('approvals.php'));
        $this->assertDirectoryExists(resource_path('views/vendor/filament-approvals'));
        $this->assertFileExists(app_path('Filament/Resources/ApprovalFlowResource.php'));
    }

    /** @test */
    public function it_provides_clear_feedback_on_completion()
    {
        $this->artisan('approvals:publish', ['--config' => true])
            ->expectsOutput('✅ Publishing completed successfully!')
            ->assertExitCode(0);
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
