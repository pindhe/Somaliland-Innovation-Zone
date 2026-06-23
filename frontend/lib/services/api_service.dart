import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';
import '../models/application.dart';
import '../models/admin.dart';
import '../models/course.dart';

class ApiService {
  String? _token;

  Future<void> loadToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('access_token');
  }

  Future<void> saveToken(String access, String refresh) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('access_token', access);
    await prefs.setString('refresh_token', refresh);
    _token = access;
  }

  Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('access_token');
    await prefs.remove('refresh_token');
    _token = null;
  }

  Map<String, String> _headers({bool json = true, bool auth = false}) {
    final headers = <String, String>{};
    if (json) headers['Content-Type'] = 'application/json';
    if (auth && _token != null) headers['Authorization'] = 'Bearer $_token';
    return headers;
  }

  Future<dynamic> _get(String path, {bool auth = false}) async {
    final res = await http.get(
      Uri.parse('${ApiConfig.baseUrl}$path'),
      headers: _headers(auth: auth),
    );
    return _handle(res);
  }

  Future<dynamic> _post(String path, Map<String, dynamic> body, {bool auth = false}) async {
    final res = await http.post(
      Uri.parse('${ApiConfig.baseUrl}$path'),
      headers: _headers(auth: auth),
      body: jsonEncode(body),
    );
    return _handle(res);
  }

  Future<dynamic> _patchMultipart(String path, Map<String, String> fields, {List<http.MultipartFile>? files}) async {
    final req = http.MultipartRequest('PATCH', Uri.parse('${ApiConfig.baseUrl}$path'));
    if (_token != null) req.headers['Authorization'] = 'Bearer $_token';
    req.fields.addAll(fields);
    if (files != null) req.files.addAll(files);
    final streamed = await req.send();
    final res = await http.Response.fromStream(streamed);
    return _handle(res);
  }

  Future<dynamic> _postMultipart(String path, Map<String, String> fields, {List<http.MultipartFile>? files}) async {
    final req = http.MultipartRequest('POST', Uri.parse('${ApiConfig.baseUrl}$path'));
    if (_token != null) req.headers['Authorization'] = 'Bearer $_token';
    req.fields.addAll(fields);
    if (files != null) req.files.addAll(files);
    final streamed = await req.send();
    final res = await http.Response.fromStream(streamed);
    return _handle(res);
  }

  dynamic _handle(http.Response res) {
    if (res.statusCode >= 200 && res.statusCode < 300) {
      if (res.body.isEmpty) return {};
      return jsonDecode(res.body);
    }
    try {
      final err = jsonDecode(res.body);
      throw Exception(err['detail'] ?? err.toString());
    } catch (_) {
      throw Exception('Request failed (${res.statusCode})');
    }
  }

  List<T> _list<T>(dynamic data, T Function(Map<String, dynamic>) fromJson) {
    if (data is List) return data.map((e) => fromJson(e as Map<String, dynamic>)).toList();
    if (data is Map && data['results'] is List) {
      return (data['results'] as List).map((e) => fromJson(e as Map<String, dynamic>)).toList();
    }
    return [];
  }

  // Auth
  Future<AdminUser> login(String username, String password) async {
    final data = await _post('/auth/login/', {'username': username, 'password': password});
    await saveToken(data['access'], data['refresh']);
    return AdminUser.fromJson(data['user']);
  }

  Future<AdminUser> getMe() async {
    final data = await _get('/auth/me/', auth: true);
    return AdminUser.fromJson(data);
  }

  Future<void> forgotPassword(String email) async {
    await _post('/auth/forgot-password/', {'email': email});
  }

  // Courses
  Future<List<Course>> getCourses({Map<String, String>? params}) async {
    var path = '/courses/';
    if (params != null && params.isNotEmpty) {
      path += '?${Uri(queryParameters: params).query}';
    }
    final data = await _get(path, auth: _token != null);
    return _list(data, Course.fromJson);
  }

  Future<List<Course>> getFeaturedCourses() async {
    final data = await _get('/courses/featured/');
    return _list(data, Course.fromJson);
  }

  Future<Course> getCourse(int id) async {
    final data = await _get('/courses/$id/');
    return Course.fromJson(data);
  }

  Future<Course> createCourse(Map<String, String> fields, {http.MultipartFile? image}) async {
    final data = await _postMultipart('/courses/', fields, files: image != null ? [image] : null);
    return Course.fromJson(data);
  }

  Future<Course> updateCourse(int id, Map<String, String> fields, {http.MultipartFile? image}) async {
    final data = await _patchMultipart('/courses/$id/', fields, files: image != null ? [image] : null);
    return Course.fromJson(data);
  }

  Future<void> publishCourse(int id) async => _post('/courses/$id/publish/', {}, auth: true);
  Future<void> archiveCourse(int id) async => _post('/courses/$id/archive/', {}, auth: true);

  // Applications
  Future<List<Application>> getApplications({Map<String, String>? params}) async {
    var path = '/applications/';
    if (params != null && params.isNotEmpty) {
      path += '?${Uri(queryParameters: params).query}';
    }
    final data = await _get(path, auth: true);
    return _list(data, Application.fromJson);
  }

  Future<Application> submitApplication(ApplicationFormData form) async {
    final data = await _post('/applications/', form.toJson());
    return Application.fromJson(data);
  }

  Future<void> approveApplication(int id, {String? notes}) async {
    await _post('/applications/$id/approve/', {'status': 'approved', 'admin_notes': notes ?? ''}, auth: true);
  }

  Future<void> rejectApplication(int id, {String? notes}) async {
    await _post('/applications/$id/reject/', {'status': 'rejected', 'admin_notes': notes ?? ''}, auth: true);
  }

  // Dashboard
  Future<DashboardStats> getDashboardStats() async {
    final data = await _get('/dashboard/stats/', auth: true);
    return DashboardStats.fromJson(data);
  }

  // Notifications
  Future<List<NotificationItem>> getNotifications() async {
    final data = await _get('/notifications/', auth: true);
    return _list(data, NotificationItem.fromJson);
  }

  Future<void> sendNotification(Map<String, dynamic> body) async {
    await _post('/notifications/', body, auth: true);
  }
}
