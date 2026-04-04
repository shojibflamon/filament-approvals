<?php

namespace EightyNine\Approvals\Tables\Actions;

use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Model;

class ApprovalActions
{
    public static function make(Action|array $action, $alwaysVisibleActions = []): array
    {
        
        $actions = [
            ActionGroup::make([
                SubmitAction::make(),
                ApproveAction::make(),
                ReturnAction::make(),
                RejectAction::make(),
                DiscardAction::make(),
            ])
                ->label(__('filament-approvals::approvals.actions.approvals'))
                ->icon('heroicon-m-ellipsis-vertical')
                ->size(ActionSize::Small)
                ->color('primary')
                ->button(),
        ];
        
        if(is_array($action)) {
            foreach($action as $a) {
                $actions[] = $a->visible(fn (Model $record) => $record->isApprovalCompleted());
            }
        } else {
            $actions[] = $action->visible(fn (Model $record) => $record->isApprovalCompleted());
        }
        
        return array_merge($actions, $alwaysVisibleActions);
    }
}
