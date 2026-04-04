<?php

namespace EightyNine\Approvals\Tables\Actions;

use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class ApproveAction extends Action
{

    public static function getDefaultName(): ?string
    {
        return 'Approve';
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->color('primary')
            ->action('Approve')
            ->label(__('filament-approvals::approvals.actions.approve'))
            ->icon('heroicon-m-check')
            ->label(__('filament-approvals::approvals.actions.approve'))
            ->form($this->getDefaultForm())
            ->visible(
                fn (Model $record) =>
//                dump($record) . dump($record->canBeApprovedBy(Auth::user())) . dump($record->isSubmitted()).dump(!$record->isApprovalCompleted())  .dump(!$record->isDiscarded()).dd('hit').
                    $record->isSubmitted() &&
                    $record->canBeApprovedBy(Auth::user()) &&
                    !$record->isApprovalCompleted() &&
                    !$record->isDiscarded()
            )
            ->requiresConfirmation()
            ->modalDescription(__('filament-approvals::approvals.actions.approve_confirmation_text'));
        
    }


    public function action(Closure | string | null $action): static
    {
        if ($action !== 'Approve') {
            throw new \Exception('You\'re unable to override the action for this plugin');
        }

        $this->action = $this->approveModel();

        return $this;
    }


    /**
     * Approve data function.
     *
     */
    private function approveModel(): Closure
    {
        return function (array $data, Model $record): bool {
            $record->approve(comment: Arr::get($data, 'comment', ''), user: Auth::user());
            Notification::make()
                ->title('Approved successfully')
                ->success()
                ->send();
            return true;
        };
    }
    
    protected function getDefaultForm(): array
    {
        return [
            Textarea::make("comment")
                ->visible(config('approvals.enable_approval_comments', false)),
        ];
    }
}
