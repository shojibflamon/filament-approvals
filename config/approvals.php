<?php

// Configuration for EightyNine/Approvals
return [
    /*
    |--------------------------------------------------------------------------
    | Role Model
    |--------------------------------------------------------------------------
    |
    | Specify the model that represents roles in your application.
    | This model must be compatible with spatie/laravel-permission package.
    | The default assumes you're using the standard Role model from the package.
    |
    */
    "role_model" => App\Models\Role::class,

    /*
    |--------------------------------------------------------------------------
    | Navigation Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how the approval flows appear in your Filament navigation.
    | You can disable navigation registration entirely or customize the
    | icon and sort order.
    |
    */
    "navigation" => [
        // Whether to register navigation items for approval flows
        "should_register_navigation" => true,
        
        // Icon to use in the navigation (Heroicons)
        "icon" => "heroicon-o-clipboard-document-check",
        
        // Sort order in navigation (lower numbers appear first)
        "sort" => 1,
        
        // Navigation group (optional)
        "group" => null,
        
        // Navigation label (optional, defaults to resource label)
        "label" => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Comment Settings
    |--------------------------------------------------------------------------
    |
    | Configure whether users can add comments when performing approval actions.
    | Comments can provide valuable context for approval decisions.
    |
    */
    
    // Allow users to add comments when approving items
    "enable_approval_comments" => false,
    
    // Allow users to add comments when rejecting items (recommended)
    "enable_rejection_comments" => true,
    
    // Allow users to add comments when returning items (recommended)
    "enable_return_comments" => true,
    
    // Allow users to add comments when discarding items
    "enable_discard_comments" => true,
    
    // Require comments for rejections (helps with audit trail)
    "require_rejection_comments" => false,

    /*
    |--------------------------------------------------------------------------
    | UI Customization
    |--------------------------------------------------------------------------
    |
    | Customize the appearance and behavior of approval components.
    |
    */
    "ui" => [
        // Show approval history in tables
        "show_approval_history" => true,
        
        // Show user avatars in approval history
        "show_user_avatars" => true,
        
        // Date format for approval timestamps
        "date_format" => "M j, Y g:i A",
        
        // Colors for different approval statuses
        "status_colors" => [
            "pending" => "warning",
            "approved" => "success", 
            "rejected" => "danger",
            "discarded" => "gray",
            "submitted" => "info",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security & Permissions
    |--------------------------------------------------------------------------
    |
    | Configure security settings for approval processes.
    |
    */
    "security" => [
        // Prevent users from approving their own submissions
        "prevent_self_approval" => true,
        
        // Log all approval actions for audit purposes
        "audit_approvals" => true,
        
        // Additional permissions required for approval actions
        "required_permissions" => [
            // 'can-approve-high-value-items',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notifications for approval process events.
    |
    */
    "notifications" => [
        // Enable email notifications for approval events
        "email_enabled" => false,
        
        // Enable database notifications
        "database_enabled" => true,
        
        // Notification events to send
        "events" => [
            "submitted",
            "approved", 
            "rejected",
            "completed",
        ],
    ],
];
