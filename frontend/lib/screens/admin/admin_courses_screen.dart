import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../models/course.dart';
import '../../services/api_service.dart';
import '../../utils/constants.dart';
import '../../widgets/common_widgets.dart';
import 'admin_shell.dart';

class AdminCoursesScreen extends StatefulWidget {
  const AdminCoursesScreen({super.key});

  @override
  State<AdminCoursesScreen> createState() => _AdminCoursesScreenState();
}

class _AdminCoursesScreenState extends State<AdminCoursesScreen> {
  List<Course> courses = [];
  bool loading = true;
  String search = '';
  String statusFilter = '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      final params = statusFilter.isNotEmpty ? {'status': statusFilter} : null;
      courses = await context.read<ApiService>().getCourses(params: params);
    } catch (_) {}
    if (mounted) setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    final filtered = courses.where((c) => search.isEmpty || c.title.toLowerCase().contains(search.toLowerCase())).toList();

    return AdminShell(
      title: 'Course Management',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(child: TextField(decoration: const InputDecoration(prefixIcon: Icon(Icons.search), hintText: 'Search courses...'), onChanged: (v) => setState(() => search = v))),
              const SizedBox(width: 12),
              DropdownButton<String>(
                hint: const Text('Status'),
                value: statusFilter.isEmpty ? null : statusFilter,
                items: const [
                  DropdownMenuItem(value: '', child: Text('All')),
                  DropdownMenuItem(value: 'open', child: Text('Open')),
                  DropdownMenuItem(value: 'draft', child: Text('Draft')),
                  DropdownMenuItem(value: 'archived', child: Text('Archived')),
                ],
                onChanged: (v) { statusFilter = v ?? ''; _load(); },
              ),
              const SizedBox(width: 12),
              ElevatedButton.icon(onPressed: () => context.push('/admin/courses/new'), icon: const Icon(Icons.add), label: const Text('Add Course')),
            ],
          ),
          const SizedBox(height: 20),
          Expanded(
            child: loading
                ? const LoadingView()
                : filtered.isEmpty
                    ? const EmptyStateWidget(title: 'No courses', description: 'Create your first training program.')
                    : ListView.separated(
                        itemCount: filtered.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 8),
                        itemBuilder: (_, i) {
                          final c = filtered[i];
                          return Card(
                            child: ListTile(
                              title: Text(c.title, style: const TextStyle(fontWeight: FontWeight.w600)),
                              subtitle: Text('${c.category} • ${c.trainingType} • ${formatDate(c.startDate)}'),
                              trailing: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  StatusChip(status: c.status),
                                  PopupMenuButton<String>(
                                    onSelected: (action) async {
                                      final api = context.read<ApiService>();
                                      if (action == 'edit') context.push('/admin/courses/${c.id}/edit');
                                      if (action == 'publish') { await api.publishCourse(c.id); _load(); }
                                      if (action == 'archive') { await api.archiveCourse(c.id); _load(); }
                                    },
                                    itemBuilder: (_) => [
                                      const PopupMenuItem(value: 'edit', child: Text('Edit')),
                                      if (c.status != 'open') const PopupMenuItem(value: 'publish', child: Text('Publish')),
                                      if (c.status != 'archived') const PopupMenuItem(value: 'archive', child: Text('Archive')),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }
}
