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
        'device_name': 'C-Net Store Delivery',
      },
    );
    final data = response.data ?? <String, dynamic>{};
    final user = data['user'] as Map<String, dynamic>? ?? <String, dynamic>{};
    if (user['role'] != 'delivery_partner') {
      throw DioException(
        requestOptions: response.requestOptions,
        response: Response<Map<String, dynamic>>(
          requestOptions: response.requestOptions,
          statusCode: 403,
          data: const {'message': 'A Delivery Partner account is required.'},
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

  Future<Map<String, dynamic>> profile() async =>
      (await dio.get<Map<String, dynamic>>('/delivery/profile')).data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> assignments() async =>
      (await dio.get<Map<String, dynamic>>('/delivery/assignments')).data ??
      <String, dynamic>{};

  Future<Map<String, dynamic>> earnings() async =>
      (await dio.get<Map<String, dynamic>>('/delivery/earnings')).data ??
      <String, dynamic>{};

  Future<void> availability(
    bool online, {
    double? latitude,
    double? longitude,
  }) =>
      dio.patch<void>(
        '/delivery/availability',
        data: {
          'is_online': online,
          'latitude': latitude,
          'longitude': longitude,
        },
      );

  Future<void> location(
    int assignment,
    double latitude,
    double longitude,
    double? accuracy,
  ) =>
      dio.post<void>(
        '/delivery/assignments/$assignment/location',
        data: {
          'latitude': latitude,
          'longitude': longitude,
          'accuracy_meters': accuracy,
        },
      );

  Future<Map<String, dynamic>> updateAssignment(
    int id,
    String status, {
    String? pickupOtp,
    String? deliveryOtp,
    String? failureReason,
  }) async =>
      (await dio.patch<Map<String, dynamic>>(
        '/delivery/assignments/$id',
        data: {
          'status': status,
          'pickup_otp': pickupOtp,
          'delivery_otp': deliveryOtp,
          'failure_reason': failureReason,
        },
      ))
          .data ??
      <String, dynamic>{};
}
