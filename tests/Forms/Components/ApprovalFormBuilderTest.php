<?php

namespace EightyNine\Approvals\Tests\Forms\Components;

use EightyNine\Approvals\Forms\Components\ApprovalFormBuilder;
use EightyNine\Approvals\Tests\TestCase;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class ApprovalFormBuilderTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $component = ApprovalFormBuilder::make();
        
        $this->assertInstanceOf(ApprovalFormBuilder::class, $component);
    }

    /** @test */
    public function it_has_correct_view()
    {
        $component = ApprovalFormBuilder::make();
        
        $this->assertEquals('filament-approvals::forms.components.approval-form-builder', $component->getView());
    }

    /** @test */
    public function it_returns_child_components()
    {
        $component = ApprovalFormBuilder::make();
        $children = $component->getChildComponents();
        
        $this->assertIsArray($children);
        $this->assertNotEmpty($children);
        
        // Should contain a Section component
        $this->assertInstanceOf(Section::class, $children[0]);
    }

    /** @test */
    public function child_components_have_approval_flow_select()
    {
        $component = ApprovalFormBuilder::make();
        $children = $component->getChildComponents();
        
        $section = $children[0];
        $schema = $section->getChildComponents();
        
        // Find the approval_flow_id select
        $approvalFlowSelect = collect($schema)->first(function ($component) {
            return $component instanceof Select && $component->getName() === 'approval_flow_id';
        });
        
        $this->assertNotNull($approvalFlowSelect);
        $this->assertInstanceOf(Select::class, $approvalFlowSelect);
    }

    /** @test */
    public function child_components_have_priority_select()
    {
        $component = ApprovalFormBuilder::make();
        $children = $component->getChildComponents();
        
        $section = $children[0];
        $schema = $section->getChildComponents();
        
        // Find the priority select
        $prioritySelect = collect($schema)->first(function ($component) {
            return $component instanceof Select && $component->getName() === 'priority';
        });
        
        $this->assertNotNull($prioritySelect);
        $this->assertInstanceOf(Select::class, $prioritySelect);
        
        // Check priority options
        $options = $prioritySelect->getOptions();
        $expectedOptions = ['low', 'normal', 'high', 'urgent'];
        
        foreach ($expectedOptions as $option) {
            $this->assertArrayHasKey($option, $options);
        }
    }

    /** @test */
    public function child_components_have_toggles()
    {
        $component = ApprovalFormBuilder::make();
        $children = $component->getChildComponents();
        
        $section = $children[0];
        $schema = $section->getChildComponents();
        
        // Find toggles
        $toggles = collect($schema)->filter(function ($component) {
            return $component instanceof Toggle;
        });
        
        $this->assertGreaterThan(0, $toggles->count());
        
        // Check for specific toggles
        $toggleNames = $toggles->map(fn($t) => $t->getName())->toArray();
        $this->assertContains('require_comments', $toggleNames);
        $this->assertContains('auto_submit', $toggleNames);
    }

    /** @test */
    public function section_is_collapsible()
    {
        $component = ApprovalFormBuilder::make();
        $children = $component->getChildComponents();
        
        $section = $children[0];
        
        $this->assertTrue($section->isCollapsible());
        $this->assertFalse($section->isCollapsed()); // Should not be collapsed by default
    }

    /** @test */
    public function section_has_correct_description()
    {
        $component = ApprovalFormBuilder::make();
        $children = $component->getChildComponents();
        
        $section = $children[0];
        
        $this->assertEquals('Configure the approval workflow for this process', $section->getDescription());
    }
}
