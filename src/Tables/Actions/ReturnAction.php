<?php

namespace EightyNine\Approvals\Tables\Actions;

use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
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

        $this->color('danger')
            ->action('Return')
            ->form($this->getDefaultForm())
            ->icon('heroicon-m-no-symbol')
            ->label(__('filament-approvals::approvals.actions.return'))
            ->visible(
                fn (Model $record) =>
                $record->canBeApprovedBy(Auth::user()) &&
                    $record->isSubmitted() &&
                    !$record->isApprovalCompleted() &&
                    !$record->isDiscarded() &&
                    !$record->isRejected()
            )
            ->requiresConfirmation()
            ->modalDescription(__('filament-approvals::approvals.actions.reject_confirmation_text'));
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
     * Discard data function.
     *
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
                ->visible(config('approvals.enable_return_comments', true)),
        ];
    }
}
