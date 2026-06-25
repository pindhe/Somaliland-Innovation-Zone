import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/course.dart';
import '../../services/api_service.dart';
import '../../utils/constants.dart';
import '../../widgets/common_widgets.dart';

import '../../widgets/student_navbar.dart';

class CourseDetailScreen extends StatefulWidget {
  final int courseId;
  const CourseDetailScreen({super.key, required this.courseId});

  @override
  State<CourseDetailScreen> createState() => _CourseDetailScreenState();
}

class _CourseDetailScreenState extends State<CourseDetailScreen> {
  Course? course;
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      course = await context.read<ApiService>().getCourse(widget.courseId);
    } catch (_) {}
    if (mounted) setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    if (loading) return const Scaffold(body: LoadingView());
    if (course == null) {
      return Scaffold(
        appBar: const StudentNavbar(title: 'Course', showBack: true),
        body: const EmptyStateWidget(title: 'Course not found', description: 'This course may no longer be available.'),
      );
    }

    final c = course!;
    final isWide = MediaQuery.sizeOf(context).width > 800;
    final palette = context.palette;

    return Scaffold(
      backgroundColor: palette.background,
      appBar: StudentNavbar(title: c.title, showBack: true),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: isWide
            ? Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(flex: 2, child: _mainContent(context, c)),
                  const SizedBox(width: 24),
                  SizedBox(width: 320, child: _sidebar(context, c)),
                ],
              )
            : Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _mainContent(context, c),
                  const SizedBox(height: 20),
                  _sidebar(context, c),
                ],
              ),
      ),
    );
  }

  Widget _mainContent(BuildContext context, Course c) {
    final palette = context.palette;
    return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: SizedBox(
              height: 220,
              width: double.infinity,
              child: c.imageUrl != null
                  ? CachedNetworkImage(imageUrl: c.imageUrl!, fit: BoxFit.cover)
                  : Container(color: AppColors.primary.withValues(alpha: 0.1), child: const Icon(Icons.menu_book, size: 64, color: AppColors.primary)),
            ),
          ),
          const SizedBox(height: 16),
          Wrap(spacing: 8, children: [
            Chip(label: Text(c.category)),
            Chip(label: Text(c.isFree ? 'Free Training' : 'Paid Course'), backgroundColor: c.isFree ? AppColors.accent.withValues(alpha: 0.15) : null),
          ]),
          const SizedBox(height: 12),
          Text(c.title, style: TextStyle(fontSize: 26, fontWeight: FontWeight.w800, color: palette.textPrimary)),
          const SizedBox(height: 12),
          Text(c.description, style: TextStyle(height: 1.6, color: palette.textSecondary)),
          if (c.outcomeList.isNotEmpty) ...[
            const SizedBox(height: 24),
            const Text('Learning Outcomes', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
            const SizedBox(height: 8),
            ...c.outcomeList.map((o) => Padding(
                  padding: const EdgeInsets.only(bottom: 6),
                  child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    const Icon(Icons.check_circle, color: AppColors.accent, size: 18),
                    const SizedBox(width: 8),
                    Expanded(child: Text(o)),
                  ]),
                )),
          ],
          if (c.requirementList.isNotEmpty) ...[
            const SizedBox(height: 24),
            const Text('Requirements', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
            const SizedBox(height: 8),
            ...c.requirementList.map((r) => Padding(
                  padding: const EdgeInsets.only(bottom: 6),
                  child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    const Text('•  '),
                    Expanded(child: Text(r)),
                  ]),
                )),
          ],
        ],
      );
  }

  Widget _sidebar(BuildContext context, Course c) => Card(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Course Details', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
              const SizedBox(height: 16),
              _detail(context, Icons.person_outline, 'Instructor', c.instructor),
              _detail(context, Icons.schedule_outlined, 'Duration', c.duration),
              _detail(context, Icons.calendar_today_outlined, 'Dates', '${formatDate(c.startDate)} – ${formatDate(c.endDate)}'),
              _detail(context, Icons.people_outline, 'Seats', '${c.seatsAvailable} of ${c.seats} available'),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: c.seatsAvailable > 0 ? () => context.push('/apply/${c.id}') : null,
                  child: Text(c.seatsAvailable > 0 ? 'Apply Now' : 'No Seats Available'),
                ),
              ),
            ],
          ),
        ),
      );

  Widget _detail(BuildContext context, IconData icon, String label, String value) {
    final palette = context.palette;
    return Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, size: 20, color: AppColors.primary),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label, style: TextStyle(fontSize: 12, color: palette.textSecondary)),
                  Text(value, style: TextStyle(fontWeight: FontWeight.w600, color: palette.textPrimary)),
                ],
              ),
            ),
          ],
        ),
      );
  }
}
