import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../services/api_service.dart';
import '../../widgets/theme_toggle_button.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _email = TextEditingController();
  bool sent = false;
  bool loading = false;

  Future<void> _submit() async {
    setState(() => loading = true);
    try {
      await context.read<ApiService>().forgotPassword(_email.text);
    } catch (_) {}
    if (mounted) setState(() { loading = false; sent = true; });
  }

  @override
  Widget build(BuildContext context) {
    final palette = context.palette;
    return Scaffold(
      backgroundColor: palette.background,
      appBar: AppBar(
        title: const Text('Forgot Password'),
        actions: const [ThemeToggleButton(), SizedBox(width: 8)],
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 400),
            child: sent
                ? Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.mark_email_read_outlined, size: 48, color: AppColors.primary),
                      const SizedBox(height: 16),
                      Text('If an account exists with this email, a reset link has been sent.', textAlign: TextAlign.center, style: TextStyle(color: palette.textSecondary)),
                      const SizedBox(height: 20),
                      ElevatedButton(onPressed: () => context.go('/admin/login'), child: const Text('Back to Login')),
                    ],
                  )
                : Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      TextField(
                        controller: _email,
                        decoration: const InputDecoration(
                          labelText: 'Email',
                          prefixIcon: Icon(Icons.email_outlined),
                        ),
                      ),
                      const SizedBox(height: 20),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: loading ? null : _submit,
                          child: loading ? const CircularProgressIndicator() : const Text('Send Reset Link'),
                        ),
                      ),
                    ],
                  ),
          ),
        ),
      ),
    );
  }
}
