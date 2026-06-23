'use client';

import { useEffect, useState, Suspense } from 'react';
import { useSearchParams } from 'next/navigation';
import { Search } from 'lucide-react';
import { StudentNavbar } from '@/components/layout/StudentNavbar';
import { StudentFooter } from '@/components/layout/StudentFooter';
import { CourseCard } from '@/components/courses/CourseCard';
import { EmptyState } from '@/components/ui/EmptyState';
import { api } from '@/lib/api';
import type { Course } from '@/lib/types';
import { COURSE_CATEGORIES } from '@/lib/utils';

function CoursesContent() {
  const searchParams = useSearchParams();
  const [courses, setCourses] = useState<Course[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [category, setCategory] = useState(searchParams.get('category') || '');
  const [trainingType, setTrainingType] = useState('');

  useEffect(() => {
    const cat = searchParams.get('category');
    if (cat) setCategory(cat);
  }, [searchParams]);

  useEffect(() => {
    setLoading(true);
    const params: Record<string, string> = {};
    if (category) params.category = category;
    if (trainingType) params.training_type = trainingType;

    api.getCourses(params)
      .then(setCourses)
      .catch(() => setCourses([]))
      .finally(() => setLoading(false));
  }, [category, trainingType]);

  const filtered = courses.filter((c) =>
    !search || c.title.toLowerCase().includes(search.toLowerCase()) ||
    c.description.toLowerCase().includes(search.toLowerCase()),
  );

  return (
    <div className="page-container py-8 sm:py-12">
      <div className="mb-8">
        <h1 className="mb-2 text-3xl font-bold text-gray-900 dark:text-white">Available Courses</h1>
        <p className="text-gray-500 dark:text-gray-400">Browse and apply for training programs</p>
      </div>

      <div className="mb-8 flex flex-col gap-4 sm:flex-row">
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
        <select
          value={category}
          onChange={(e) => setCategory(e.target.value)}
          className="input-field sm:w-48"
        >
          <option value="">All Categories</option>
          {COURSE_CATEGORIES.map((c) => (
            <option key={c} value={c}>{c}</option>
          ))}
        </select>
        <select
          value={trainingType}
          onChange={(e) => setTrainingType(e.target.value)}
          className="input-field sm:w-40"
        >
          <option value="">All Types</option>
          <option value="free">Free</option>
          <option value="paid">Paid</option>
        </select>
      </div>

      {loading ? (
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {[1, 2, 3, 4, 5, 6].map((n) => (
            <div key={n} className="glass-card h-80 animate-pulse !p-0">
              <div className="h-48 bg-gray-200 dark:bg-gray-700" />
            </div>
          ))}
        </div>
      ) : filtered.length > 0 ? (
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {filtered.map((course, i) => (
            <CourseCard key={course.id} course={course} index={i} />
          ))}
        </div>
      ) : (
        <EmptyState
          title="No courses found"
          description="Try adjusting your filters or check back later for new programs."
          icon="book"
        />
      )}
    </div>
  );
}

export default function CoursesPage() {
  return (
    <div className="flex min-h-screen flex-col">
      <StudentNavbar />
      <main className="flex-1">
        <Suspense fallback={<div className="page-container py-12 text-center">Loading...</div>}>
          <CoursesContent />
        </Suspense>
      </main>
      <StudentFooter />
    </div>
  );
}
