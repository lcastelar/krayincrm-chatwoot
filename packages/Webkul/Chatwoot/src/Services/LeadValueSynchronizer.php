<?php

namespace Webkul\Chatwoot\Services;

use Illuminate\Support\Facades\DB;

class LeadValueSynchronizer
{
    /**
     * Recalculate a lead value from the amounts of its linked products.
     */
    public function recalculate(int $leadId): float
    {
        DB::statement('CALL chatwoot_recalculate_lead_value(?)', [$leadId]);

        return (float) DB::table('leads')
            ->where('id', $leadId)
            ->value('lead_value');
    }
}
