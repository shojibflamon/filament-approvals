<?php

use EightyNine\Approvals\Tables\Actions\ReturnAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->mockUser = Mockery::mock(\Illuminate\Contracts\Auth\Authenticatable::class);
    $this->mockUser->shouldReceive('getKey')->andReturn(1);
    $this->mockUser->shouldReceive('hasRole')->andReturn(true);
    
    Auth::shouldReceive('user')->andReturn($this->mockUser);
    Auth::shouldReceive('id')->andReturn(1);
});

it('has correct default name', function () {
    expect(ReturnAction::getDefaultName())->toBe('Return');
});

it('can be instantiated', function () {
    $action = ReturnAction::make();
    expect($action)->toBeInstanceOf(ReturnAction::class);
});

it('has warning color', function () {
    $action = ReturnAction::make();
    expect($action->getColor())->toBe('warning');
});

it('has correct icon', function () {
    $action = ReturnAction::make();
    expect($action->getIcon())->toBe('heroicon-m-arrow-uturn-left');
});

it('has translated label', function () {
    $action = ReturnAction::make();
    expect($action->getLabel())->toBe(__('filament-approvals::approvals.actions.return'));
});

it('requires confirmation', function () {
    $action = ReturnAction::make();
    expect($action->getModalDescription())->toBe(__('filament-approvals::approvals.actions.return_confirmation_text'));
});

it('has comment form field', function () {
    $action = ReturnAction::make();
    $reflection = new ReflectionClass($action);
    $formProperty = $reflection->getProperty('form');
    expect($formProperty->getValue($action))->not->toBeNull();
});

it('does not allow action override', function () {
    $action = ReturnAction::make();
    
    $action->action('CustomAction');
})->throws(\Exception::class, 'You\'re unable to override the action for this plugin');
