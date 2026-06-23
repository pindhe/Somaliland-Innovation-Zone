import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../models/course.dart';
import '../../services/api_service.dart';
import '../../utils/constants.dart';
import '../../widgets/common_widgets.dart';

import '../../widgets/student_navbar.dart';

class CoursesScreen extends StatefulWidget {
  const CoursesScreen({super.key});

  @override
  State<CoursesScreen> createState() => _CoursesScreenState();
}

class _CoursesScreenState extends State<CoursesScreen> {
  List<Course> courses = [];
  bool loading = true;
  String search = '';
  String category = '';
  String trainingType = '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      final params = <String, String>{};
      if (category.isNotEmpty) params['category'] = category;
      if (trainingType.isNotEmpty) params['training_type'] = trainingType;
      courses = await context.read<ApiService>().getCourses(params: params.isEmpty ? null : params);
    } catch (_) {
      courses = [];
    }
    if (mounted) setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    final filtered = courses.where((c) {
      if (search.isEmpty) return true;
      return c.title.toLowerCase().contains(search.toLowerCase()) ||
          c.description.toLowerCase().contains(search.toLowerCase());
    }).toList();

    final width = MediaQuery.sizeOf(context).width;
    final cols = width > 900 ? 3 : width > 600 ? 2 : 1;

    return Scaffold(
      appBar: const StudentNavbar(title: 'Available Courses', showBack: true),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                TextField(
                  decoration: const InputDecoration(prefixIcon: Icon(Icons.search), hintText: 'Search courses...'),
                  onChanged: (v) => setState(() => search = v),
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        decoration: const InputDecoration(labelText: 'Category'),
                        initialValue: category.isEmpty ? null : category,
                        items: [const DropdownMenuItem(value: '', child: Text('All Categories')), ...courseCategories.map((c) => DropdownMenuItem(value: c, child: Text(c)))],
                        onChanged: (v) { category = v ?? ''; _load(); },
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        decoration: const InputDecoration(labelText: 'Type'),
                        initialValue: trainingType.isEmpty ? null : trainingType,
                        items: const [
                          DropdownMenuItem(value: '', child: Text('All Types')),
                          DropdownMenuItem(value: 'free', child: Text('Free')),
                          DropdownMenuItem(value: 'paid', child: Text('Paid')),
                        ],
                        onChanged: (v) { trainingType = v ?? ''; _load(); },
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          Expanded(
            child: loading
                ? const LoadingView()
                : filtered.isEmpty
                    ? const EmptyStateWidget(title: 'No courses found', description: 'Try adjusting your filters.', icon: Icons.book_outlined)
                    : GridView.builder(
                        padding: const EdgeInsets.all(16),
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: cols,
                          mainAxisSpacing: 16,
                          crossAxisSpacing: 16,
                          childAspectRatio: 0.72,
                        ),
                        itemCount: filtered.length,
                        itemBuilder: (_, i) => CourseCard(
                          course: filtered[i],
                          onTap: () => context.push('/courses/${filtered[i].id}'),
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}
