import 'package:flutter/material.dart';
import '../models/admin.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService api;
  AdminUser? user;
  bool loading = true;
  String? error;

  AuthProvider(this.api);

  bool get isAuthenticated => user != null;

  Future<void> init() async {
    loading = true;
    notifyListeners();
    try {
      await api.loadToken();
      user = await api.getMe();
    } catch (_) {
      user = null;
      await api.clearToken();
    } finally {
      loading = false;
      notifyListeners();
    }
  }

  Future<bool> login(String username, String password) async {
    error = null;
    try {
      user = await api.login(username, password);
      notifyListeners();
      return true;
    } catch (e) {
      error = e.toString().replaceFirst('Exception: ', '');
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await api.clearToken();
    user = null;
    notifyListeners();
  }
}

class ThemeProvider extends ChangeNotifier {
  ThemeMode mode = ThemeMode.light;

  void toggle() {
    mode = mode == ThemeMode.light ? ThemeMode.dark : ThemeMode.light;
    notifyListeners();
  }
}
