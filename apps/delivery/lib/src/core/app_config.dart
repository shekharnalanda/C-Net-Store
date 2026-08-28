abstract final class AppConfig {
  static const apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://cnetstore.mciedu.com/api/v1',
  );
  static const websiteUrl = 'https://cnetstore.mciedu.com';
  static const googleMapsApiKey = String.fromEnvironment('GOOGLE_MAPS_API_KEY');
  static const firebaseEnabled =
      bool.fromEnvironment('FIREBASE_ENABLED', defaultValue: false);
  static const supportPhone = '7004773247';
  static const supportEmail = 'mcieducationalgroup@gmail.com';

  static void validateProduction() {
    final uri = Uri.parse(apiBaseUrl);
    if (uri.scheme != 'https' || uri.host != 'cnetstore.mciedu.com') {
      throw StateError('A secure C-Net Store production API URL is required.');
    }
  }
}
