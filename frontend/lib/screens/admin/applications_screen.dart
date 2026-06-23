import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/application.dart';
import '../../services/api_service.dart';
import '../../widgets/common_widgets.dart';
import 'admin_shell.dart';

class ApplicationsScreen extends StatefulWidget {
  const ApplicationsScreen({super.key});

  @override
  State<ApplicationsScreen> createState() => _ApplicationsScreenState();
}

class _ApplicationsScreenState extends State<ApplicationsScreen> {
  List<Application> applications = [];
  Application? selected;
  final _notes = TextEditingController();
  bool loading = true;
  String statusFilter = '';
  String search = '';

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      final params = <String, String>{};
      if (statusFilter.isNotEmpty) params['application_status'] = statusFilter;
      if (search.isNotEmpty) params['search'] = search;
      applications = await context.read<ApiService>().getApplications(params: params.isEmpty ? null : params);
    } catch (_) {}
    if (mounted) setState(() => loading = false);
  }

  Future<void> _approve() async {
    if (selected == null) return;
    await context.read<ApiService>().approveApplication(selected!.id, notes: _notes.text);
    _load();
    setState(() => selected = null);
  }

  Future<void> _reject() async {
    if (selected == null) return;
    await context.read<ApiService>().rejectApplication(selected!.id, notes: _notes.text);
    _load();
    setState(() => selected = null);
  }

  @override
  Widget build(BuildContext context) {
    final isWide = MediaQuery.sizeOf(context).width > 900;

    return AdminShell(
      title: 'Applications',
      child: Column(
        children: [
          Row(
            children: [
              Expanded(child: TextField(decoration: const InputDecoration(prefixIcon: Icon(Icons.search), hintText: 'Search...'), onChanged: (v) => search = v, onSubmitted: (_) => _load())),
              const SizedBox(width: 12),
              DropdownButton<String>(
                hint: const Text('Status'),
                value: statusFilter.isEmpty ? null : statusFilter,
                items: const [
                  DropdownMenuItem(value: '', child: Text('All')),
                  DropdownMenuItem(value: 'pending', child: Text('Pending')),
                  DropdownMenuItem(value: 'approved', child: Text('Approved')),
                  DropdownMenuItem(value: 'rejected', child: Text('Rejected')),
                ],
                onChanged: (v) { statusFilter = v ?? ''; _load(); },
              ),
            ],
          ),
          const SizedBox(height: 16),
          Expanded(
            child: loading
                ? const LoadingView()
                : isWide
                    ? Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(flex: 2, child: _list()),
                          const SizedBox(width: 16),
                          Expanded(child: _detail()),
                        ],
                      )
                    : Column(children: [Expanded(child: _list()), if (selected != null) _detail()]),
          ),
        ],
      ),
    );
  }

  Widget _list() {
    if (applications.isEmpty) return const EmptyStateWidget(title: 'No applications', description: 'Applications will appear here.');
    return ListView.separated(
      itemCount: applications.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (_, i) {
        final a = applications[i];
        final isSelected = selected?.id == a.id;
        return Card(
          color: isSelected ? Theme.of(context).colorScheme.primaryContainer.withValues(alpha: 0.3) : null,
          child: ListTile(
            onTap: () => setState(() { selected = a; _notes.text = a.adminNotes ?? ''; }),
            title: Text(a.fullName),
            subtitle: Text('${a.courseTitle} • ${a.email}'),
            trailing: StatusChip(status: a.applicationStatus),
          ),
        );
      },
    );
  }

  Widget _detail() {
    if (selected == null) {
      return const Card(child: Padding(padding: EdgeInsets.all(32), child: Center(child: Text('Select an application'))));
    }
    final a = selected!;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(a.fullName, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
            const SizedBox(height: 12),
            Text('Email: ${a.email}'),
            Text('Phone: ${a.phone}'),
            Text('Course: ${a.courseTitle}'),
            Text('Education: ${a.educationLevel} - ${a.institution}'),
            const SizedBox(height: 8),
            Text('Motivation: ${a.motivation}', maxLines: 3, overflow: TextOverflow.ellipsis),
            const SizedBox(height: 16),
            TextField(controller: _notes, decoration: const InputDecoration(labelText: 'Admin Notes'), maxLines: 3),
            if (a.applicationStatus == 'pending') ...[
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(child: ElevatedButton(onPressed: _approve, child: const Text('Approve'))),
                  const SizedBox(width: 12),
                  Expanded(child: OutlinedButton(onPressed: _reject, style: OutlinedButton.styleFrom(foregroundColor: Colors.red), child: const Text('Reject'))),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }
}
