import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/course.dart';
import '../../services/api_service.dart';
import '../../widgets/common_widgets.dart';
import '../../widgets/student_navbar.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<Course> featured = [];
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      featured = await context.read<ApiService>().getFeaturedCourses();
    } catch (_) {}
    if (mounted) setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    final palette = context.palette;
    final width = MediaQuery.sizeOf(context).width;
    final cols = width > 900 ? 3 : width > 600 ? 2 : 1;

    return Scaffold(
      backgroundColor: palette.background,
      appBar: const StudentNavbar(),
      body: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _hero(context)),
          SliverPadding(
            padding: const EdgeInsets.all(20),
            sliver: SliverToBoxAdapter(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Why Choose SIZSR?', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: palette.textPrimary)),
                  const SizedBox(height: 16),
                  LayoutBuilder(builder: (context, c) {
                    final w = c.maxWidth;
                    final fc = w > 800 ? 4 : w > 500 ? 2 : 1;
                    return GridView.count(
                      crossAxisCount: fc,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      mainAxisSpacing: 12,
                      crossAxisSpacing: 12,
                      childAspectRatio: 1.3,
                      children: const [
                        _FeatureCard(Icons.menu_book_outlined, 'Expert-Led Courses', 'Learn from industry professionals.'),
                        _FeatureCard(Icons.verified_outlined, 'Certified Programs', 'Earn recognized certificates.'),
                        _FeatureCard(Icons.groups_outlined, 'Community Learning', 'Join innovators and entrepreneurs.'),
                        _FeatureCard(Icons.code_outlined, 'Hands-On Projects', 'Build practical real-world skills.'),
                      ],
                    );
                  }),
                  const SizedBox(height: 32),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Featured Programs', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: palette.textPrimary)),
                      TextButton(onPressed: () => context.push('/courses'), child: const Text('View All')),
                    ],
                  ),
                ],
              ),
            ),
          ),
          if (loading)
            const SliverFillRemaining(child: LoadingView())
          else if (featured.isEmpty)
            const SliverFillRemaining(
              hasScrollBody: false,
              child: EmptyStateWidget(title: 'No courses yet', description: 'Check back soon for new programs.'),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(20, 0, 20, 32),
              sliver: SliverGrid(
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: cols,
                  mainAxisSpacing: 16,
                  crossAxisSpacing: 16,
                  childAspectRatio: 0.72,
                ),
                delegate: SliverChildBuilderDelegate(
                  (context, i) => CourseCard(course: featured[i], onTap: () => context.push('/courses/${featured[i].id}')),
                  childCount: featured.length,
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _hero(BuildContext context) {
    final palette = context.palette;
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.all(20),
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(24),
        gradient: LinearGradient(
          colors: [palette.heroGradientStart, palette.heroGradientEnd],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
            decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(20)),
            child: const Text('Somaliland Innovation Zone', style: TextStyle(color: Colors.white, fontSize: 12)),
          ),
          const SizedBox(height: 20),
          const Text(
            'Transform Your Future with Professional Training',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.w800, height: 1.2),
          ),
          const SizedBox(height: 12),
          const Text(
            'Discover world-class training programs designed to equip you with in-demand skills.',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.white70, fontSize: 15),
          ),
          const SizedBox(height: 24),
          Wrap(
            spacing: 12,
            runSpacing: 12,
            alignment: WrapAlignment.center,
            children: [
              ElevatedButton(
                style: ElevatedButton.styleFrom(backgroundColor: AppColors.accent),
                onPressed: () => context.push('/courses'),
                child: const Text('Browse Courses'),
              ),
              OutlinedButton(
                style: OutlinedButton.styleFrom(foregroundColor: Colors.white, side: const BorderSide(color: Colors.white38)),
                onPressed: () => context.push('/courses'),
                child: const Text('Apply Now'),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _FeatureCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String desc;
  const _FeatureCard(this.icon, this.title, this.desc);

  @override
  Widget build(BuildContext context) {
    final palette = context.palette;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: Theme.of(context).colorScheme.primary, size: 28),
            const SizedBox(height: 10),
            Text(title, textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.w700, color: palette.textPrimary)),
            const SizedBox(height: 6),
            Text(desc, textAlign: TextAlign.center, style: TextStyle(fontSize: 12, color: palette.textSecondary)),
          ],
        ),
      ),
    );
  }
}
