import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import 'config/theme.dart';
import 'providers/app_providers.dart';
import 'services/api_service.dart';
import 'screens/student/home_screen.dart';
import 'screens/student/courses_screen.dart';
import 'screens/student/course_detail_screen.dart';
import 'screens/student/application_form_screen.dart';
import 'screens/student/success_screen.dart';
import 'screens/admin/login_screen.dart';
import 'screens/admin/dashboard_screen.dart';
import 'screens/admin/admin_courses_screen.dart';
import 'screens/admin/course_form_screen.dart';
import 'screens/admin/applications_screen.dart';
import 'screens/admin/notifications_screen.dart';
import 'screens/admin/forgot_password_screen.dart';

final _rootKey = GlobalKey<NavigatorState>();

GoRouter createRouter(AuthProvider auth) => GoRouter(
      navigatorKey: _rootKey,
      initialLocation: '/',
      refreshListenable: auth,
      redirect: (context, state) {
        final isAdminRoute = state.matchedLocation.startsWith('/admin');
        final isLogin = state.matchedLocation == '/admin/login' ||
            state.matchedLocation == '/admin/forgot-password';
        if (isAdminRoute && !isLogin && !auth.isAuthenticated) {
          return '/admin/login';
        }
        if (isLogin && auth.isAuthenticated) return '/admin/dashboard';
        return null;
      },
      routes: [
        GoRoute(path: '/', builder: (_, __) => const HomeScreen()),
        GoRoute(path: '/courses', builder: (_, __) => const CoursesScreen()),
        GoRoute(
          path: '/courses/:id',
          builder: (_, state) => CourseDetailScreen(courseId: int.parse(state.pathParameters['id']!)),
        ),
        GoRoute(
          path: '/apply/:id',
          builder: (_, state) => ApplicationFormScreen(courseId: int.parse(state.pathParameters['id']!)),
        ),
        GoRoute(path: '/apply/success', builder: (_, __) => const SuccessScreen()),
        GoRoute(path: '/admin/login', builder: (_, __) => const AdminLoginScreen()),
        GoRoute(path: '/admin/forgot-password', builder: (_, __) => const ForgotPasswordScreen()),
        GoRoute(path: '/admin/dashboard', builder: (_, __) => const DashboardScreen()),
        GoRoute(path: '/admin/courses', builder: (_, __) => const AdminCoursesScreen()),
        GoRoute(path: '/admin/courses/new', builder: (_, __) => const CourseFormScreen()),
        GoRoute(
          path: '/admin/courses/:id/edit',
          builder: (_, state) => CourseFormScreen(courseId: int.parse(state.pathParameters['id']!)),
        ),
        GoRoute(path: '/admin/applications', builder: (_, __) => const ApplicationsScreen()),
        GoRoute(path: '/admin/notifications', builder: (_, __) => const NotificationsScreen()),
      ],
    );

class SizsrApp extends StatefulWidget {
  const SizsrApp({super.key});

  @override
  State<SizsrApp> createState() => _SizsrAppState();
}

class _SizsrAppState extends State<SizsrApp> {
  late final ApiService _api;
  late final AuthProvider _auth;
  late final ThemeProvider _theme;

  @override
  void initState() {
    super.initState();
    _api = ApiService();
    _auth = AuthProvider(_api)..init();
    _theme = ThemeProvider()..init();
  }

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<ApiService>.value(value: _api),
        ChangeNotifierProvider<ThemeProvider>.value(value: _theme),
        ChangeNotifierProvider<AuthProvider>.value(value: _auth),
      ],
      child: Consumer2<ThemeProvider, AuthProvider>(
        builder: (context, theme, auth, _) {
          if (!theme.initialized || auth.loading) {
            return MaterialApp(
              debugShowCheckedModeBanner: false,
              theme: AppTheme.light(),
              darkTheme: AppTheme.dark(),
              home: const Scaffold(body: Center(child: CircularProgressIndicator())),
            );
          }
          return MaterialApp.router(
            title: 'SIZSR',
            debugShowCheckedModeBanner: false,
            theme: AppTheme.light(),
            darkTheme: AppTheme.dark(),
            themeMode: theme.mode,
            themeAnimationDuration: Duration.zero,
            routerConfig: createRouter(auth),
          );
        },
      ),
    );
  }
}
