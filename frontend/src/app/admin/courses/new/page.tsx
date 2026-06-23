'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { AdminGuard } from '@/components/admin/AdminGuard';
import { Input, Select, Textarea } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';
import { api } from '@/lib/api';
import { COURSE_CATEGORIES } from '@/lib/utils';

const categoryOptions = COURSE_CATEGORIES.map((c) => ({ value: c, label: c }));

function CourseForm({ courseId }: { courseId?: number }) {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [form, setForm] = useState({
    title: '', category: '', description: '', duration: '',
    training_type: 'free', instructor: '', seats: '30',
    requirements: '', outcomes: '', status: 'draft',
    start_date: '', end_date: '',
  });
  const [image, setImage] = useState<File | null>(null);
  const [error, setError] = useState('');

  const update = (field: string, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    const data = new FormData();
    Object.entries(form).forEach(([key, val]) => data.append(key, val));
    if (image) data.append('image', image);

    try {
      if (courseId) {
        await api.updateCourse(courseId, data);
      } else {
        await api.createCourse(data);
      }
      router.push('/admin/courses');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save course');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="mx-auto max-w-3xl space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
          {courseId ? 'Edit Course' : 'Add New Course'}
        </h1>
        <p className="text-gray-500">Fill in the course details below</p>
      </div>

      <div className="glass-card grid gap-4 sm:grid-cols-2">
        <Input label="Course Title *" value={form.title} onChange={(e) => update('title', e.target.value)} required className="sm:col-span-2" />
        <Select label="Category *" options={categoryOptions} value={form.category} onChange={(e) => update('category', e.target.value)} required />
        <Input label="Duration *" placeholder="e.g. 8 weeks" value={form.duration} onChange={(e) => update('duration', e.target.value)} required />
        <Select label="Training Type *" options={[{ value: 'free', label: 'Free' }, { value: 'paid', label: 'Paid' }]} value={form.training_type} onChange={(e) => update('training_type', e.target.value)} />
        <Input label="Instructor Name *" value={form.instructor} onChange={(e) => update('instructor', e.target.value)} required />
        <Input label="Available Seats *" type="number" min="1" value={form.seats} onChange={(e) => update('seats', e.target.value)} required />
        <Input label="Start Date *" type="date" value={form.start_date} onChange={(e) => update('start_date', e.target.value)} required />
        <Input label="End Date *" type="date" value={form.end_date} onChange={(e) => update('end_date', e.target.value)} required />
        <Select label="Status" options={[
          { value: 'draft', label: 'Draft' }, { value: 'open', label: 'Open' },
          { value: 'closed', label: 'Closed' }, { value: 'archived', label: 'Archived' },
        ]} value={form.status} onChange={(e) => update('status', e.target.value)} />
        <div className="sm:col-span-2">
          <label className="label">Course Image</label>
          <input type="file" accept="image/*" onChange={(e) => setImage(e.target.files?.[0] || null)} className="input-field" />
        </div>
        <Textarea label="Course Description *" value={form.description} onChange={(e) => update('description', e.target.value)} required className="sm:col-span-2" />
        <Textarea label="Learning Outcomes" value={form.outcomes} onChange={(e) => update('outcomes', e.target.value)} className="sm:col-span-2" placeholder="One outcome per line" />
        <Textarea label="Course Requirements" value={form.requirements} onChange={(e) => update('requirements', e.target.value)} className="sm:col-span-2" placeholder="One requirement per line" />
      </div>

      {error && <p className="text-sm text-red-500">{error}</p>}

      <div className="flex gap-3">
        <Button type="submit" loading={loading}>{courseId ? 'Update Course' : 'Create Course'}</Button>
        <Button type="button" variant="secondary" onClick={() => router.back()}>Cancel</Button>
      </div>
    </form>
  );
}

export default function NewCoursePage() {
  return (
    <AdminGuard>
      <CourseForm />
    </AdminGuard>
  );
}
