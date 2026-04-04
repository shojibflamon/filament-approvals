<?php

namespace EightyNine\Approvals\Forms\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class ReturnAction extends Action
{

    public static function getDefaultName(): ?string
    {
        return 'Return';
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->color('warning')
            ->action('Return')
            ->form($this->getDefaultForm())
            ->icon('heroicon-m-arrow-uturn-left')
            ->label(__('filament-approvals::approvals.actions.return'))
            ->visible(
                fn (Model $record) =>
                $record->canBeApprovedBy(Auth::user()) &&
                    $record->isSubmitted() &&
                    !$record->isApprovalCompleted() &&
                    !$record->isDiscarded() &&
                    !$record->isRejected() &&
                    !$record->isReturned()
            )
            ->requiresConfirmation()
            ->modalDescription(__('filament-approvals::approvals.actions.return_confirmation_text'));
    }


    public function action(Closure | string | null $action): static
    {
        if ($action !== 'Return') {
            throw new \Exception('You\'re unable to override the action for this plugin');
        }

        $this->action = $this->returnModel();

        return $this;
    }


    /**
     * Return the record to the previous step.
     */
    private function returnModel(): Closure
    {
        return function (array $data, Model $record): bool {
            
            $record->return(Arr::get($data, 'comment', ''), Auth::user());
            
            Notification::make()
                ->title(__('filament-approvals::approvals.notifications.returned'))
                ->success()
                ->send();

            return true;
        };
    }


    protected function getDefaultForm(): array
    {
        return [
            Textarea::make("comment")
                ->visible(config('approvals.enable_return_comments', true))
                ->required(config('approvals.require_return_comments', false)),
        ];
    }
}
