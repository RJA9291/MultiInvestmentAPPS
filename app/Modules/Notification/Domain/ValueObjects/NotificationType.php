<?php

namespace App\Modules\Notification\Domain\ValueObjects;

/**
 * NotificationType — the Project Owner's brief's own proposed type list,
 * kept as-is (no locked enum value set exists yet for this column,
 * `08_DATABASE_DESIGN.md` DB-028 only says "enum" without enumerating
 * values) — extended with DATA_ROOM_GRANTED/REVOKED since the brief listed
 * both as source events but only in its mapping table, not this list.
 */
enum NotificationType: string
{
    case ProjectSubmitted = 'PROJECT_SUBMITTED';
    case ProjectApproved = 'PROJECT_APPROVED';
    case ProjectRejected = 'PROJECT_REJECTED';
    case DocumentUploaded = 'DOCUMENT_UPLOADED';
    case DocumentApproved = 'DOCUMENT_APPROVED';
    case DocumentRejected = 'DOCUMENT_REJECTED';
    case AiReviewReady = 'AI_REVIEW_READY';
    case AccessRequested = 'ACCESS_REQUESTED';
    case AccessApproved = 'ACCESS_APPROVED';
    case AccessRejected = 'ACCESS_REJECTED';
    case DataRoomGranted = 'DATA_ROOM_GRANTED';
    case DataRoomRevoked = 'DATA_ROOM_REVOKED';
    case InvestorVerified = 'INVESTOR_VERIFIED';
    case InvestorRejected = 'INVESTOR_REJECTED';
}
