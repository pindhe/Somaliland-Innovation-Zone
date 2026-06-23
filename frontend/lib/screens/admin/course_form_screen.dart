import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../models/course.dart';
import '../../services/api_service.dart';
import '../../utils/constants.dart';
import '../../widgets/common_widgets.dart';
import 'admin_shell.dart';

class CourseFormScreen extends StatefulWidget {
  final int? courseId;
  const CourseFormScreen({super.key, this.courseId});

  @override
  State<CourseFormScreen> createState() => _CourseFormScreenState();
}

class _CourseFormScreenState extends State<CourseFormScreen> {
  final _title = TextEditingController();
  final _desc = TextEditingController();
  final _duration = TextEditingController();
  final _instructor = TextEditingController();
  final _seats = TextEditingController(text: '30');
  final _outcomes = TextEditingController();
  final _requirements = TextEditingController();
  String category = '';
  String trainingType = 'free';
  String status = 'draft';
  DateTime? startDate;
  DateTime? endDate;
  bool loading = false;
  bool fetching = false;

  @override
  void initState() {
    super.initState();
    if (widget.courseId != null) _loadCourse();
  }

  Future<void> _loadCourse() async {
    setState(() => fetching = true);
    try {
      final c = await context.read<ApiService>().getCourse(widget.courseId!);
      _title.text = c.title;
      _desc.text = c.description;
      _duration.text = c.duration;
      _instructor.text = c.instructor;
      _seats.text = c.seats.toString();
      _outcomes.text = c.outcomes ?? '';
      _requirements.text = c.requirements ?? '';
      category = c.category;
      trainingType = c.trainingType;
      status = c.status;
      startDate = DateTime.tryParse(c.startDate);
      endDate = DateTime.tryParse(c.endDate);
    } catch (_) {}
    if (mounted) setState(() => fetching = false);
  }

  Future<void> _save() async {
    if (_title.text.isEmpty || category.isEmpty || startDate == null || endDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Please fill required fields')));
      return;
    }
    setState(() => loading = true);
    try {
      final fields = {
        'title': _title.text,
        'category': category,
        'description': _desc.text,
        'duration': _duration.text,
        'training_type': trainingType,
        'instructor': _instructor.text,
        'seats': _seats.text,
        'outcomes': _outcomes.text,
        'requirements': _requirements.text,
        'status': status,
        'start_date': startDate!.toIso8601String().split('T').first,
        'end_date': endDate!.toIso8601String().split('T').first,
      };
      final api = context.read<ApiService>();
      if (widget.courseId != null) {
        await api.updateCourse(widget.courseId!, fields);
      } else {
        await api.createCourse(fields);
      }
      if (mounted) context.go('/admin/courses');
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return AdminShell(
      title: widget.courseId != null ? 'Edit Course' : 'Add Course',
      child: fetching
          ? const LoadingView()
          : SingleChildScrollView(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 700),
                child: Column(
                  children: [
                    TextField(controller: _title, decoration: const InputDecoration(labelText: 'Course Title *')),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      decoration: const InputDecoration(labelText: 'Category *'),
                      value: category.isEmpty ? null : category,
                      items: courseCategories.map((c) => DropdownMenuItem(value: c, child: Text(c))).toList(),
                      onChanged: (v) => setState(() => category = v ?? ''),
                    ),
                    const SizedBox(height: 12),
                    TextField(controller: _duration, decoration: const InputDecoration(labelText: 'Duration *')),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            decoration: const InputDecoration(labelText: 'Type'),
                            value: trainingType,
                            items: const [
                              DropdownMenuItem(value: 'free', child: Text('Free')),
                              DropdownMenuItem(value: 'paid', child: Text('Paid')),
                            ],
                            onChanged: (v) => setState(() => trainingType = v ?? 'free'),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: DropdownButtonFormField<String>(
                            decoration: const InputDecoration(labelText: 'Status'),
                            value: status,
                            items: const [
                              DropdownMenuItem(value: 'draft', child: Text('Draft')),
                              DropdownMenuItem(value: 'open', child: Text('Open')),
                              DropdownMenuItem(value: 'closed', child: Text('Closed')),
                              DropdownMenuItem(value: 'archived', child: Text('Archived')),
                            ],
                            onChanged: (v) => setState(() => status = v ?? 'draft'),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextField(controller: _instructor, decoration: const InputDecoration(labelText: 'Instructor *')),
                    const SizedBox(height: 12),
                    TextField(controller: _seats, decoration: const InputDecoration(labelText: 'Seats *'), keyboardType: TextInputType.number),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: _dateBtn('Start Date *', startDate, (d) => setState(() => startDate = d))),
                        const SizedBox(width: 12),
                        Expanded(child: _dateBtn('End Date *', endDate, (d) => setState(() => endDate = d))),
                      ],
                    ),
                    const SizedBox(height: 12),
                    TextField(controller: _desc, decoration: const InputDecoration(labelText: 'Description *'), maxLines: 4),
                    const SizedBox(height: 12),
                    TextField(controller: _outcomes, decoration: const InputDecoration(labelText: 'Learning Outcomes'), maxLines: 3),
                    const SizedBox(height: 12),
                    TextField(controller: _requirements, decoration: const InputDecoration(labelText: 'Requirements'), maxLines: 3),
                    const SizedBox(height: 24),
                    Row(
                      children: [
                        ElevatedButton(onPressed: loading ? null : _save, child: Text(loading ? 'Saving...' : 'Save Course')),
                        const SizedBox(width: 12),
                        OutlinedButton(onPressed: () => context.pop(), child: const Text('Cancel')),
                      ],
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _dateBtn(String label, DateTime? date, void Function(DateTime) onPick) => OutlinedButton(
        onPressed: () async {
          final d = await showDatePicker(context: context, initialDate: date ?? DateTime.now(), firstDate: DateTime.now(), lastDate: DateTime.now().add(const Duration(days: 730)));
          if (d != null) onPick(d);
        },
        child: Text(date == null ? label : '${formatDate(date.toIso8601String())}'),
      );
}
