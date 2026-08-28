# C-Net Store Mobile Apps

This directory contains three independent Flutter applications:

- customer: marketplace, cart, checkout, Razorpay payment, orders and account.
- seller: business dashboard, products, inventory and order processing.
- delivery: availability, assignments, GPS, pickup/delivery OTP and earnings.

Production API: https://cnetstore.mciedu.com/api/v1

## Generate platform runners

Flutter SDK is the source of truth for Android and iOS runner files. From the
repository root run:

    bash apps/tool/prepare_mobile_apps.sh

The script generates Android/iOS runners, installs dependencies, formats,
analyzes and tests all three apps.

Recommended application IDs:

- Customer: com.mciedu.cnetstore.customer
- Seller: com.mciedu.cnetstore.seller
- Delivery: com.mciedu.cnetstore.delivery

## Secure production configuration

Copy mobile_env.example.json to mobile_env.production.json. The production
file is ignored by Git.

- RAZORPAY_KEY_ID: live public Key ID only. Never include the Key Secret.
- GOOGLE_MAPS_API_KEY: package-restricted Android/iOS Maps key.
- FIREBASE_ENABLED: true only after Firebase native files are installed.

Build example:

    flutter build appbundle --release --dart-define-from-file=../mobile_env.production.json

Razorpay payment signatures are always verified by Laravel. Razorpay Key
Secret and webhook secret remain only on the production server.

## Console-dependent release steps

1. Generate an Android signing keystore outside Git.
2. Register restricted Maps keys for the three package IDs.
3. Register three Firebase Android apps and three iOS bundle IDs.
4. Keep google-services.json and GoogleService-Info.plist outside Git.
5. Configure APNs and Apple signing on macOS.
6. Upload signed AAB files to Google Play Console.

GitHub Actions generates temporary runners, analyzes, tests and builds debug
APK artifacts for every mobile change.
