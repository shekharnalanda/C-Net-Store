<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class LegalContentSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'meta_description' => 'How C-Net Store collects, uses and protects customer, seller and delivery partner information.',
                'content' => "C-Net Store respects your privacy. We collect account, contact, address, order, payment-reference, device and delivery information only to operate the marketplace, process online payments, provide support, prevent fraud and meet legal obligations.\n\nPayment card or UPI credentials are handled by the authorised payment provider and are not stored by C-Net Store. Access to personal data is restricted to authorised staff, sellers and delivery partners only as required to fulfil an order.\n\nYou may request correction or deletion of eligible personal information by contacting shekharnalanda@gmail.com or 7782801846. Certain transaction records may be retained where required for accounting, fraud prevention or law.",
            ],
            [
                'title' => 'Terms and Conditions',
                'slug' => 'terms-and-conditions',
                'meta_description' => 'Terms governing use of the C-Net Store marketplace and applications.',
                'content' => "C-Net Store is a local multi-vendor marketplace for shopping, grocery and food delivery in supported service areas. By using the website or apps, you agree to provide accurate information and use the service lawfully.\n\nProduct availability, preparation time and delivery estimates may vary by seller and location. Prices, taxes, fees and the payable total are shown before payment. Cash on Delivery is not available; orders require supported online payment.\n\nC-Net Store may suspend accounts, listings or transactions involving misuse, fraud, unsafe products or violation of marketplace rules.",
            ],
            [
                'title' => 'Cancellation and Refund Policy',
                'slug' => 'cancellation-refund-policy',
                'meta_description' => 'C-Net Store order cancellation and online payment refund policy.',
                'content' => "Cancellation eligibility depends on the order stage and product type. An order may generally be cancelled before the seller begins preparation or dispatch. Food and customised items may not be cancellable after preparation starts.\n\nApproved refunds are returned to the original online payment method. Bank or payment-provider processing time may apply after C-Net Store initiates the refund. Duplicate payments, failed orders with captured payment and verified non-delivery will be investigated through support.\n\nRaise a support request with the order number and payment reference for assistance.",
            ],
            [
                'title' => 'Shipping and Delivery Policy',
                'slug' => 'shipping-delivery-policy',
                'meta_description' => 'Local delivery coverage, timing and responsibilities for C-Net Store orders.',
                'content' => "C-Net Store currently serves supported locations around Bihar Sharif. Delivery availability, minimum order, fee and estimated time depend on the seller outlet, service radius, traffic, weather and product preparation.\n\nCustomers must provide a complete reachable address and phone number. A delivery may be delayed or cancelled when the address is outside coverage, unsafe to access or the customer cannot be contacted.\n\nOrders may be delivered by an approved C-Net delivery partner or by the seller, as shown for the order.",
            ],
            [
                'title' => 'Seller Marketplace Terms',
                'slug' => 'seller-marketplace-terms',
                'meta_description' => 'Core listing, fulfilment and settlement responsibilities for C-Net Store sellers.',
                'content' => "Only approved sellers may publish products on C-Net Store. Sellers are responsible for accurate product names, descriptions, prices, taxes, stock, licences and legally compliant goods.\n\nSellers must accept or reject orders promptly, maintain inventory, package products safely and follow the selected delivery workflow. Prohibited, counterfeit, expired or misleading listings may be removed and the seller account may be suspended.\n\nCommission, refunds and settlement adjustments are calculated according to the seller configuration and verified order records.",
            ],
        ];

        foreach ($pages as $page) {
            CmsPage::firstOrCreate(
                ['slug' => $page['slug']],
                [...$page, 'is_published' => true, 'published_at' => now()]
            );
        }
    }
}
