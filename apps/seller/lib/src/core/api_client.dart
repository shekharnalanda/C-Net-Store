import 'package:dio/dio.dart';

import 'app_config.dart';
import 'token_store.dart';

class ApiClient {
  ApiClient({TokenStore tokenStore = const TokenStore()})
      : _tokenStore = tokenStore {
    AppConfig.validateProduction();
    dio = Dio(
      BaseOptions(
        baseUrl: AppConfig.apiBaseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 25),
        sendTimeout: const Duration(seconds: 25),
        headers: const {'Accept': 'application/json'},
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
        'device_name': 'C-Net Store Seller',
      },
    );
    final data = response.data ?? <String, dynamic>{};
    final user = data['user'] as Map<String, dynamic>? ?? <String, dynamic>{};
    if (user['role'] != 'seller') {
      throw DioException(
        requestOptions: response.requestOptions,
        response: Response<Map<String, dynamic>>(
          requestOptions: response.requestOptions,
          statusCode: 403,
          data: const {'message': 'A Seller account is required.'},
        ),
      );
    }
    await _tokenStore.save(data['token'] as String);
    return data;
  }

  Future<void> logout() async {
    try {
      await dio.post<void>('/logout');
    } finally {
      await _tokenStore.clear();
    }
  }

  Future<Map<String, dynamic>> me() async =>
      (await dio.get<Map<String, dynamic>>('/me')).data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> dashboard() async =>
      (await dio.get<Map<String, dynamic>>('/seller/dashboard')).data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> businesses() async =>
      (await dio.get<Map<String, dynamic>>('/seller/businesses')).data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> createBusiness(
    Map<String, dynamic> payload,
  ) async =>
      (await dio.post<Map<String, dynamic>>(
        '/seller/businesses',
        data: payload,
      ))
          .data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> products() async =>
      (await dio.get<Map<String, dynamic>>('/seller/products')).data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> createProduct(
    Map<String, dynamic> payload,
  ) async =>
      (await dio.post<Map<String, dynamic>>(
        '/seller/products',
        data: payload,
      ))
          .data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> updateProduct(
    int productId,
    Map<String, dynamic> payload,
  ) async =>
      (await dio.patch<Map<String, dynamic>>(
        '/seller/products/$productId',
        data: payload,
      ))
          .data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> orders() async =>
      (await dio.get<Map<String, dynamic>>('/seller/orders')).data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> updateOrder(
    int orderId,
    String status, {
    String? note,
  }) async =>
      (await dio.patch<Map<String, dynamic>>(
        '/seller/orders/$orderId',
        data: {'status': status, 'note': note},
      ))
          .data ??
      <String, dynamic>{};
}
