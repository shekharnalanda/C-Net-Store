# C-Net Store mobile apps

The customer, seller and delivery applications are separate Flutter targets
sharing the same Laravel REST API contract.

## Production API

All three apps default to the live HTTPS endpoint:

`https://cnetstore.mciedu.com/api/v1`

Override it only for an intentional staging build:

```bash
flutter run --dart-define=API_BASE_URL=https://staging.example.com/api/v1
```

Authentication tokens are stored with `flutter_secure_storage`. The API client
sends `Accept: application/json` and a Bearer token, and removes an expired token
after a `401` response.

## Build targets

| App | Package | Purpose |
| --- | --- | --- |
| Customer | `cnet_store_customer` | Catalogue, account and cart |
| Seller | `cnet_store_seller` | Business, products and orders |
| Delivery | `cnet_store_delivery` | Availability, GPS, assignments and earnings |

The Mobile apps CI workflow generates the standard Android host project in an
isolated GitHub runner, analyzes each app, builds a production-connected debug
APK, and publishes each APK as a workflow artifact. The three apps remain
separate release targets.

## Release-only configuration

Do not commit signing keys, Firebase service-account files, private API keys, or
store credentials. Android release signing and Firebase/Maps provider files are
deployment secrets and must be supplied through the release environment. A
release build must keep the production API URL above and use separately
restricted Android application credentials for each app.
