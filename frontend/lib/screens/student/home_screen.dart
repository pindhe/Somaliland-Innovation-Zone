import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/course.dart';
import '../../providers/app_providers.dart';
import '../../services/api_service.dart';
import '../../widgets/common_widgets.dart';

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
    final width = MediaQuery.sizeOf(context).width;
    final cols = width > 900 ? 3 : width > 600 ? 2 : 1;

    return Scaffold(
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            floating: true,
            title: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(color: AppColors.primary, borderRadius: BorderRadius.circular(10)),
                  child: const Icon(Icons.school, color: Colors.white, size: 20),
                ),
                const SizedBox(width: 10),
                const Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('SIZSR', style: TextStyle(fontSize: 16)),
                    Text('Innovation Zone', style: TextStyle(fontSize: 10, color: AppColors.textSecondary)),
                  ],
                ),
              ],
            ),
            actions: [
              IconButton(
                icon: Icon(context.watch<ThemeProvider>().mode == ThemeMode.dark ? Icons.light_mode : Icons.dark_mode),
                onPressed: () => context.read<ThemeProvider>().toggle(),
              ),
              TextButton(onPressed: () => context.push('/courses'), child: const Text('Courses')),
              const SizedBox(width: 8),
              Padding(
                padding: const EdgeInsets.only(right: 12),
                child: ElevatedButton(onPressed: () => context.push('/courses'), child: const Text('Apply Now')),
              ),
            ],
          ),
          SliverToBoxAdapter(child: _hero(context)),
          SliverPadding(
            padding: const EdgeInsets.all(20),
            sliver: SliverToBoxAdapter(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Why Choose SIZSR?', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
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
                        _FeatureCard(Icons.menu_book, 'Expert-Led Courses', 'Learn from industry professionals.'),
                        _FeatureCard(Icons.verified, 'Certified Programs', 'Earn recognized certificates.'),
                        _FeatureCard(Icons.groups, 'Community Learning', 'Join innovators and entrepreneurs.'),
                        _FeatureCard(Icons.code, 'Hands-On Projects', 'Build practical real-world skills.'),
                      ],
                    );
                  }),
                  const SizedBox(height: 32),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Featured Programs', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
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

  Widget _hero(BuildContext context) => Container(
        width: double.infinity,
        margin: const EdgeInsets.all(20),
        padding: const EdgeInsets.all(32),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(24),
          gradient: const LinearGradient(
            colors: [AppColors.primary, AppColors.primaryDark, Color(0xFF1E3A8A)],
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

class _FeatureCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String desc;
  const _FeatureCard(this.icon, this.title, this.desc);

  @override
  Widget build(BuildContext context) => Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, color: AppColors.primary, size: 28),
              const SizedBox(height: 10),
              Text(title, textAlign: TextAlign.center, style: const TextStyle(fontWeight: FontWeight.w700)),
              const SizedBox(height: 6),
              Text(desc, textAlign: TextAlign.center, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
            ],
          ),
        ),
      );
}
