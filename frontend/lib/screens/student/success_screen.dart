import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../config/theme.dart';

class SuccessScreen extends StatelessWidget {
  const SuccessScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 500),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: AppColors.accent.withValues(alpha: 0.15),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(Icons.check_circle, color: AppColors.accent, size: 72),
                ),
                const SizedBox(height: 24),
                const Text('Application Submitted!', style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800)),
                const SizedBox(height: 20),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      children: const [
                        Text('Thank you for your application.', style: TextStyle(height: 1.6)),
                        SizedBox(height: 12),
                        Text(
                          'Your registration has been successfully submitted and is currently under review by our administration team.',
                          style: TextStyle(height: 1.6, color: AppColors.textSecondary),
                        ),
                        SizedBox(height: 12),
                        Text(
                          'You will be contacted soon regarding the outcome of your application. Please wait for further communication.',
                          style: TextStyle(height: 1.6, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                Wrap(
                  spacing: 12,
                  runSpacing: 12,
                  alignment: WrapAlignment.center,
                  children: [
                    ElevatedButton.icon(
                      onPressed: () => context.go('/'),
                      icon: const Icon(Icons.home),
                      label: const Text('Return Home'),
                    ),
                    OutlinedButton.icon(
                      onPressed: () => context.go('/courses'),
                      icon: const Icon(Icons.menu_book),
                      label: const Text('Browse More Courses'),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
