'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { Plus, Search, Edit, Eye, Archive, Send } from 'lucide-react';
import { AdminGuard } from '@/components/admin/AdminGuard';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { EmptyState } from '@/components/ui/EmptyState';
import { api } from '@/lib/api';
import type { Course } from '@/lib/types';
import { STATUS_COLORS, COURSE_CATEGORIES, formatDate } from '@/lib/utils';

function CoursesContent() {
  const [courses, setCourses] = useState<Course[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  const load = () => {
    setLoading(true);
    const params: Record<string, string> = {};
    if (statusFilter) params.status = statusFilter;
    api.getCourses(params)
      .then(setCourses)
      .catch(() => setCourses([]))
      .finally(() => setLoading(false));
  };

  useEffect(() => { load(); }, [statusFilter]);

  const handlePublish = async (id: number) => {
    await api.publishCourse(id);
    load();
  };

  const handleArchive = async (id: number) => {
    await api.archiveCourse(id);
    load();
  };

  const filtered = courses.filter((c) =>
    !search || c.title.toLowerCase().includes(search.toLowerCase()),
  );

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Course Management</h1>
          <p className="text-gray-500">Create and manage training programs</p>
        </div>
        <Link href="/admin/courses/new">
          <Button><Plus className="h-4 w-4" /> Add Course</Button>
        </Link>
      </div>

      <div className="flex flex-col gap-3 sm:flex-row">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <input
            type="text"
            placeholder="Search courses..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="input-field pl-10"
          />
        </div>
        <select value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)} className="input-field sm:w-40">
          <option value="">All Status</option>
          <option value="open">Open</option>
          <option value="closed">Closed</option>
          <option value="draft">Draft</option>
          <option value="archived">Archived</option>
        </select>
      </div>

      {loading ? (
        <div className="space-y-3">
          {[1, 2, 3].map((n) => <div key={n} className="glass-card h-20 animate-pulse" />)}
        </div>
      ) : filtered.length > 0 ? (
        <div className="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                <th className="px-4 py-3 text-left font-medium text-gray-500">Title</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500 hidden sm:table-cell">Category</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500 hidden md:table-cell">Type</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500 hidden lg:table-cell">Dates</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">Status</th>
                <th className="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
              </tr>
            </thead>
            <tbody>
              {filtered.map((course) => (
                <tr key={course.id} className="border-b border-gray-100 dark:border-gray-800">
                  <td className="px-4 py-3 font-medium text-gray-900 dark:text-white">{course.title}</td>
                  <td className="px-4 py-3 text-gray-500 hidden sm:table-cell">{course.category}</td>
                  <td className="px-4 py-3 capitalize hidden md:table-cell">{course.training_type}</td>
                  <td className="px-4 py-3 text-gray-500 hidden lg:table-cell">
                    {formatDate(course.start_date)} – {formatDate(course.end_date)}
                  </td>
                  <td className="px-4 py-3">
                    <Badge className={STATUS_COLORS[course.status]}>{course.status}</Badge>
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex justify-end gap-1">
                      <Link href={`/courses/${course.id}`} className="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800" title="View">
                        <Eye className="h-4 w-4" />
                      </Link>
                      <Link href={`/admin/courses/${course.id}/edit`} className="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-primary-600 dark:hover:bg-gray-800" title="Edit">
                        <Edit className="h-4 w-4" />
                      </Link>
                      {course.status !== 'open' && (
                        <button onClick={() => handlePublish(course.id)} className="rounded-lg p-2 text-gray-400 hover:bg-accent-50 hover:text-accent-600" title="Publish">
                          <Send className="h-4 w-4" />
                        </button>
                      )}
                      {course.status !== 'archived' && (
                        <button onClick={() => handleArchive(course.id)} className="rounded-lg p-2 text-gray-400 hover:bg-red-50 hover:text-red-600" title="Archive">
                          <Archive className="h-4 w-4" />
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      ) : (
        <EmptyState
          title="No courses yet"
          description="Create your first training program to get started."
          icon="book"
          actionLabel="Add Course"
          onAction={() => window.location.href = '/admin/courses/new'}
        />
      )}
    </div>
  );
}

export default function AdminCoursesPage() {
  return (
    <AdminGuard>
      <CoursesContent />
    </AdminGuard>
  );
}
