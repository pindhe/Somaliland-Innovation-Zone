import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../providers/app_providers.dart';

class AdminShell extends StatelessWidget {
  final String title;
  final Widget child;

  const AdminShell({super.key, required this.title, required this.child});

  @override
  Widget build(BuildContext context) {
    final isWide = MediaQuery.sizeOf(context).width > 768;
    final location = GoRouterState.of(context).matchedLocation;

    final items = [
      _NavItem('/admin/dashboard', Icons.dashboard, 'Dashboard'),
      _NavItem('/admin/courses', Icons.menu_book, 'Courses'),
      _NavItem('/admin/applications', Icons.description, 'Applications'),
      _NavItem('/admin/notifications', Icons.notifications, 'Notifications'),
    ];

    final drawer = NavigationDrawer(
      selectedIndex: items.indexWhere((i) => location.startsWith(i.path)).clamp(0, items.length - 1),
      onDestinationSelected: (i) {
        context.go(items[i].path);
        if (!isWide) Navigator.pop(context);
      },
      children: [
        Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(color: Theme.of(context).colorScheme.primary, borderRadius: BorderRadius.circular(8)),
                child: const Icon(Icons.school, color: Colors.white, size: 20),
              ),
              const SizedBox(width: 10),
              const Text('SIZSR Admin', style: TextStyle(fontWeight: FontWeight.w800)),
            ],
          ),
        ),
        ...items.map((i) => NavigationDrawerDestination(icon: Icon(i.icon), label: Text(i.label))),
        const Divider(),
        ListTile(
          leading: const Icon(Icons.logout, color: Colors.red),
          title: const Text('Logout', style: TextStyle(color: Colors.red)),
          onTap: () => context.read<AuthProvider>().logout(),
        ),
      ],
    );

    return Scaffold(
      appBar: AppBar(
        title: Text(title),
        actions: [
          IconButton(
            icon: Icon(context.watch<ThemeProvider>().mode == ThemeMode.dark ? Icons.light_mode : Icons.dark_mode),
            onPressed: () => context.read<ThemeProvider>().toggle(),
          ),
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
}

class _NavItem {
  final String path;
  final IconData icon;
  final String label;
  _NavItem(this.path, this.icon, this.label);
}
