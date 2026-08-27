import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class TokenStore {
  const TokenStore();
  static const storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );
  static const key = 'cnet_store_delivery_token';
  Future<void> save(String value) => storage.write(key: key, value: value);
  Future<String?> read() => storage.read(key: key);
  Future<bool> hasToken() async => (await read())?.isNotEmpty == true;
  Future<void> clear() => storage.delete(key: key);
}
