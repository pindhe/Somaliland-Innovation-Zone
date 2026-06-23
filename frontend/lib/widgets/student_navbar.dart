import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../config/theme.dart';
import 'app_logo.dart';
import 'theme_toggle_button.dart';

/// Shared student portal navbar — clean, responsive, plain icons.
class StudentNavbar extends StatelessWidget implements PreferredSizeWidget {
  final String? title;
  final bool showBack;
  final VoidCallback? onBack;

  const StudentNavbar({
    super.key,
    this.title,
    this.showBack = false,
    this.onBack,
  });

  @override
  Size get preferredSize => const Size.fromHeight(64);

  @override
  Widget build(BuildContext context) {
    final palette = context.palette;
    final width = MediaQuery.sizeOf(context).width;
    final isMobile = width < 640;
    final location = GoRouterState.of(context).matchedLocation;
    final onCourses = location.startsWith('/courses');

    return Material(
      color: palette.navbarBackground,
      elevation: 0,
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: palette.navbarBackground,
          border: Border(bottom: BorderSide(color: palette.border.withValues(alpha: 0.8))),
        ),
        child: SafeArea(
          bottom: false,
          child: SizedBox(
            height: 64,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  if (showBack) ...[
                    IconButton(
                      onPressed: onBack ?? () => context.canPop() ? context.pop() : context.go('/'),
                      icon: Icon(Icons.arrow_back_ios_new_rounded, size: 20, color: palette.iconMuted),
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(minWidth: 36, minHeight: 36),
                      style: IconButton.styleFrom(backgroundColor: Colors.transparent),
                    ),
                    const SizedBox(width: 4),
                  ],
                  if (title != null)
                    Expanded(
                      child: Text(
                        title!,
                        style: TextStyle(fontSize: 17, fontWeight: FontWeight.w700, color: palette.textPrimary),
                        overflow: TextOverflow.ellipsis,
                      ),
                    )
                  else
                    AppLogo(onTap: () => context.go('/')),
                  const Spacer(),
                  if (!isMobile) ..._desktopActions(context, palette, onCourses),
                  if (isMobile)
                    IconButton(
                      onPressed: () => _openMobileMenu(context),
                      icon: Icon(Icons.menu_rounded, color: palette.iconMuted),
                      padding: EdgeInsets.zero,
                      style: IconButton.styleFrom(backgroundColor: Colors.transparent),
                    ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  List<Widget> _desktopActions(BuildContext context, AppPalette palette, bool onCourses) {
    return [
      const ThemeToggleButton(),
      const SizedBox(width: 4),
      TextButton(
        onPressed: () => context.go('/courses'),
        style: TextButton.styleFrom(
          foregroundColor: onCourses ? palette.link : palette.textPrimary,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        ),
        child: const Text('Courses'),
      ),
      const SizedBox(width: 8),
      ElevatedButton(
        onPressed: () => context.go('/courses'),
        style: ElevatedButton.styleFrom(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
          minimumSize: const Size(0, 40),
        ),
        child: const Text('Apply Now'),
      ),
      const SizedBox(width: 4),
    ];
  }

  void _openMobileMenu(BuildContext context) {
    final palette = context.palette;
    showModalBottomSheet(
      context: context,
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const AppLogo(),
              const SizedBox(height: 20),
              ListTile(
                leading: Icon(Icons.home_outlined, color: palette.iconMuted),
                title: const Text('Home'),
                onTap: () { Navigator.pop(ctx); context.go('/'); },
              ),
              ListTile(
                leading: Icon(Icons.menu_book_outlined, color: palette.iconMuted),
                title: const Text('Courses'),
                onTap: () { Navigator.pop(ctx); context.go('/courses'); },
              ),
              ListTile(
                leading: Icon(Icons.admin_panel_settings_outlined, color: palette.iconMuted),
                title: const Text('Admin Login'),
                onTap: () { Navigator.pop(ctx); context.go('/admin/login'); },
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  const ThemeToggleButton(),
                  const SizedBox(width: 8),
                  Text('Theme', style: TextStyle(color: palette.textSecondary)),
                ],
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () { Navigator.pop(ctx); context.go('/courses'); },
                child: const Text('Apply Now'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Wraps student pages with consistent navbar + themed background.
class StudentScaffold extends StatelessWidget {
  final String? title;
  final bool showBack;
  final VoidCallback? onBack;
  final Widget body;
  final bool scrollable;

  const StudentScaffold({
    super.key,
    this.title,
    this.showBack = false,
    this.onBack,
    required this.body,
    this.scrollable = true,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: context.palette.background,
      appBar: StudentNavbar(title: title, showBack: showBack, onBack: onBack),
      body: scrollable ? SingleChildScrollView(child: body) : body,
    );
  }
}
