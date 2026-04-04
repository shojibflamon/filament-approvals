<?php

namespace EightyNine\Approvals\Tables\Actions;

use Closure;
use EightyNine\Approvals\Models\ApprovableModel;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
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
            ->icon('heroicon-m-archive-box-x-mark')
            ->action('Discard')
            ->label(__('filament-approvals::approvals.actions.discard'))
            ->visible(
                fn (Model $record) =>
                $record->isRejected() &&
                $record->canBeApprovedBy(Auth::user())
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
     * Discard data function.
     *
     */
    private function discardModel(): Closure
    {
        return function (array $data, ApprovableModel $record): bool {
            $record->discard(null, Auth::user());
            Notification::make()
                ->title('Discarded successfully')
                ->success()
                ->send();

            return true;
        };
    }
}
