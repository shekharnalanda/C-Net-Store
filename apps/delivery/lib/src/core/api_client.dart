import 'package:dio/dio.dart';

import 'token_store.dart';

class ApiClient {
  ApiClient() {
    dio = Dio(
      BaseOptions(
        baseUrl: const String.fromEnvironment(
          'API_BASE_URL',
          defaultValue: 'https://cnetstore.mciedu.com/api/v1',
        ),
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 20),
        headers: {'Accept': 'application/json'},
      ),
    );
    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await const TokenStore().read();
          if (token != null) options.headers['Authorization'] = 'Bearer $token';
          handler.next(options);
        },
        onError: (error, handler) async {
          if (error.response?.statusCode == 401)
            await const TokenStore().clear();
          handler.next(error);
        },
      ),
    );
  }
  late final Dio dio;
  Future<void> login(String login, String password) async {
    final response = await dio.post<Map<String, dynamic>>(
      '/login',
      data: {
        'login': login,
        'password': password,
        'device_name': 'C-Net Store Delivery',
      },
    );
    final data = response.data ?? {};
    final user = data['user'] as Map<String, dynamic>? ?? {};
    if (user['role'] != 'delivery_partner')
      throw DioException(
        requestOptions: response.requestOptions,
        response: Response(
          requestOptions: response.requestOptions,
          statusCode: 403,
          data: {'message': 'यह Delivery Partner account नहीं है।'},
        ),
      );
    await const TokenStore().save(data['token'] as String);
  }

  Future<Map<String, dynamic>> profile() async =>
      (await dio.get<Map<String, dynamic>>('/delivery/profile')).data ?? {};
  Future<Map<String, dynamic>> assignments() async =>
      (await dio.get<Map<String, dynamic>>('/delivery/assignments')).data ?? {};
  Future<Map<String, dynamic>> earnings() async =>
      (await dio.get<Map<String, dynamic>>('/delivery/earnings')).data ?? {};
  Future<void> availability(
    bool online, {
    double? latitude,
    double? longitude,
  }) => dio.patch(
    '/delivery/availability',
    data: {'is_online': online, 'latitude': latitude, 'longitude': longitude},
  );
  Future<void> location(
    int assignment,
    double latitude,
    double longitude,
    double? accuracy,
  ) => dio.post(
    '/delivery/assignments/$assignment/location',
    data: {
      'latitude': latitude,
      'longitude': longitude,
      'accuracy_meters': accuracy,
    },
  );
  Future<void> updateAssignment(
    int id,
    String status, {
    String? pickupOtp,
    String? deliveryOtp,
  }) => dio.patch(
    '/delivery/assignments/$id',
    data: {
      'status': status,
      'pickup_otp': pickupOtp,
      'delivery_otp': deliveryOtp,
    },
  );
}
