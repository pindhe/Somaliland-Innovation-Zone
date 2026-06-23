import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/app_providers.dart';
import '../../widgets/app_logo.dart';
import '../../widgets/theme_toggle_button.dart';

class AdminShell extends StatelessWidget {
  final String title;
  final Widget child;

  const AdminShell({super.key, required this.title, required this.child});

  @override
  Widget build(BuildContext context) {
    final isWide = MediaQuery.sizeOf(context).width > 768;
    final location = GoRouterState.of(context).matchedLocation;
    final palette = context.palette;

    final items = [
      _NavItem('/admin/dashboard', Icons.dashboard_outlined, 'Dashboard'),
      _NavItem('/admin/courses', Icons.menu_book_outlined, 'Courses'),
      _NavItem('/admin/applications', Icons.description_outlined, 'Applications'),
      _NavItem('/admin/notifications', Icons.notifications_outlined, 'Notifications'),
    ];

    final selectedIndex = items.indexWhere((i) => location.startsWith(i.path)).clamp(0, items.length - 1);

    final drawer = NavigationDrawer(
      selectedIndex: selectedIndex,
      onDestinationSelected: (i) {
        context.go(items[i].path);
        if (!isWide) Navigator.pop(context);
      },
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 20, 16, 12),
          child: AppLogo(iconSize: 26, onTap: () => context.go('/admin/dashboard')),
        ),
        ...items.map((i) => NavigationDrawerDestination(
              icon: Icon(i.icon),
              selectedIcon: Icon(_filledIcon(i.icon)),
              label: Text(i.label),
            )),
        const Divider(),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8),
          child: ListTile(
            leading: const Icon(Icons.logout_outlined, color: AppColors.error),
            title: const Text('Logout', style: TextStyle(color: AppColors.error)),
            onTap: () => context.read<AuthProvider>().logout(),
          ),
        ),
      ],
    );

    return Scaffold(
      backgroundColor: palette.background,
      appBar: AppBar(
        title: Text(title),
        actions: const [
          ThemeToggleButton(),
          SizedBox(width: 8),
        ],
      ),
      drawer: isWide ? null : Drawer(child: drawer),
      body: Row(
        children: [
          if (isWide) SizedBox(width: 260, child: drawer),
          Expanded(child: Padding(padding: const EdgeInsets.all(20), child: child)),
        ],
      ),
    );
  }

  IconData _filledIcon(IconData outlined) {
    if (outlined == Icons.dashboard_outlined) return Icons.dashboard;
    if (outlined == Icons.menu_book_outlined) return Icons.menu_book;
    if (outlined == Icons.description_outlined) return Icons.description;
    if (outlined == Icons.notifications_outlined) return Icons.notifications;
    return outlined;
  }
}

class _NavItem {
  final String path;
  final IconData icon;
  final String label;
  _NavItem(this.path, this.icon, this.label);
}
