<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class StorePreflight extends Command
{
    protected $signature = 'store:preflight {--production : Require all live-service configuration}';

    protected $description = 'Verify that C-Net Store is ready to run safely';

    private int $failures = 0;

    public function handle(): int
    {
        $production = (bool) $this->option('production');
        $this->newLine();
        $this->info('C-Net Store deployment preflight');

        $this->check('PHP 8.2 or newer', version_compare(PHP_VERSION, '8.2.0', '>='), PHP_VERSION);
        $this->check('Application key configured', filled(config('app.key')));
        $this->check('Cash on Delivery disabled', config('store.cod_enabled') === false);
        $this->check('HTTPS application URL', str_starts_with((string) config('app.url'), 'https://'), (string) config('app.url'));
        $this->check('Debug mode disabled', ! config('app.debug'), config('app.debug') ? 'enabled' : 'disabled');
        $this->check('Production environment', ! $production || app()->environment('production'), app()->environment());

        foreach ([storage_path('framework'), storage_path('logs'), base_path('bootstrap/cache')] as $path) {
            $this->check("Writable: {$path}", is_dir($path) && is_writable($path));
        }

        $databaseReady = false;
        try {
            DB::connection()->getPdo();
            $databaseReady = true;
            $this->pass('Database connection');
            Artisan::call('migrate:status');
            $this->pass('Migration table available');
        } catch (Throwable $exception) {
            $this->failCheck('Database connection/migrations', $exception->getMessage());
        }

        if ($databaseReady) {
            $catalogAudit = Artisan::call('store:catalog-audit');
            $this->check('Published catalogue is safe', $catalogAudit === self::SUCCESS);
            $imageAudit = Artisan::call('store:image-library-audit');
            $this->check('Product image library is valid', $imageAudit === self::SUCCESS);
        }

        $this->check('Razorpay key configured', ! $production || filled(config('services.razorpay.key_id')));
        $this->check('Razorpay secret configured', ! $production || filled(config('services.razorpay.key_secret')));
        $this->check('Razorpay webhook secret configured', ! $production || filled(config('services.razorpay.webhook_secret')));
        $adminEmail = config('store.admin_email');
        $this->check('Administrator email configured', filled($adminEmail));
        $adminExists = $databaseReady && filled($adminEmail) && User::query()->where('email', $adminEmail)->where('role', UserRole::SuperAdmin)->exists();
        $this->check('Administrator account available', ! $production || $adminExists);
        $this->check('SMTP host configured', ! $production || filled(config('mail.mailers.smtp.host')));
        $this->check('Mail sender configured', filled(config('mail.from.address')));
        $this->check('Queue is not synchronous in production', ! $production || config('queue.default') !== 'sync');
        $this->check('Session cookies are secure in production', ! $production || config('session.secure'));

        $this->newLine();
        if ($this->failures > 0) {
            $this->error("PREFLIGHT FAILED: {$this->failures} check(s) need attention.");
            return self::FAILURE;
        }

        $this->info('PREFLIGHT PASSED: C-Net Store is ready.');
        return self::SUCCESS;
    }

    private function check(string $label, bool $passed, ?string $detail = null): void
    {
        $passed ? $this->pass($label, $detail) : $this->failCheck($label, $detail);
    }

    private function pass(string $label, ?string $detail = null): void
    {
        $this->line('<fg=green>PASS</> '.$label.($detail ? " ({$detail})" : ''));
    }

    private function failCheck(string $label, ?string $detail = null): void
    {
        $this->failures++;
        $safeDetail = $detail ? mb_substr(preg_replace('/password=[^ ]+/i', 'password=[hidden]', $detail), 0, 180) : null;
        $this->line('<fg=red>FAIL</> '.$label.($safeDetail ? " ({$safeDetail})" : ''));
    }
}
