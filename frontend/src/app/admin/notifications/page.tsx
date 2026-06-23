'use client';

import { useEffect, useState } from 'react';
import { Send } from 'lucide-react';
import { AdminGuard } from '@/components/admin/AdminGuard';
import { Input, Select, Textarea } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';
import { api } from '@/lib/api';
import type { Notification, Course } from '@/lib/types';
import { formatDateTime } from '@/lib/utils';

function NotificationsContent() {
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [courses, setCourses] = useState<Course[]>([]);
  const [loading, setLoading] = useState(false);
  const [form, setForm] = useState({
    title: '',
    message: '',
    notification_type: 'announcement',
    recipient_type: 'all',
    recipient_email: '',
    course: '',
  });
  const [success, setSuccess] = useState('');

  useEffect(() => {
    api.getNotifications().then(setNotifications).catch(() => {});
    api.getCourses().then(setCourses).catch(() => {});
  }, []);

  const update = (field: string, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setSuccess('');
    try {
      const data: Record<string, unknown> = { ...form };
      if (form.course) data.course = Number(form.course);
      else delete data.course;
      if (form.recipient_type !== 'specific') delete data.recipient_email;
      await api.sendNotification(data);
      setSuccess('Notification sent successfully!');
      setForm({ title: '', message: '', notification_type: 'announcement', recipient_type: 'all', recipient_email: '', course: '' });
      api.getNotifications().then(setNotifications);
    } catch (err) {
      setSuccess('');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Notifications</h1>
        <p className="text-gray-500">Send messages to applicants and students</p>
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <form onSubmit={handleSubmit} className="glass-card space-y-4">
          <h3 className="font-semibold text-gray-900 dark:text-white">Send Notification</h3>
          <Select label="Type" options={[
            { value: 'approval', label: 'Approval Message' },
            { value: 'rejection', label: 'Rejection Message' },
            { value: 'course_update', label: 'Course Update' },
            { value: 'announcement', label: 'General Announcement' },
          ]} value={form.notification_type} onChange={(e) => update('notification_type', e.target.value)} />
          <Input label="Title *" value={form.title} onChange={(e) => update('title', e.target.value)} required />
          <Textarea label="Message *" value={form.message} onChange={(e) => update('message', e.target.value)} required />
          <Select label="Recipients" options={[
            { value: 'all', label: 'All Applicants' },
            { value: 'approved', label: 'Approved Applicants' },
            { value: 'pending', label: 'Pending Applicants' },
            { value: 'rejected', label: 'Rejected Applicants' },
            { value: 'specific', label: 'Specific Email' },
          ]} value={form.recipient_type} onChange={(e) => update('recipient_type', e.target.value)} />
          {form.recipient_type === 'specific' && (
            <Input label="Recipient Email" type="email" value={form.recipient_email} onChange={(e) => update('recipient_email', e.target.value)} required />
          )}
          <Select label="Related Course (optional)" options={[
            { value: '', label: 'None' },
            ...courses.map((c) => ({ value: String(c.id), label: c.title })),
          ]} value={form.course} onChange={(e) => update('course', e.target.value)} />
          {success && <p className="text-sm text-accent-600">{success}</p>}
          <Button type="submit" loading={loading} className="w-full">
            <Send className="h-4 w-4" /> Send Notification
          </Button>
        </form>

        <div className="glass-card">
          <h3 className="mb-4 font-semibold text-gray-900 dark:text-white">Sent Notifications</h3>
          <div className="space-y-3 max-h-[500px] overflow-y-auto">
            {notifications.length > 0 ? notifications.map((n) => (
              <div key={n.id} className="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                <div className="flex items-start justify-between">
                  <p className="font-medium text-gray-900 dark:text-white">{n.title}</p>
                  <span className="text-xs text-gray-400 capitalize">{n.notification_type.replace('_', ' ')}</span>
                </div>
                <p className="mt-1 text-sm text-gray-500 line-clamp-2">{n.message}</p>
                <p className="mt-2 text-xs text-gray-400">{formatDateTime(n.sent_at)}</p>
              </div>
            )) : (
              <p className="text-sm text-gray-500 text-center py-8">No notifications sent yet</p>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

export default function NotificationsPage() {
  return (
    <AdminGuard>
      <NotificationsContent />
    </AdminGuard>
  );
}
