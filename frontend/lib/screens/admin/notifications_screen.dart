import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/admin.dart';
import '../../models/course.dart';
import '../../services/api_service.dart';
import '../../widgets/common_widgets.dart';
import 'admin_shell.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final _title = TextEditingController();
  final _message = TextEditingController();
  String type = 'announcement';
  String recipientType = 'all';
  String? courseId;
  List<NotificationItem> sent = [];
  List<Course> courses = [];
  bool loading = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final api = context.read<ApiService>();
    try {
      sent = await api.getNotifications();
      courses = await api.getCourses();
    } catch (_) {}
    if (mounted) setState(() {});
  }

  Future<void> _send() async {
    setState(() => loading = true);
    try {
      final body = <String, dynamic>{
        'title': _title.text,
        'message': _message.text,
        'notification_type': type,
        'recipient_type': recipientType,
      };
      if (courseId != null && courseId!.isNotEmpty) body['course'] = int.parse(courseId!);
      await context.read<ApiService>().sendNotification(body);
      _title.clear();
      _message.clear();
      await _load();
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Notification sent!')));
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isWide = MediaQuery.sizeOf(context).width > 800;

    return AdminShell(
      title: 'Notifications',
      child: isWide
          ? Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(child: _form()),
                const SizedBox(width: 20),
                Expanded(child: _history()),
              ],
            )
          : SingleChildScrollView(child: Column(children: [_form(), const SizedBox(height: 20), _history()])),
    );
  }

  Widget _form() => Card(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Send Notification', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                decoration: const InputDecoration(labelText: 'Type'),
                value: type,
                items: const [
                  DropdownMenuItem(value: 'approval', child: Text('Approval Message')),
                  DropdownMenuItem(value: 'rejection', child: Text('Rejection Message')),
                  DropdownMenuItem(value: 'course_update', child: Text('Course Update')),
                  DropdownMenuItem(value: 'announcement', child: Text('General Announcement')),
                ],
                onChanged: (v) => setState(() => type = v ?? 'announcement'),
              ),
              const SizedBox(height: 12),
              TextField(controller: _title, decoration: const InputDecoration(labelText: 'Title *')),
              const SizedBox(height: 12),
              TextField(controller: _message, decoration: const InputDecoration(labelText: 'Message *'), maxLines: 4),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                decoration: const InputDecoration(labelText: 'Recipients'),
                value: recipientType,
                items: const [
                  DropdownMenuItem(value: 'all', child: Text('All Applicants')),
                  DropdownMenuItem(value: 'approved', child: Text('Approved')),
                  DropdownMenuItem(value: 'pending', child: Text('Pending')),
                  DropdownMenuItem(value: 'rejected', child: Text('Rejected')),
                ],
                onChanged: (v) => setState(() => recipientType = v ?? 'all'),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                decoration: const InputDecoration(labelText: 'Related Course (optional)'),
                value: courseId,
                items: [
                  const DropdownMenuItem(value: null, child: Text('None')),
                  ...courses.map((c) => DropdownMenuItem(value: c.id.toString(), child: Text(c.title))),
                ],
                onChanged: (v) => setState(() => courseId = v),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: loading ? null : _send,
                  icon: const Icon(Icons.send),
                  label: Text(loading ? 'Sending...' : 'Send Notification'),
                ),
              ),
            ],
          ),
        ),
      );

  Widget _history() => Card(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Sent Notifications', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
              const SizedBox(height: 12),
              if (sent.isEmpty)
                const Padding(padding: EdgeInsets.all(32), child: Center(child: Text('No notifications sent yet')))
              else
                ...sent.map((n) => ListTile(
                      title: Text(n.title),
                      subtitle: Text(n.message, maxLines: 2, overflow: TextOverflow.ellipsis),
                      trailing: Text(n.notificationType, style: const TextStyle(fontSize: 11)),
                    )),
            ],
          ),
        ),
      );
}
