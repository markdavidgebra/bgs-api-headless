<?php

namespace App\Console\Commands;

use App\Mail\AdminLowStockAlertMail;
use App\Models\Product;
use App\Support\AdminNotificationRecipients;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAdminLowStockDigestCommand extends Command
{
    protected $signature = 'notifications:admin-low-stock';

    protected $description = 'Email admins with inventory permission about low/out-of-stock products';

    public function handle(): int
    {
        $emails = AdminNotificationRecipients::emailsForPermission('products.manage');
        if ($emails === []) {
            $this->warn('No admin recipients with products.manage permission.');

            return self::SUCCESS;
        }

        $products = Product::query()
            ->where('status', 'active')
            ->where(function ($q): void {
                $q->where('stock_quantity', '<=', 0)
                    ->orWhereColumn('stock_quantity', '<=', 'minimum_stock_alert');
            })
            ->orderBy('name')
            ->get();

        if ($products->isEmpty()) {
            $this->info('No low-stock products.');

            return self::SUCCESS;
        }

        Mail::to($emails)->send(new AdminLowStockAlertMail($products));
        $this->info('Low-stock digest sent to '.count($emails).' recipient(s).');

        return self::SUCCESS;
    }
}
