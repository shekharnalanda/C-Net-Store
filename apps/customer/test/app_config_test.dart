import 'package:cnet_store_customer/src/core/app_config.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('production API uses HTTPS', () {
    final uri = Uri.parse(AppConfig.apiBaseUrl);
    expect(uri.scheme, 'https');
    expect(uri.host, 'cnetstore.mciedu.com');
  });
}
