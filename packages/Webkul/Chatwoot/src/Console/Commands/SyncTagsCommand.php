<?php

namespace Webkul\Chatwoot\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Chatwoot\Services\ChatwootApiService;
use Webkul\Chatwoot\Services\SyncContext;
use Webkul\Tag\Models\Tag;

class SyncTagsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatwoot:sync-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automated background sync of Chatwoot labels into Krayin tag catalog and automatic purge of deleted labels.';

    /**
     * Execute the console command.
     */
    public function handle(ChatwootApiService $apiService)
    {
        $this->info('Starting automated Chatwoot tag sync and purge...');

        $result = SyncContext::executeWithoutLoop(function () use ($apiService) {
            $chatwootLabels = $apiService->getLabels();

            if ($chatwootLabels === null) {
                return ['success' => false, 'message' => 'Failed to reach Chatwoot API'];
            }

            $activeTitles = [];
            $syncedCount = 0;

            foreach ($chatwootLabels as $label) {
                $title = trim($label['title'] ?? '');
                $color = $label['color'] ?? '#51C984';

                if (empty($title)) {
                    continue;
                }

                $activeTitles[] = $title;

                Tag::updateOrCreate(
                    ['name' => $title],
                    ['color' => $color, 'user_id' => 1]
                );

                $syncedCount++;
            }

            // Purge deleted/stale tags
            $deletedCount = 0;
            if (! empty($activeTitles)) {
                $deletedCount = Tag::whereNotIn('name', $activeTitles)->delete();
            }

            return [
                'success' => true,
                'synced'  => $syncedCount,
                'purged'  => $deletedCount,
            ];
        });

        if ($result['success'] ?? false) {
            $this->info("Tags synced: {$result['synced']}, Obsolete tags purged: {$result['purged']}");
        } else {
            $this->error($result['message'] ?? 'Error occurred during sync');
        }

        return 0;
    }
}
