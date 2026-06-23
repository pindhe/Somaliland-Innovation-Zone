'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import { AdminGuard } from '@/components/admin/AdminGuard';
import { Input, Select, Textarea } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';
import { api } from '@/lib/api';
import { COURSE_CATEGORIES } from '@/lib/utils';
import { useRouter } from 'next/navigation';

const categoryOptions = COURSE_CATEGORIES.map((c) => ({ value: c, label: c }));

function EditCourseForm() {
  const params = useParams();
  const router = useRouter();
  const courseId = Number(params.id);
  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(true);
  const [form, setForm] = useState({
    title: '', category: '', description: '', duration: '',
    training_type: 'free', instructor: '', seats: '30',
    requirements: '', outcomes: '', status: 'draft',
    start_date: '', end_date: '',
  });
  const [image, setImage] = useState<File | null>(null);
  const [error, setError] = useState('');

  useEffect(() => {
    api.getCourse(courseId).then((course) => {
      setForm({
        title: course.title,
        category: course.category,
        description: course.description,
        duration: course.duration,
        training_type: course.training_type,
        instructor: course.instructor,
        seats: String(course.seats),
        requirements: course.requirements || '',
        outcomes: course.outcomes || '',
        status: course.status,
        start_date: course.start_date,
        end_date: course.end_date,
      });
    }).finally(() => setFetching(false));
  }, [courseId]);

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
      await api.updateCourse(courseId, data);
      router.push('/admin/courses');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to update');
    } finally {
      setLoading(false);
    }
  };

  if (fetching) return <div className="animate-pulse glass-card h-96" />;

  return (
    <form onSubmit={handleSubmit} className="mx-auto max-w-3xl space-y-6">
      <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Edit Course</h1>
      <div className="glass-card grid gap-4 sm:grid-cols-2">
        <Input label="Course Title *" value={form.title} onChange={(e) => update('title', e.target.value)} required className="sm:col-span-2" />
        <Select label="Category *" options={categoryOptions} value={form.category} onChange={(e) => update('category', e.target.value)} />
        <Input label="Duration *" value={form.duration} onChange={(e) => update('duration', e.target.value)} required />
        <Select label="Training Type *" options={[{ value: 'free', label: 'Free' }, { value: 'paid', label: 'Paid' }]} value={form.training_type} onChange={(e) => update('training_type', e.target.value)} />
        <Input label="Instructor *" value={form.instructor} onChange={(e) => update('instructor', e.target.value)} required />
        <Input label="Seats *" type="number" value={form.seats} onChange={(e) => update('seats', e.target.value)} required />
        <Input label="Start Date *" type="date" value={form.start_date} onChange={(e) => update('start_date', e.target.value)} required />
        <Input label="End Date *" type="date" value={form.end_date} onChange={(e) => update('end_date', e.target.value)} required />
        <Select label="Status" options={[
          { value: 'draft', label: 'Draft' }, { value: 'open', label: 'Open' },
          { value: 'closed', label: 'Closed' }, { value: 'archived', label: 'Archived' },
        ]} value={form.status} onChange={(e) => update('status', e.target.value)} />
        <div className="sm:col-span-2">
          <label className="label">Update Image</label>
          <input type="file" accept="image/*" onChange={(e) => setImage(e.target.files?.[0] || null)} className="input-field" />
        </div>
        <Textarea label="Description *" value={form.description} onChange={(e) => update('description', e.target.value)} required className="sm:col-span-2" />
        <Textarea label="Learning Outcomes" value={form.outcomes} onChange={(e) => update('outcomes', e.target.value)} className="sm:col-span-2" />
        <Textarea label="Requirements" value={form.requirements} onChange={(e) => update('requirements', e.target.value)} className="sm:col-span-2" />
      </div>
      {error && <p className="text-sm text-red-500">{error}</p>}
      <div className="flex gap-3">
        <Button type="submit" loading={loading}>Update Course</Button>
        <Button type="button" variant="secondary" onClick={() => router.back()}>Cancel</Button>
      </div>
    </form>
  );
}

export default function EditCoursePage() {
  return (
    <AdminGuard>
      <EditCourseForm />
    </AdminGuard>
  );
}
