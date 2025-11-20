<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class TestInvitationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:invitation-email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test invitation email to verify email configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address format.');
            return 1;
        }

        $this->info('Testing invitation email to: ' . $email);
        $this->info('Using SMTP configuration from .env');
        $this->newLine();

        // Display current mail configuration
        $this->info('Mail Configuration:');
        $this->line('  Mailer: ' . config('mail.default'));
        $this->line('  Host: ' . config('mail.mailers.smtp.host'));
        $this->line('  Port: ' . config('mail.mailers.smtp.port'));
        $this->line('  Encryption: ' . config('mail.mailers.smtp.encryption'));
        $this->line('  Username: ' . config('mail.mailers.smtp.username'));
        $this->line('  From Address: ' . config('mail.from.address'));
        $this->line('  From Name: ' . config('mail.from.name'));
        $this->newLine();

        // Create a test invitation
        $this->info('Creating test invitation...');
        
        // Get the first admin user (or create a dummy entry)
        $adminUser = User::where('role', 'system_admin')
            ->orWhere('role', 'admin')
            ->first();

        if (!$adminUser) {
            $this->error('No admin user found in database. Please create an admin user first.');
            return 1;
        }

        // Create test invitation
        $invitation = Invitation::create([
            'email' => $email,
            'token' => Invitation::generateToken(),
            'role' => 'staff', // Default role for test
            'invited_by' => $adminUser->id,
            'expires_at' => Carbon::now()->addDays(7),
            'used' => false,
        ]);

        $this->info('Test invitation created with token: ' . substr($invitation->token, 0, 16) . '...');
        $this->newLine();

        // Send the email
        try {
            $this->info('Sending test invitation email (real-time)...');
            $this->warn('Note: Emails are now sent immediately (not queued)');
            $this->newLine();
            
            Notification::route('mail', $email)
                ->notify(new UserInvitation($invitation));

            $this->newLine();
            $this->info('✓ Email sent successfully in real-time!');
            $this->newLine();
            
            $this->info('Next steps:');
            $this->line('  1. Check your inbox (and spam folder) at: ' . $email);
            $this->line('  2. Verify the email looks professional');
            $this->line('  3. Click the "Create Your Account" button');
            $this->line('  4. Verify it opens the registration page');
            $this->newLine();
            
            $this->info('Registration URL: ' . route('register', ['token' => $invitation->token]));
            $this->newLine();

            // Clean up test invitation
            if ($this->confirm('Delete test invitation from database?', true)) {
                $invitation->delete();
                $this->info('✓ Test invitation deleted.');
            } else {
                $this->warn('Test invitation kept in database (ID: ' . $invitation->id . ')');
            }

            return 0;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('✗ Failed to send email!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            
            $this->warn('Common issues:');
            $this->line('  • Check your .env mail configuration');
            $this->line('  • Verify SMTP credentials are correct');
            $this->line('  • Ensure firewall allows port ' . config('mail.mailers.smtp.port'));
            $this->line('  • Check if queue worker is running (if using queues)');
            $this->line('  • Try: php artisan config:clear');
            $this->newLine();

            // Clean up test invitation
            $invitation->delete();
            
            return 1;
        }
    }
}
