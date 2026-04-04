<?php

namespace EightyNine\Approvals\Forms\Actions;

use Closure;
use EightyNine\Approvals\Models\ApprovableModel;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DiscardAction extends Action
{

    public static function getDefaultName(): ?string
    {
        return 'Discard';
    }


    protected function setUp(): void
    {
        parent::setUp();

        $this->color('danger')
            ->action('Discard')
            ->label(__('filament-approvals::approvals.actions.discard'))
            ->icon('heroicon-m-trash')
            ->visible(
                fn (Model $record) =>
                $record->canBeApprovedBy(Auth::user()) &&
                    $record->isRejected()
            )
            ->requiresConfirmation()
            ->modalDescription(__('filament-approvals::approvals.actions.discard_confirmation_text'));
    }


    public function action(Closure | string | null $action): static
    {
        if ($action !== 'Discard') {
            throw new \Exception('You\'re unable to override the action for this plugin');
        }

        $this->action = $this->discardModel();

        return $this;
    }


    /**
     * Discard the record.
     */
    private function discardModel(): Closure
    {
        return function (array $data, Model $record): bool {
            $record->discard(null, Auth::user());
            
            if (config('approvals.notifications.database_enabled', true)) {
                $events = config('approvals.notifications.events', ['submitted', 'approved', 'rejected', 'returned', 'completed']);
                if (in_array('completed', $events)) {
                    Notification::make()
                        ->title(__('filament-approvals::approvals.notifications.discarded'))
                        ->success()
                        ->send();
                }
            }

            return true;
        };
    }
}
