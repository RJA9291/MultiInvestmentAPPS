<?php

namespace App\Modules\Identity\Interfaces\Console\Commands;

use App\Modules\Identity\Domain\Entities\User;
use App\Modules\Identity\Domain\Events\UserRegistered;
use App\Modules\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Modules\Identity\Domain\ValueObjects\EmailAddress;
use App\Modules\Identity\Domain\ValueObjects\PasswordHash;
use App\Modules\Identity\Domain\ValueObjects\RoleSet;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Manual account creation for the MVP soft-launch (`MVP_Launch_Strategy.docx`
 * Phase 2: "onboard 3-5 users manually"). No public self-registration
 * endpoint exists this Sprint by design — every early account is created
 * by the Business Owner running this command, then the email/password is
 * shared with that person directly (WhatsApp/personal contact), matching
 * the launch kit's own outreach scripts.
 *
 * Usage:
 *   php artisan accounts:create "Ahmad bin Ismail" ahmad@example.com business_owner
 *   php artisan accounts:create "Siti Aisha" siti@example.com investor --password="ChooseOwn123"
 *
 * If --password is omitted, a random 12-character password is generated
 * and printed once to the console — it is never logged anywhere else.
 */
class CreateAccountCommand extends Command
{
    protected $signature = 'accounts:create
        {name : Display name shown to other users}
        {email : Login email}
        {role : One of business_owner, investor, admin, compliance_officer}
        {--password= : Plaintext password; a random one is generated and printed if omitted}';

    protected $description = 'Manually create a login account (email + password) for the MVP soft launch.';

    public function handle(UserRepositoryInterface $users): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $name = trim((string) $this->argument('name'));
        $role = trim((string) $this->argument('role'));

        if ($users->existsByEmail($email)) {
            $this->error("An account with email {$email} already exists.");

            return self::FAILURE;
        }

        $plainPassword = $this->option('password') ?: Str::random(12);

        try {
            $user = User::register(
                id: (string) Str::uuid(),
                displayName: $name,
                email: EmailAddress::fromString($email),
                passwordHash: PasswordHash::fromPlainText($plainPassword),
                roles: RoleSet::fromArray([$role]),
            );
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $users->save($user);

        UserRegistered::dispatch($user->id(), $user->email()->toString(), $user->roles()->toArray());

        $this->info('Account created.');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Email', $email],
                ['Role', $role],
                ['Password', $plainPassword],
                ['Login URL', config('app.url') . '/login (or your API base + POST /v1/auth/login)'],
            ]
        );
        $this->warn('Copy the password now — it is shown only once. Share it with the user directly (see launch_kit Investor Outreach Scripts).');

        return self::SUCCESS;
    }
}
