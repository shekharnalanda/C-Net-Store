import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class TokenStore {
  const TokenStore();
  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );
  static const _key = 'cnet_store_customer_token';

  Future<void> save(String token) => _storage.write(key: _key, value: token);
  Future<String?> read() => _storage.read(key: _key);
  Future<bool> hasToken() async => (await read())?.isNotEmpty == true;
  Future<void> clear() => _storage.delete(key: _key);
}
