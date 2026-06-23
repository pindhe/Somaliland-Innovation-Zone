class ApiConfig {
  static const String baseUrl = String.fromEnvironment(
    'API_URL',
    defaultValue: 'http://localhost:8001/api',
  );

  static String get mediaBaseUrl {
    final uri = Uri.parse(baseUrl);
    return '${uri.scheme}://${uri.host}:${uri.port}';
  }
}
