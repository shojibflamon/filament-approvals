<?php

namespace EightyNine\Approvals\Forms\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RejectAction extends Action
{


    public static function getDefaultName(): ?string
    {
        return 'Reject';
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->color('danger')
            ->action('Reject')
            ->label(__('filament-approvals::approvals.actions.reject'))
            ->icon('heroicon-m-no-symbol')
            ->form($this->getDefaultForm())
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
        if ($action !== 'Reject') {
            throw new \Exception('You\'re unable to override the action for this plugin');
        }

        $this->action = $this->rejectModel();

        return $this;
    }


    /**
     * Reject the record.
     */
    private function rejectModel(): Closure
    {
        return function (array $data, Model $record): bool {
            $record->reject($data["comment"], Auth::user());
            
            if (config('approvals.notifications.database_enabled', true)) {
                $events = config('approvals.notifications.events', ['submitted', 'approved', 'rejected', 'returned', 'completed']);
                if (in_array('rejected', $events)) {
                    Notification::make()
                        ->title(__('filament-approvals::approvals.notifications.rejected'))
                        ->success()
                        ->send();
                }
            }

            return true;
        };
    }


    protected function getDefaultForm(): array
    {
        return [
            Textarea::make("comment"),
        ];
    }
}
