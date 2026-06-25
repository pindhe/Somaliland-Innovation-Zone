import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../../models/application.dart';
import '../../models/course.dart';
import '../../services/api_service.dart';
import '../../config/theme.dart';
import '../../utils/constants.dart';

import '../../widgets/student_navbar.dart';

class ApplicationFormScreen extends StatefulWidget {
  final int courseId;
  const ApplicationFormScreen({super.key, required this.courseId});

  @override
  State<ApplicationFormScreen> createState() => _ApplicationFormScreenState();
}

class _ApplicationFormScreenState extends State<ApplicationFormScreen> {
  final _form = ApplicationFormData();
  int step = 0;
  bool submitting = false;
  Course? course;

  static const steps = [
    'Personal Information',
    'Educational Information',
    'Course Selection',
    'Motivation',
    'Review & Submit',
  ];

  @override
  void initState() {
    super.initState();
    _form.selectedCourse = widget.courseId;
    context.read<ApiService>().getCourse(widget.courseId).then((c) {
      if (mounted) setState(() => course = c);
    });
  }

  bool _validate() {
    switch (step) {
      case 0:
        return _form.fullName.isNotEmpty && _form.gender.isNotEmpty && _form.dateOfBirth.isNotEmpty &&
            _form.nationality.isNotEmpty && _form.phone.isNotEmpty && _form.email.contains('@') && _form.address.isNotEmpty;
      case 1:
        return _form.educationLevel.isNotEmpty && _form.institution.isNotEmpty && _form.fieldOfStudy.isNotEmpty;
      case 2:
        return _form.preferredSchedule.isNotEmpty;
      case 3:
        return _form.motivation.isNotEmpty && _form.careerGoals.isNotEmpty;
      default:
        return true;
    }
  }

  Future<void> _submit() async {
    setState(() => submitting = true);
    try {
      await context.read<ApiService>().submitApplication(_form);
      if (mounted) context.go('/apply/success');
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
      }
    } finally {
      if (mounted) setState(() => submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: context.palette.background,
      appBar: const StudentNavbar(title: 'Application Form', showBack: true),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Text('Step ${step + 1} of ${steps.length}: ${steps[step]}', style: const TextStyle(color: Colors.grey)),
                const SizedBox(height: 12),
                LinearProgressIndicator(value: (step + 1) / steps.length, borderRadius: BorderRadius.circular(4)),
              ],
            ),
          ),
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Card(
                child: Padding(padding: const EdgeInsets.all(20), child: _stepContent()),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                if (step > 0)
                  OutlinedButton(onPressed: () => setState(() => step--), child: const Text('Previous')),
                const Spacer(),
                if (step < steps.length - 1)
                  ElevatedButton(
                    onPressed: _validate() ? () => setState(() => step++) : null,
                    child: const Text('Next'),
                  )
                else
                  ElevatedButton(
                    onPressed: submitting ? null : _submit,
                    child: submitting ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)) : const Text('Submit Application'),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _stepContent() {
    switch (step) {
      case 0:
        return Column(children: [
          _field('Full Name', (v) => _form.fullName = v, required: true),
          _dropdown('Gender', genderOptions, (v) => _form.gender = v),
          _dateField(),
          _field('Nationality', (v) => _form.nationality = v),
          _field('Phone Number', (v) => _form.phone = v),
          _field('Email Address', (v) => _form.email = v),
          _field('Residential Address', (v) => _form.address = v, maxLines: 3),
        ]);
      case 1:
        return Column(children: [
          _dropdown('Education Level', educationOptions, (v) => _form.educationLevel = v),
          _field('Institution Name', (v) => _form.institution = v),
          _field('Field of Study', (v) => _form.fieldOfStudy = v),
          _numberField('Graduation Year', (v) => _form.graduationYear = int.tryParse(v) ?? _form.graduationYear),
        ]);
      case 2:
        return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Card(
            color: Colors.blue.shade50,
            child: ListTile(
              title: const Text('Selected Course'),
              subtitle: Text(course?.title ?? 'Loading...'),
            ),
          ),
          const SizedBox(height: 16),
          _dropdown('Preferred Schedule', scheduleOptions, (v) => _form.preferredSchedule = v),
        ]);
      case 3:
        return Column(children: [
          _field('Why do you want to join this course?', (v) => _form.motivation = v, maxLines: 4),
          _field('Career Goals', (v) => _form.careerGoals = v, maxLines: 4),
          _field('Additional Comments', (v) => _form.comments = v, maxLines: 3, required: false),
        ]);
      default:
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _reviewSection('Personal', {
              'Name': _form.fullName,
              'Email': _form.email,
              'Phone': _form.phone,
              'Address': _form.address,
            }),
            _reviewSection('Education', {
              'Level': _form.educationLevel,
              'Institution': _form.institution,
              'Field': _form.fieldOfStudy,
            }),
            _reviewSection('Course', {
              'Course': course?.title ?? '',
              'Schedule': _form.preferredSchedule,
            }),
          ],
        );
    }
  }

  Widget _field(String label, void Function(String) onChanged, {int maxLines = 1, bool required = true}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextFormField(
        decoration: InputDecoration(labelText: required ? '$label *' : label),
        maxLines: maxLines,
        onChanged: onChanged,
      ),
    );
  }

  Widget _numberField(String label, void Function(String) onChanged) => Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: TextFormField(
          decoration: InputDecoration(labelText: '$label *'),
          keyboardType: TextInputType.number,
          initialValue: _form.graduationYear.toString(),
          onChanged: onChanged,
        ),
      );

  Widget _dropdown(String label, List<Map<String, String>> options, void Function(String) onChanged) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: DropdownButtonFormField<String>(
        decoration: InputDecoration(labelText: '$label *'),
        items: options.map((o) => DropdownMenuItem(value: o['value'], child: Text(o['label']!))).toList(),
        onChanged: (v) { if (v != null) onChanged(v); },
      ),
    );
  }

  Widget _dateField() => Padding(
        padding: const EdgeInsets.only(bottom: 12),
        child: TextFormField(
          decoration: const InputDecoration(labelText: 'Date of Birth *', suffixIcon: Icon(Icons.calendar_today)),
          readOnly: true,
          controller: TextEditingController(text: _form.dateOfBirth),
          onTap: () async {
            final d = await showDatePicker(context: context, initialDate: DateTime(2000), firstDate: DateTime(1960), lastDate: DateTime.now());
            if (d != null) setState(() => _form.dateOfBirth = d.toIso8601String().split('T').first);
          },
        ),
      );

  Widget _reviewSection(String title, Map<String, String> items) => Padding(
        padding: const EdgeInsets.only(bottom: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 16)),
            const SizedBox(height: 8),
            ...items.entries.map((e) => Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      SizedBox(width: 100, child: Text(e.key, style: const TextStyle(color: Colors.grey, fontSize: 13))),
                      Expanded(child: Text(e.value.isEmpty ? '—' : e.value)),
                    ],
                  ),
                )),
          ],
        ),
      );
}
