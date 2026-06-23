'use client';

import { useEffect, useState } from 'react';
import { Search, Download, CheckCircle, XCircle, Eye } from 'lucide-react';
import { AdminGuard } from '@/components/admin/AdminGuard';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { EmptyState } from '@/components/ui/EmptyState';
import { Textarea } from '@/components/ui/Input';
import { api } from '@/lib/api';
import type { Application } from '@/lib/types';
import { STATUS_COLORS, formatDateTime } from '@/lib/utils';

function ApplicationsContent() {
  const [applications, setApplications] = useState<Application[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [selected, setSelected] = useState<Application | null>(null);
  const [notes, setNotes] = useState('');
  const [actionLoading, setActionLoading] = useState(false);

  const load = () => {
    setLoading(true);
    const params: Record<string, string> = {};
    if (statusFilter) params.application_status = statusFilter;
    if (search) params.search = search;
    api.getApplications(params)
      .then(setApplications)
      .catch(() => setApplications([]))
      .finally(() => setLoading(false));
  };

  useEffect(() => { load(); }, [statusFilter]);

  const handleApprove = async (id: number) => {
    setActionLoading(true);
    try {
      await api.approveApplication(id, notes);
      setSelected(null);
      setNotes('');
      load();
    } finally {
      setActionLoading(false);
    }
  };

  const handleReject = async (id: number) => {
    setActionLoading(true);
    try {
      await api.rejectApplication(id, notes);
      setSelected(null);
      setNotes('');
      load();
    } finally {
      setActionLoading(false);
    }
  };

  const handleExport = async () => {
    const params: Record<string, string> = {};
    if (statusFilter) params.application_status = statusFilter;
    const blob = await api.exportApplications(params);
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'applications.csv';
    a.click();
    URL.revokeObjectURL(url);
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Applications</h1>
          <p className="text-gray-500">Review and manage student applications</p>
        </div>
        <Button variant="secondary" onClick={handleExport}>
          <Download className="h-4 w-4" /> Export CSV
        </Button>
      </div>

      <div className="flex flex-col gap-3 sm:flex-row">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <input
            type="text"
            placeholder="Search by name, email, phone..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && load()}
            className="input-field pl-10"
          />
        </div>
        <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)} className="input-field sm:w-40">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <Button variant="secondary" onClick={load}>Search</Button>
      </div>

      <div className="grid gap-6 lg:grid-cols-3">
        <div className="lg:col-span-2">
          {loading ? (
            <div className="space-y-3">{[1, 2, 3].map((n) => <div key={n} className="glass-card h-16 animate-pulse" />)}</div>
          ) : applications.length > 0 ? (
            <div className="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                    <th className="px-4 py-3 text-left font-medium text-gray-500">Applicant</th>
                    <th className="px-4 py-3 text-left font-medium text-gray-500 hidden sm:table-cell">Course</th>
                    <th className="px-4 py-3 text-left font-medium text-gray-500 hidden md:table-cell">Submitted</th>
                    <th className="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                    <th className="px-4 py-3 text-right font-medium text-gray-500">View</th>
                  </tr>
                </thead>
                <tbody>
                  {applications.map((app) => (
                    <tr
                      key={app.id}
                      className={`border-b border-gray-100 cursor-pointer transition-colors dark:border-gray-800 ${
                        selected?.id === app.id ? 'bg-primary-50 dark:bg-primary-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50'
                      }`}
                      onClick={() => { setSelected(app); setNotes(app.admin_notes || ''); }}
                    >
                      <td className="px-4 py-3">
                        <p className="font-medium text-gray-900 dark:text-white">{app.full_name}</p>
                        <p className="text-xs text-gray-500">{app.email}</p>
                      </td>
                      <td className="px-4 py-3 text-gray-500 hidden sm:table-cell">{app.course_title}</td>
                      <td className="px-4 py-3 text-gray-500 hidden md:table-cell">{formatDateTime(app.submitted_at)}</td>
                      <td className="px-4 py-3">
                        <Badge className={STATUS_COLORS[app.application_status]}>{app.application_status}</Badge>
                      </td>
                      <td className="px-4 py-3 text-right">
                        <Eye className="inline h-4 w-4 text-gray-400" />
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <EmptyState title="No applications" description="Applications will appear here when students apply." />
          )}
        </div>

        {/* Detail Panel */}
        <div className="glass-card sticky top-24 h-fit">
          {selected ? (
            <div className="space-y-4">
              <h3 className="font-semibold text-gray-900 dark:text-white">{selected.full_name}</h3>
              <dl className="space-y-2 text-sm">
                <div><dt className="text-gray-500">Email</dt><dd>{selected.email}</dd></div>
                <div><dt className="text-gray-500">Phone</dt><dd>{selected.phone}</dd></div>
                <div><dt className="text-gray-500">Course</dt><dd>{selected.course_title}</dd></div>
                <div><dt className="text-gray-500">Education</dt><dd>{selected.education_level} - {selected.institution}</dd></div>
                <div><dt className="text-gray-500">Motivation</dt><dd className="text-gray-600 dark:text-gray-300">{selected.motivation}</dd></div>
              </dl>
              <Textarea label="Admin Notes" value={notes} onChange={(e) => setNotes(e.target.value)} />
              {selected.application_status === 'pending' && (
                <div className="flex gap-2">
                  <Button className="flex-1" onClick={() => handleApprove(selected.id)} loading={actionLoading}>
                    <CheckCircle className="h-4 w-4" /> Approve
                  </Button>
                  <Button variant="danger" className="flex-1" onClick={() => handleReject(selected.id)} loading={actionLoading}>
                    <XCircle className="h-4 w-4" /> Reject
                  </Button>
                </div>
              )}
            </div>
          ) : (
            <p className="text-center text-sm text-gray-500 py-8">Select an application to view details</p>
          )}
        </div>
      </div>
    </div>
  );
}

export default function ApplicationsPage() {
  return (
    <AdminGuard>
      <ApplicationsContent />
    </AdminGuard>
  );
}
