import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/course.dart';
import '../../services/api_service.dart';
import '../../utils/constants.dart';
import '../../widgets/common_widgets.dart';

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
        appBar: AppBar(),
        body: const EmptyStateWidget(title: 'Course not found', description: 'This course may no longer be available.'),
      );
    }

    final c = course!;
    final isWide = MediaQuery.sizeOf(context).width > 800;

    return Scaffold(
      appBar: AppBar(title: Text(c.title, maxLines: 1, overflow: TextOverflow.ellipsis)),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: isWide
            ? Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(flex: 2, child: _mainContent(c)),
                  const SizedBox(width: 24),
                  SizedBox(width: 320, child: _sidebar(context, c)),
                ],
              )
            : Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _mainContent(c),
                  const SizedBox(height: 20),
                  _sidebar(context, c),
                ],
              ),
      ),
    );
  }

  Widget _mainContent(Course c) => Column(
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
          Text(c.title, style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w800)),
          const SizedBox(height: 12),
          Text(c.description, style: const TextStyle(height: 1.6, color: AppColors.textSecondary)),
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

  Widget _sidebar(BuildContext context, Course c) => Card(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Course Details', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
              const SizedBox(height: 16),
              _detail(Icons.person_outline, 'Instructor', c.instructor),
              _detail(Icons.schedule, 'Duration', c.duration),
              _detail(Icons.calendar_today, 'Dates', '${formatDate(c.startDate)} – ${formatDate(c.endDate)}'),
              _detail(Icons.people_outline, 'Seats', '${c.seatsAvailable} of ${c.seats} available'),
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

  Widget _detail(IconData icon, String label, String value) => Padding(
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
                  Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                  Text(value, style: const TextStyle(fontWeight: FontWeight.w600)),
                ],
              ),
            ),
          ],
        ),
      );
}
