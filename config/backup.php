<?php

return [

    'backup' => [
        'name' => 'kab-magelang-labkes-backup',

        'source' => [
            'files' => [
                'include' => [base_path()],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    base_path('storage/logs'),
                    base_path('storage/framework/cache'),
                    base_path('storage/framework/sessions'),
                    base_path('storage/framework/views'),
                    base_path('storage/app/backups')
                ],
                'files' => [
                    'include' => [],
                    'exclude' => [],
                ],
                'databases' => ['mysql'],
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => null,
            ],
            'databases' => ['mysql'],
        ],

        'database_dump_compressor' => null,
        'database_dump_file_extension' => '',
        'destination' => [
            'filename_prefix' => 'kab-magelang-labkes-backup-',
            'disks' => ['local'],
        ],
        'temporary_directory' => storage_path('app/backup-temp'),
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => 'default',
    ],

    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class => ['mail'],
        ],
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
        'mail' => [
            'to' => env('BACKUP_MAIL_TO', 'admin@kab-magelang-labkes.com'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'noreply@kab-magelang-labkes.com'),
                'name' => env('MAIL_FROM_NAME', 'kab-magelang Labkes Backup System'),
            ],
        ],
        'slack' => [
            'webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL', ''),
            'channel' => null,
            'username' => null,
            'icon' => null,
        ],
        'discord' => [
            'webhook_url' => env('BACKUP_DISCORD_WEBHOOK_URL', ''),
            'username' => null,
            'avatar_url' => null,
        ],
    ],

    'monitor_backups' => [
        [
            'name' => 'kab-magelang-labkes-backup',
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],

    // Custom backup settings
    'custom' => [
        'backup_path' => storage_path('app/backups'),
        'max_backups' => 10,
        'backup_retention_days' => 30,
        'compress_backups' => true,
        'backup_database' => true,
        'backup_files' => true,
    ],

];
