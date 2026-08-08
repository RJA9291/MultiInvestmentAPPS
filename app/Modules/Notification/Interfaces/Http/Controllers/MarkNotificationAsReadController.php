<?php

namespace App\Modules\Notification\Interfaces\Http\Controllers;

use App\Core\Shared\Http\ApiResponse;
use App\Modules\Notification\Application\Services\MarkNotificationAsReadService;
use App\Modules\Notification\Interfaces\Http\Requests\MarkNotificationAsReadRequest;
use App\Modules\Notification\Interfaces\Http\Resources\NotificationResource;
use Illuminate\Auth\Access\AuthorizationException;
use RuntimeException;

/** Proposed API-028: POST /v1/notifications/{id}/read */
class MarkNotificationAsReadController
{
    public function __invoke(string $id, MarkNotificationAsReadRequest $request, MarkNotificationAsReadService $service)
    {
        $validated = $request->validated();

        try {
            $notification = $service->execute($id, $validated['requesting_user_id']);
        } catch (RuntimeException $e) {
            return ApiResponse::error('NOTIFICATION_NOT_FOUND', $e->getMessage(), status: 404);
        } catch (AuthorizationException $e) {
            return ApiResponse::error('NOTIFICATION_NOT_OWNED_BY_USER', $e->getMessage(), status: 403);
        }

        return ApiResponse::success(new NotificationResource($notification));
    }
}
