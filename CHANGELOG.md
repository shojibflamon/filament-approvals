# Changelog

All notable changes to `filament-approvals` will be documented in this file.

## 1.1.0 - 2026-04-04

### Added

- ReturnAction for returning approval records to previous step
- Forms/Actions/ReturnAction.php for page-level usage
- `require_return_comments` config option
- Notification translation keys (submitted, approved, rejected, returned, discarded, completed)

### Changed

- Filament support expanded to ^3.0|^4.0|^5.0
- Laravel support updated to ^11.28|^12.0
- Added Livewire ^3.0|^4.0 support
- Wired up `ui.status_colors` config for dynamic badge colors
- Wired up `notifications` config (database_enabled, events filtering) in all action files

### Fixed

- ReturnAction modal using wrong confirmation text
- Duplicate label call in ApproveAction
- Removed debug dump statement in ApproveAction
- Replaced Filament facade with helper in Blade view

## 1.0.0 - 202X-XX-XX

- initial release
