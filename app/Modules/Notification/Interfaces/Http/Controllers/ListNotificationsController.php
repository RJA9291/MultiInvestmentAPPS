<?php

namespace App\Modules\Notification\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Notification\Domain\Repositories\NotificationRepositoryInterface;
use App\Modules\Notification\Interfaces\Http\Requests\ListNotificationsRequest;
use App\Modules\Notification\Interfaces\Http\Resources\NotificationResource;

/** Proposed API-027: GET /v1/notifications?recipient_user_id=... */
class ListNotificationsController
{
    public function __invoke(ListNotificationsRequest $request, NotificationRepositoryInterface $notifications)
    {
        $validated = $request->validated();

        return ApiResponse::success(
            NotificationResource::collection($notifications->findForRecipient($validated['recipient_user_id']))
        );
    }
}
