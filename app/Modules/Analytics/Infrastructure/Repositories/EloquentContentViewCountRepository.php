<?php

namespace App\Modules\Analytics\Infrastructure\Repositories;

use App\Modules\Analytics\Domain\Repositories\ContentViewCountRepositoryInterface;
use App\Modules\Analytics\Infrastructure\Eloquent\ContentViewCountModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EloquentContentViewCountRepository implements ContentViewCountRepositoryInterface
{
    public function recordView(string $viewableType, string $viewableId): void
    {
        DB::transaction(function () use ($viewableType, $viewableId) {
            $row = ContentViewCountModel::where('viewable_type', $viewableType)
                ->where('viewable_id', $viewableId)
                ->lockForUpdate()
                ->first();

            if ($row) {
                $row->increment('view_count');
                $row->update(['last_viewed_at' => now()]);

                return;
            }

            ContentViewCountModel::create([
                'id' => (string) Str::uuid(),
                'viewable_type' => $viewableType,
                'viewable_id' => $viewableId,
                'view_count' => 1,
                'last_viewed_at' => now(),
            ]);
        });
    }

    public function countFor(string $viewableType, string $viewableId): int
    {
        return (int) (ContentViewCountModel::where('viewable_type', $viewableType)
            ->where('viewable_id', $viewableId)
            ->value('view_count') ?? 0);
    }
}
