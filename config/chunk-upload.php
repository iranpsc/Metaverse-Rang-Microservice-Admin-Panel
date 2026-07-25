<?php
/**
 * @see https://github.com/pionl/laravel-chunk-upload
 */

return [
    /*
     * The storage config
     */
    'storage' => [
        /*
         * Returns the folder name of the chunks. The location is in storage/app/{folder_name}
         */
        'chunks' => 'chunks',
        'disk' => 'local',
    ],
    'clear' => [
        /*
         * How old chunks we should delete
         */
        'timestamp' => '-3 HOURS',
        'schedule' => [
            'enabled' => true,
            'cron' => '25 * * * *', // run every hour on the 25th minute
        ],
    ],
    'chunk' => [
        // Chunk file naming. Prefer the client upload identifier (resumableIdentifier)
        // over session/browser — session locking blocks parallel chunk requests during
        // large merges in production and can prevent assembly from ever reaching 100%.
        'name' => [
            'use' => [
                'session' => false,
                'browser' => false,
            ],
        ],
    ],
    'handlers' => [
        // A list of handlers/providers that will be appended to existing list of handlers
        'custom' => [],
        // Overrides the list of handlers - use only what you really want
        'override' => [
            // \Pion\Laravel\ChunkUpload\Handler\DropZoneUploadHandler::class
        ],
    ],
];
