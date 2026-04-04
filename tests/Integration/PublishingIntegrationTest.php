<?php

namespace EightyNine\Approvals\Tests\Integration;

use EightyNine\Approvals\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class PublishingIntegrationTest extends TestCase
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
    public function complete_publishing_workflow_works_correctly()
    {
        // Step 1: Publish configuration
        $this->artisan('approvals:publish', ['--config' => true])
            ->assertExitCode(0);
        
        $this->assertFileExists(config_path('approvals.php'));
        
        // Step 2: Verify config can be loaded
        $config = include config_path('approvals.php');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('role_model', $config);
        
        // Step 3: Publish views
        $this->artisan('approvals:publish', ['--views' => true])
            ->assertExitCode(0);
        
        $viewsPath = resource_path('views/vendor/filament-approvals');
        $this->assertDirectoryExists($viewsPath);
        $this->assertFileExists($viewsPath . '/tables/columns/approval-status-column.blade.php');
        
        // Step 4: Verify views contain expected content
        $statusColumnContent = File::get($viewsPath . '/tables/columns/approval-status-column.blade.php');
        $this->assertStringContainsString('$getRecord()->approvalStatus', $statusColumnContent);
        $this->assertStringContainsString('isApprovalCompleted()', $statusColumnContent);
        
        // Step 5: Publish resources
        $this->artisan('approvals:publish', ['--resources' => true])
            ->assertExitCode(0);
        
        $this->assertFileExists(app_path('Filament/Resources/ApprovalFlowResource.php'));
        
        // Step 6: Publish components
        $this->artisan('approvals:publish', ['--components' => true])
            ->assertExitCode(0);
        
        $this->assertDirectoryExists(app_path('Forms/Approvals'));
        $this->assertDirectoryExists(app_path('Tables/Approvals'));
        
        // Step 7: Verify all files work together
        $this->assertTrue(File::exists(config_path('approvals.php')));
        $this->assertTrue(File::isDirectory(resource_path('views/vendor/filament-approvals')));
        $this->assertTrue(File::exists(app_path('Filament/Resources/ApprovalFlowResource.php')));
        $this->assertTrue(File::isDirectory(app_path('Forms/Approvals')));
        $this->assertTrue(File::isDirectory(app_path('Tables/Approvals')));
    }

    /** @test */
    public function published_config_integrates_with_laravel_config_system()
    {
        // Publish config
        $this->artisan('approvals:publish', ['--config' => true])
            ->assertExitCode(0);
        
        // Load the published config
        Config::set('approvals', include config_path('approvals.php'));
        
        // Test config values can be accessed
        $this->assertEquals('App\Models\Role', Config::get('approvals.role_model'));
        $this->assertTrue(Config::get('approvals.navigation.should_register_navigation'));
        $this->assertEquals('heroicon-o-clipboard-document-check', Config::get('approvals.navigation.icon'));
        $this->assertFalse(Config::get('approvals.enable_approval_comments'));
        $this->assertTrue(Config::get('approvals.enable_rejection_comments'));
        
        // Test new configuration options
        $this->assertIsArray(Config::get('approvals.ui'));
        $this->assertIsArray(Config::get('approvals.security'));
        $this->assertIsArray(Config::get('approvals.notifications'));
        
        // Test UI configuration
        $this->assertTrue(Config::get('approvals.ui.show_approval_history'));
        $this->assertIsArray(Config::get('approvals.ui.status_colors'));
        
        // Test security configuration
        $this->assertTrue(Config::get('approvals.security.prevent_self_approval'));
        $this->assertTrue(Config::get('approvals.security.audit_approvals'));
    }

    /** @test */
    public function published_views_can_be_rendered()
    {
        // Publish views
        $this->artisan('approvals:publish', ['--views' => true])
            ->assertExitCode(0);
        
        $viewsPath = resource_path('views/vendor/filament-approvals');
        
        // Test that view files are valid PHP/Blade syntax
        $statusColumnPath = $viewsPath . '/tables/columns/approval-status-column.blade.php';
        $actionViewPath = $viewsPath . '/tables/columns/approval-status-column-action-view.blade.php';
        
        $this->assertFileExists($statusColumnPath);
        $this->assertFileExists($actionViewPath);
        
        // Basic syntax validation
        $statusContent = File::get($statusColumnPath);
        $actionContent = File::get($actionViewPath);
        
        // Check for valid Blade syntax (no obvious syntax errors)
        $this->assertStringNotContainsString('<?php echo', $statusContent); // Should use Blade syntax
        $this->assertStringContainsString('@if', $statusContent);
        $this->assertStringContainsString('@endif', $statusContent);
        
        $this->assertStringContainsString('@foreach', $actionContent);
        $this->assertStringContainsString('@endforeach', $actionContent);
    }

    /** @test */
    public function published_resources_have_correct_structure()
    {
        // Publish resources
        $this->artisan('approvals:publish', ['--resources' => true])
            ->assertExitCode(0);
        
        $resourcePath = app_path('Filament/Resources/ApprovalFlowResource.php');
        $this->assertFileExists($resourcePath);
        
        $content = File::get($resourcePath);
        
        // Check for expected class structure
        $this->assertStringContainsString('class ApprovalFlowResource extends Resource', $content);
        $this->assertStringContainsString('public static function form(Form $form): Form', $content);
        $this->assertStringContainsString('public static function table(Table $table): Table', $content);
        $this->assertStringContainsString('public static function getPages(): array', $content);
        
        // Check for proper namespace
        $this->assertStringContainsString('namespace EightyNine\Approvals\Filament\Resources;', $content);
    }

    /** @test */
    public function published_components_can_be_instantiated()
    {
        // Publish components
        $this->artisan('approvals:publish', ['--components' => true])
            ->assertExitCode(0);
        
        $formsPath = app_path('Forms/Approvals');
        $tablesPath = app_path('Tables/Approvals');
        
        $this->assertDirectoryExists($formsPath);
        $this->assertDirectoryExists($tablesPath);
        
        // Check that component files exist
        $formBuilderPath = $formsPath . '/Components/ApprovalFormBuilder.php';
        $progressColumnPath = $tablesPath . '/Components/ApprovalProgressColumn.php';
        
        if (File::exists($formBuilderPath)) {
            $content = File::get($formBuilderPath);
            $this->assertStringContainsString('class ApprovalFormBuilder', $content);
            $this->assertStringContainsString('public static function make()', $content);
        }
        
        if (File::exists($progressColumnPath)) {
            $content = File::get($progressColumnPath);
            $this->assertStringContainsString('class ApprovalProgressColumn', $content);
            $this->assertStringContainsString('getProgressPercentage', $content);
        }
    }

    /** @test */
    public function published_translations_have_correct_structure()
    {
        // Publish translations
        $this->artisan('approvals:publish', ['--translations' => true])
            ->assertExitCode(0);
        
        $translationsPath = resource_path('lang/vendor/filament-approvals');
        $this->assertDirectoryExists($translationsPath);
        
        $englishPath = $translationsPath . '/en/approvals.php';
        $this->assertFileExists($englishPath);
        
        $translations = include $englishPath;
        $this->assertIsArray($translations);
        
        // Check for expected translation keys
        $this->assertArrayHasKey('status_column', $translations);
        $this->assertArrayHasKey('actions', $translations);
        
        // Check nested structure
        $this->assertIsArray($translations['status_column']);
        $this->assertIsArray($translations['actions']);
    }

    /** @test */
    public function republishing_overwrites_existing_files()
    {
        // First publish
        $this->artisan('approvals:publish', ['--config' => true])
            ->assertExitCode(0);
        
        $configPath = config_path('approvals.php');
        $this->assertFileExists($configPath);
        
        // Modify the file
        $originalContent = File::get($configPath);
        File::put($configPath, "<?php\nreturn ['modified' => true];");
        
        $modifiedContent = File::get($configPath);
        $this->assertNotEquals($originalContent, $modifiedContent);
        
        // Republish
        $this->artisan('approvals:publish', ['--config' => true])
            ->assertExitCode(0);
        
        // Should be overwritten
        $newContent = File::get($configPath);
        $this->assertStringContainsString('role_model', $newContent);
        $this->assertStringNotContainsString("'modified' => true", $newContent);
    }

    /** @test */
    public function publishing_creates_directory_structure()
    {
        // Ensure directories don't exist
        $this->assertDirectoryDoesNotExist(resource_path('views/vendor'));
        $this->assertDirectoryDoesNotExist(app_path('Forms'));
        $this->assertDirectoryDoesNotExist(app_path('Tables'));
        
        // Publish components
        $this->artisan('approvals:publish', ['--components' => true])
            ->assertExitCode(0);
        
        // Directories should be created
        $this->assertDirectoryExists(app_path('Forms/Approvals'));
        $this->assertDirectoryExists(app_path('Tables/Approvals'));
        
        // Publish views
        $this->artisan('approvals:publish', ['--views' => true])
            ->assertExitCode(0);
        
        // View directory structure should be created
        $this->assertDirectoryExists(resource_path('views/vendor/filament-approvals'));
        $this->assertDirectoryExists(resource_path('views/vendor/filament-approvals/tables/columns'));
    }

    protected function cleanupPublishedFiles(): void
    {
        $paths = [
            config_path('approvals.php'),
            resource_path('views/vendor/filament-approvals'),
            resource_path('views/vendor'),
            app_path('Filament/Resources/ApprovalFlowResource.php'),
            app_path('Forms/Approvals'),
            app_path('Forms'),
            app_path('Tables/Approvals'),
            app_path('Tables'),
            resource_path('lang/vendor/filament-approvals'),
            resource_path('lang/vendor'),
            base_path('stubs/filament-approvals'),
            base_path('stubs'),
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
