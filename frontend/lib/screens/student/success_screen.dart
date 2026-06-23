import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../config/theme.dart';
import '../../widgets/student_navbar.dart';

class SuccessScreen extends StatelessWidget {
  const SuccessScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final palette = context.palette;
    return Scaffold(
      backgroundColor: palette.background,
      appBar: const StudentNavbar(showBack: true),
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
                  child: const Icon(Icons.check_circle_outlined, color: AppColors.accent, size: 72),
                ),
                const SizedBox(height: 24),
                Text('Application Submitted!', style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: palette.textPrimary)),
                const SizedBox(height: 20),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      children: [
                        Text('Thank you for your application.', style: TextStyle(height: 1.6, color: palette.textPrimary)),
                        const SizedBox(height: 12),
                        Text(
                          'Your registration has been successfully submitted and is currently under review by our administration team.',
                          style: TextStyle(height: 1.6, color: palette.textSecondary),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'You will be contacted soon regarding the outcome of your application. Please wait for further communication.',
                          style: TextStyle(height: 1.6, color: palette.textSecondary),
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
                      icon: const Icon(Icons.home_outlined),
                      label: const Text('Return Home'),
                    ),
                    OutlinedButton.icon(
                      onPressed: () => context.go('/courses'),
                      icon: const Icon(Icons.menu_book_outlined),
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
