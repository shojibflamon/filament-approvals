<?php

namespace EightyNine\Approvals\Tests\Tables\Components;

use EightyNine\Approvals\Models\ApprovableModel;
use EightyNine\Approvals\Tables\Components\ApprovalProgressColumn;
use EightyNine\Approvals\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;

class ApprovalProgressColumnTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        $this->assertInstanceOf(ApprovalProgressColumn::class, $column);
    }

    /** @test */
    public function it_has_correct_view()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        $this->assertEquals('filament-approvals::tables.columns.approval-progress-column', $column->getView());
    }

    /** @test */
    public function it_has_default_label()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        $this->assertEquals('Approval Progress', $column->getLabel());
    }

    /** @test */
    public function it_calculates_progress_percentage_when_no_approval_status()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        $record = Mockery::mock(ApprovableModel::class);
        $record->shouldReceive('offsetExists')->andReturn(false);
        $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(null);
        
        $percentage = $column->getProgressPercentage($record);
        
        $this->assertEquals(0, $percentage);
    }

    /** @test */
    public function it_calculates_progress_percentage_with_approval_status()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        // Mock approval flow with 3 steps
        $approvalFlow = Mockery::mock(ProcessApprovalFlow::class);
        $stepsRelation = Mockery::mock(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        $stepsRelation->shouldReceive('count')->andReturn(3);
        $approvalFlow->shouldReceive('steps')->andReturn($stepsRelation);
        
        // Mock 2 approved steps
        $approvalsRelation = Mockery::mock(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
        $approvalsRelation->shouldReceive('where')->with('approval_action', 'Approved')->andReturnSelf();
        $approvalsRelation->shouldReceive('count')->andReturn(2);
        
        $record = Mockery::mock(ApprovableModel::class);
        $record->shouldReceive('offsetExists')->andReturn(true);
        $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(true);
        $record->shouldReceive('getAttribute')->with('approvalFlow')->andReturn($approvalFlow);
        $record->shouldReceive('approvals')->andReturn($approvalsRelation);
        
        $percentage = $column->getProgressPercentage($record);
        
        $this->assertEquals(67, $percentage); // 2/3 * 100 rounded
    }

    /** @test */
    public function it_returns_current_step_when_no_approval_status()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        $record = Mockery::mock(ApprovableModel::class);
        $record->shouldReceive('offsetExists')->andReturn(false);
        $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(null);
        
        $currentStep = $column->getCurrentStep($record);
        
        $this->assertNull($currentStep);
    }

    /** @test */
    public function it_returns_completed_when_approval_is_complete()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        $record = Mockery::mock(ApprovableModel::class);
        $record->shouldReceive('offsetExists')->andReturn(true);
        $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(true);
        $record->shouldReceive('isApprovalCompleted')->andReturn(true);
        
        $currentStep = $column->getCurrentStep($record);
        
        $this->assertEquals('Completed', $currentStep);
    }

    /** @test */
    public function it_returns_next_approver_name_when_not_complete()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        $nextApprover = Mockery::mock();
        $nextApprover->name = 'John Doe';
        
        $record = Mockery::mock(ApprovableModel::class);
        $record->shouldReceive('offsetExists')->andReturn(true);
        $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(true);
        $record->shouldReceive('isApprovalCompleted')->andReturn(false);
        $record->shouldReceive('getAttribute')->with('nextApprover')->andReturn($nextApprover);
        
        $currentStep = $column->getCurrentStep($record);
        
        $this->assertEquals('John Doe', $currentStep);
    }

    /** @test */
    public function it_returns_unknown_when_no_next_approver()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        $record = Mockery::mock(ApprovableModel::class);
        $record->shouldReceive('offsetExists')->andReturn(true);
        $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(true);
        $record->shouldReceive('isApprovalCompleted')->andReturn(false);
        $record->shouldReceive('getAttribute')->with('nextApprover')->andReturn(null);
        
        $currentStep = $column->getCurrentStep($record);
        
        $this->assertEquals('Unknown', $currentStep);
    }

    /** @test */
    public function it_determines_step_status_correctly()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        // Test no approval status
        $record = Mockery::mock(ApprovableModel::class);
        $record->shouldReceive('offsetExists')->andReturn(false);
        $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(null);
        
        $this->assertEquals('not-started', $column->getStepStatus($record));
        
        // Test completed
        $record = Mockery::mock(ApprovableModel::class);
        $record->shouldReceive('offsetExists')->andReturn(true);
        $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(true);
        $record->shouldReceive('isApprovalCompleted')->andReturn(true);
        
        $this->assertEquals('completed', $column->getStepStatus($record));
        
        // Test pending status
        $approvalStatus = Mockery::mock();
        $approvalStatus->status = 'Pending';
        
        $record = Mockery::mock(ApprovableModel::class);
        $record->shouldReceive('offsetExists')->andReturn(true);
        $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn($approvalStatus);
        $record->shouldReceive('isApprovalCompleted')->andReturn(false);
        
        $this->assertEquals('pending', $column->getStepStatus($record));
    }

    /** @test */
    public function it_returns_correct_progress_colors()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        $testCases = [
            'completed' => 'success',
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            'discarded' => 'danger',
            'unknown' => 'gray',
        ];
        
        foreach ($testCases as $status => $expectedColor) {
            $record = Mockery::mock(ApprovableModel::class);
            
            // Mock the getStepStatus method behavior
            if ($status === 'completed') {
                $record->shouldReceive('offsetExists')->andReturn(true);
                $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(true);
                $record->shouldReceive('isApprovalCompleted')->andReturn(true);
            } elseif ($status === 'unknown') {
                $record->shouldReceive('offsetExists')->andReturn(false);
                $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(null);
            } else {
                $approvalStatus = Mockery::mock();
                $approvalStatus->status = ucfirst($status);
                
                $record->shouldReceive('offsetExists')->andReturn(true);
                $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn($approvalStatus);
                $record->shouldReceive('isApprovalCompleted')->andReturn(false);
            }
            
            $color = $column->getProgressColor($record);
            $this->assertEquals($expectedColor, $color, "Expected color for status '{$status}' to be '{$expectedColor}', got '{$color}'");
        }
    }

    /** @test */
    public function it_handles_edge_case_with_no_approval_flow()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        $record = Mockery::mock(ApprovableModel::class);
        $record->shouldReceive('offsetExists')->andReturn(true);
        $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(true);
        $record->shouldReceive('getAttribute')->with('approvalFlow')->andReturn(null);
        
        $approvalsRelation = Mockery::mock(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
        $approvalsRelation->shouldReceive('where')->with('approval_action', 'Approved')->andReturnSelf();
        $approvalsRelation->shouldReceive('count')->andReturn(0);
        $record->shouldReceive('approvals')->andReturn($approvalsRelation);
        
        $percentage = $column->getProgressPercentage($record);
        
        $this->assertEquals(0, $percentage);
    }

    /** @test */
    public function it_caps_progress_at_100_percent()
    {
        $column = ApprovalProgressColumn::make('approval_progress');
        
        // Mock scenario where approved steps exceed total steps
        $approvalFlow = Mockery::mock(ProcessApprovalFlow::class);
        $stepsRelation = Mockery::mock(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        $stepsRelation->shouldReceive('count')->andReturn(2);
        $approvalFlow->shouldReceive('steps')->andReturn($stepsRelation);
        
        $approvalsRelation = Mockery::mock(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
        $approvalsRelation->shouldReceive('where')->with('approval_action', 'Approved')->andReturnSelf();
        $approvalsRelation->shouldReceive('count')->andReturn(3); // More than total steps
        
        $record = Mockery::mock(ApprovableModel::class);
        $record->shouldReceive('offsetExists')->andReturn(true);
        $record->shouldReceive('getAttribute')->with('approvalStatus')->andReturn(true);
        $record->shouldReceive('getAttribute')->with('approvalFlow')->andReturn($approvalFlow);
        $record->shouldReceive('approvals')->andReturn($approvalsRelation);
        
        $percentage = $column->getProgressPercentage($record);
        
        $this->assertEquals(100, $percentage);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
