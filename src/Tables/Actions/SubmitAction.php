<?php

namespace EightyNine\Approvals\Tables\Actions;

use Closure;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SubmitAction extends Action
{

    public static function getDefaultName(): ?string
    {
        return 'Submit';
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->color('primary')
            ->icon('heroicon-m-arrow-right-circle')
            ->action('Submit')
            ->label(__('filament-approvals::approvals.actions.submit'))
            ->visible(fn (Model $record) => !$record->isSubmitted() &&
            $record->approvalStatus->creator_id == Auth::id())
            ->requiresConfirmation()
            ->modalDescription(__('filament-approvals::approvals.actions.submit_confirmation_text'));
    }


    public function action(Closure | string | null $action): static
    {
        if ($action !== 'Submit') {
            throw new \Exception('You\'re unable to override the action for this plugin');
        }

        $this->action = $this->submitModel();

        return $this;
    }


    /**
     * Submit the record.
     */
    private function submitModel(): Closure
    {
        return function (array $data, Model $record): bool {
            $record->submit(Auth::user());
            
            if (config('approvals.notifications.database_enabled', true)) {
                $events = config('approvals.notifications.events', ['submitted', 'approved', 'rejected', 'returned', 'completed']);
                if (in_array('submitted', $events)) {
                    Notification::make()
                        ->title(__('filament-approvals::approvals.notifications.submitted'))
                        ->success()
                        ->send();
                }
            }
            return true;
        };
    }
}
