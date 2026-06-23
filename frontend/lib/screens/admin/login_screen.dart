import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/app_providers.dart';

class AdminLoginScreen extends StatefulWidget {
  const AdminLoginScreen({super.key});

  @override
  State<AdminLoginScreen> createState() => _AdminLoginScreenState();
}

class _AdminLoginScreenState extends State<AdminLoginScreen> {
  final _user = TextEditingController();
  final _pass = TextEditingController();
  bool remember = false;
  bool obscure = true;
  bool loading = false;

  Future<void> _login() async {
    setState(() => loading = true);
    final ok = await context.read<AuthProvider>().login(_user.text, _pass.text);
    if (mounted) {
      setState(() => loading = false);
      if (ok) context.go('/admin/dashboard');
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final isWide = MediaQuery.sizeOf(context).width > 800;

    return Scaffold(
      body: Row(
        children: [
          if (isWide)
            Expanded(
              child: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    colors: [AppColors.primary, AppColors.primaryDark, Color(0xFF1E3A8A)],
                  ),
                ),
                child: const Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.school, color: Colors.white, size: 64),
                    SizedBox(height: 20),
                    Text('SIZSR Admin Panel', style: TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.w800)),
                    SizedBox(height: 12),
                    Text('Manage courses, applications & notifications', style: TextStyle(color: Colors.white70)),
                  ],
                ),
              ),
            ),
          Expanded(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(32),
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 400),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Admin Login', style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800)),
                      const SizedBox(height: 8),
                      const Text('Sign in to access the dashboard', style: TextStyle(color: AppColors.textSecondary)),
                      const SizedBox(height: 32),
                      TextField(controller: _user, decoration: const InputDecoration(labelText: 'Username')),
                      const SizedBox(height: 16),
                      TextField(
                        controller: _pass,
                        obscureText: obscure,
                        decoration: InputDecoration(
                          labelText: 'Password',
                          suffixIcon: IconButton(
                            icon: Icon(obscure ? Icons.visibility_off : Icons.visibility),
                            onPressed: () => setState(() => obscure = !obscure),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          Checkbox(value: remember, onChanged: (v) => setState(() => remember = v ?? false)),
                          const Text('Remember me'),
                          const Spacer(),
                          TextButton(onPressed: () => context.push('/admin/forgot-password'), child: const Text('Forgot password?')),
                        ],
                      ),
                      if (auth.error != null) ...[
                        Text(auth.error!, style: const TextStyle(color: AppColors.error)),
                        const SizedBox(height: 8),
                      ],
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: loading ? null : _login,
                          child: loading ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Text('Sign In'),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Center(child: TextButton(onPressed: () => context.go('/'), child: const Text('Back to student portal'))),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
