import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/admin.dart';
import '../../providers/app_providers.dart';
import '../../services/api_service.dart';
import '../../widgets/common_widgets.dart';
import 'admin_shell.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  DashboardStats? stats;
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      stats = await context.read<ApiService>().getDashboardStats();
    } catch (_) {}
    if (mounted) setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    return AdminShell(
      title: 'Dashboard',
      child: loading
          ? const LoadingView()
          : stats == null
              ? const EmptyStateWidget(title: 'Failed to load', description: 'Could not fetch dashboard data.')
              : SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Wrap(
                        spacing: 12,
                        runSpacing: 12,
                        children: [
                          ElevatedButton.icon(onPressed: () => context.push('/admin/courses/new'), icon: const Icon(Icons.add), label: const Text('Add Course')),
                        ],
                      ),
                      const SizedBox(height: 20),
                      LayoutBuilder(builder: (context, c) {
                        final cols = c.maxWidth > 900 ? 4 : c.maxWidth > 600 ? 2 : 1;
                        final s = stats!.stats;
                        return GridView.count(
                          crossAxisCount: cols,
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          mainAxisSpacing: 12,
                          crossAxisSpacing: 12,
                          childAspectRatio: 1.6,
                          children: [
                            StatCard(label: 'Total Courses', value: '${s['total_courses']}', icon: Icons.menu_book, color: AppColors.primary),
                            StatCard(label: 'Active Courses', value: '${s['active_courses']}', icon: Icons.school, color: AppColors.accent),
                            StatCard(label: 'Free Trainings', value: '${s['free_trainings']}', icon: Icons.people, color: Colors.blue),
                            StatCard(label: 'Paid Courses', value: '${s['paid_courses']}', icon: Icons.payments, color: Colors.indigo),
                            StatCard(label: 'Total Applications', value: '${s['total_applications']}', icon: Icons.description, color: Colors.purple),
                            StatCard(label: 'Approved', value: '${s['approved_applications']}', icon: Icons.check_circle, color: AppColors.accent),
                            StatCard(label: 'Rejected', value: '${s['rejected_applications']}', icon: Icons.cancel, color: AppColors.error),
                            StatCard(label: 'Pending', value: '${s['pending_applications']}', icon: Icons.schedule, color: AppColors.warning),
                          ],
                        );
                      }),
                      const SizedBox(height: 24),
                      const Text('Recent Applications', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
                      const SizedBox(height: 12),
                      ...stats!.recentApplications.map((a) => Card(
                            child: ListTile(
                              title: Text(a.fullName),
                              subtitle: Text(a.course),
                              trailing: StatusChip(status: a.status),
                            ),
                          )),
                    ],
                  ),
                ),
    );
  }
}
