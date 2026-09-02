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
        'device_name': 'C-Net Store Customer',
      },
    );
    final data = response.data ?? <String, dynamic>{};
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

  Future<List<dynamic>> banners() async =>
      ((await dio.get<Map<String, dynamic>>('/banners')).data?['data']
          as List<dynamic>? ??
      <dynamic>[]);

  Future<Map<String, dynamic>> catalog({
    String? type,
    int? categoryId,
    String? query,
  }) async =>
      (await dio.get<Map<String, dynamic>>(
        '/customer/catalog',
        queryParameters: {
          if (type != null) 'type': type,
          if (categoryId != null) 'category_id': categoryId,
          if (query != null && query.isNotEmpty) 'q': query,
        },
      ))
          .data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> cart(int cartId) async =>
      (await dio.get<Map<String, dynamic>>('/customer/carts/$cartId')).data ??
      <String, dynamic>{};

  Future<List<dynamic>> carts() async =>
      ((await dio.get<Map<String, dynamic>>('/customer/carts')).data?['data']
          as List<dynamic>? ??
      <dynamic>[]);

  Future<Map<String, dynamic>> addCartItem(
    Map<String, dynamic> payload,
  ) async =>
      (await dio.post<Map<String, dynamic>>(
        '/customer/cart/items',
        data: payload,
      ))
          .data ??
      <String, dynamic>{};

  Future<void> removeCartItem(int cartId, int itemId) =>
      dio.delete<void>('/customer/carts/$cartId/items/$itemId');

  Future<Map<String, dynamic>> updateCartItem(
    int cartId,
    int itemId,
    int quantity,
  ) async =>
      (await dio.patch<Map<String, dynamic>>(
        '/customer/carts/$cartId/items/$itemId',
        data: {'quantity': quantity},
      ))
          .data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> checkout(
    Map<String, dynamic> payload,
  ) async =>
      (await dio.post<Map<String, dynamic>>(
        '/customer/checkout',
        data: payload,
      ))
          .data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> orders() async =>
      (await dio.get<Map<String, dynamic>>('/customer/orders')).data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> createPayment(int orderId) async =>
      (await dio.post<Map<String, dynamic>>(
        '/customer/orders/$orderId/payment',
      ))
          .data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> verifyPayment(
    int orderId,
    Map<String, dynamic> payload,
  ) async =>
      (await dio.post<Map<String, dynamic>>(
        '/customer/orders/$orderId/payment/verify',
        data: payload,
      ))
          .data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> addresses() async =>
      (await dio.get<Map<String, dynamic>>('/customer/addresses')).data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> saveAddress(
    Map<String, dynamic> payload,
  ) async =>
      (await dio.post<Map<String, dynamic>>(
        '/customer/addresses',
        data: payload,
      ))
          .data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> wishlist() async =>
      (await dio.get<Map<String, dynamic>>('/customer/wishlist')).data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> toggleWishlist(int productId) async =>
      (await dio.post<Map<String, dynamic>>(
        '/customer/wishlist/$productId',
      ))
          .data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> supportTickets() async =>
      (await dio.get<Map<String, dynamic>>('/customer/support')).data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> createSupportTicket(
    Map<String, dynamic> payload,
  ) async =>
      (await dio.post<Map<String, dynamic>>(
        '/customer/support',
        data: payload,
      ))
          .data ??
      <String, dynamic>{};
}
