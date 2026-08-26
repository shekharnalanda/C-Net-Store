import 'package:dio/dio.dart';
import 'token_store.dart';

class ApiClient {
  ApiClient() { dio = Dio(BaseOptions(baseUrl: const String.fromEnvironment('API_BASE_URL', defaultValue: 'https://cnetstore.mciedu.com/api/v1'), connectTimeout: const Duration(seconds: 15), receiveTimeout: const Duration(seconds: 20), headers: {'Accept': 'application/json'})); dio.interceptors.add(InterceptorsWrapper(onRequest: (options, handler) async { final token = await const TokenStore().read(); if (token != null) options.headers['Authorization'] = 'Bearer $token'; handler.next(options); }, onError: (error, handler) async { if (error.response?.statusCode == 401) await const TokenStore().clear(); handler.next(error); })); }
  late final Dio dio;
  Future<Map<String, dynamic>> login(String login, String password) async { final response = await dio.post<Map<String, dynamic>>('/login', data: {'login': login, 'password': password, 'device_name': 'C-Net Store Seller'}); final data = response.data ?? {}; final user = data['user'] as Map<String, dynamic>? ?? {}; if (user['role'] != 'seller') throw DioException(requestOptions: response.requestOptions, response: Response(requestOptions: response.requestOptions, statusCode: 403, data: {'message': 'यह Seller account नहीं है।'})); await const TokenStore().save(data['token'] as String); return data; }
  Future<List<dynamic>> businesses() async => ((await dio.get<Map<String, dynamic>>('/seller/businesses')).data?['data'] as List<dynamic>? ?? []);
  Future<Map<String, dynamic>> products() async => (await dio.get<Map<String, dynamic>>('/seller/products')).data ?? {};
  Future<Map<String, dynamic>> orders() async => (await dio.get<Map<String, dynamic>>('/seller/orders')).data ?? {};
}

