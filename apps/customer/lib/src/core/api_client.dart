import 'package:dio/dio.dart';

import 'app_config.dart';
import 'token_store.dart';

class ApiClient {
  ApiClient({TokenStore tokenStore = const TokenStore()})
    : _tokenStore = tokenStore {
    dio = Dio(
      BaseOptions(
        baseUrl: AppConfig.apiBaseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 20),
        headers: {'Accept': 'application/json'},
      ),
    );
    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _tokenStore.read();
          if (token != null) options.headers['Authorization'] = 'Bearer $token';
          handler.next(options);
        },
        onError: (error, handler) async {
          if (error.response?.statusCode == 401) await _tokenStore.clear();
          handler.next(error);
        },
      ),
    );
  }

  late final Dio dio;
  final TokenStore _tokenStore;

  Future<Map<String, dynamic>> login(String login, String password) async {
    final response = await dio.post<Map<String, dynamic>>(
      '/login',
      data: {
        'login': login,
        'password': password,
        'device_name': 'C-Net Store Customer',
      },
    );
    final data = response.data ?? <String, dynamic>{};
    await _tokenStore.save(data['token'] as String);
    return data;
  }

  Future<List<dynamic>> banners() async =>
      ((await dio.get<Map<String, dynamic>>('/banners')).data?['data']
          as List<dynamic>? ??
      []);
  Future<List<dynamic>> catalog({String? type, String? query}) async =>
      ((await dio.get<Map<String, dynamic>>(
            '/customer/catalog',
            queryParameters: {'type': type, 'q': query},
          )).data?['data']
          as List<dynamic>? ??
      []);
}
