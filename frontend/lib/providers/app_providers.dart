import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
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
  static const _key = 'theme_mode';

  ThemeMode _mode = ThemeMode.system;
  bool _initialized = false;

  ThemeMode get mode => _mode;
  bool get initialized => _initialized;

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_key);
    if (saved != null) {
      _mode = ThemeMode.values.firstWhere(
        (m) => m.name == saved,
        orElse: () => ThemeMode.system,
      );
    }
    _initialized = true;
    notifyListeners();
  }

  Future<void> setMode(ThemeMode mode) async {
    _mode = mode;
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_key, mode.name);
  }

  void toggleLightDark() {
    final isDark = _mode == ThemeMode.dark;
    setMode(isDark ? ThemeMode.light : ThemeMode.dark);
  }

  IconData get toggleIcon {
    if (_mode == ThemeMode.dark) return Icons.light_mode_outlined;
    if (_mode == ThemeMode.light) return Icons.dark_mode_outlined;
    return Icons.brightness_auto_outlined;
  }

  String get modeLabel {
    switch (_mode) {
      case ThemeMode.light:
        return 'Light';
      case ThemeMode.dark:
        return 'Dark';
      case ThemeMode.system:
        return 'System';
    }
  }
}
