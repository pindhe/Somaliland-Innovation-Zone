'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import { motion } from 'framer-motion';
import {
  ArrowLeft, Calendar, Clock, User, Users, CheckCircle, BookOpen,
} from 'lucide-react';
import { StudentNavbar } from '@/components/layout/StudentNavbar';
import { StudentFooter } from '@/components/layout/StudentFooter';
import { Badge } from '@/components/ui/Badge';
import { Button } from '@/components/ui/Button';
import { api } from '@/lib/api';
import type { Course } from '@/lib/types';
import { formatDate } from '@/lib/utils';

export default function CourseDetailPage() {
  const params = useParams();
  const [course, setCourse] = useState<Course | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const id = Number(params.id);
    if (!id) return;
    api.getCourse(id)
      .then(setCourse)
      .catch(() => setCourse(null))
      .finally(() => setLoading(false));
  }, [params.id]);

  if (loading) {
    return (
      <div className="flex min-h-screen flex-col">
        <StudentNavbar />
        <main className="flex-1 page-container py-12">
          <div className="animate-pulse space-y-6">
            <div className="h-64 rounded-2xl bg-gray-200 dark:bg-gray-700" />
            <div className="h-8 w-2/3 rounded bg-gray-200 dark:bg-gray-700" />
            <div className="h-4 w-full rounded bg-gray-200 dark:bg-gray-700" />
          </div>
        </main>
        <StudentFooter />
      </div>
    );
  }

  if (!course) {
    return (
      <div className="flex min-h-screen flex-col">
        <StudentNavbar />
        <main className="flex-1 page-container py-20 text-center">
          <h1 className="text-2xl font-bold">Course not found</h1>
          <Link href="/courses" className="btn-primary mt-4 inline-flex">Back to Courses</Link>
        </main>
        <StudentFooter />
      </div>
    );
  }

  const outcomes = course.outcomes?.split('\n').filter(Boolean) || [];
  const requirements = course.requirements?.split('\n').filter(Boolean) || [];

  return (
    <div className="flex min-h-screen flex-col">
      <StudentNavbar />
      <main className="flex-1">
        <div className="page-container py-8 sm:py-12">
          <Link href="/courses" className="mb-6 inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary-600">
            <ArrowLeft className="h-4 w-4" /> Back to Courses
          </Link>

          <div className="grid gap-8 lg:grid-cols-3">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              className="lg:col-span-2"
            >
              <div className="relative mb-6 h-64 overflow-hidden rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 sm:h-80 dark:from-primary-900/40 dark:to-primary-800/40">
                {course.image_url ? (
                  <Image src={course.image_url} alt={course.title} fill className="object-cover" />
                ) : (
                  <div className="flex h-full items-center justify-center">
                    <BookOpen className="h-20 w-20 text-primary-300" />
                  </div>
                )}
              </div>

              <div className="mb-4 flex flex-wrap gap-2">
                <Badge className="bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                  {course.category}
                </Badge>
                <Badge className={course.training_type === 'free' ? 'bg-accent-500 text-white' : 'bg-primary-600 text-white'}>
                  {course.training_type === 'free' ? 'Free Training' : 'Paid Course'}
                </Badge>
              </div>

              <h1 className="mb-4 text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                {course.title}
              </h1>
              <p className="mb-8 text-gray-600 dark:text-gray-300 leading-relaxed">
                {course.description}
              </p>

              {outcomes.length > 0 && (
                <div className="glass-card mb-6">
                  <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Learning Outcomes</h2>
                  <ul className="space-y-2">
                    {outcomes.map((item, i) => (
                      <li key={i} className="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <CheckCircle className="mt-0.5 h-4 w-4 shrink-0 text-accent-500" />
                        {item}
                      </li>
                    ))}
                  </ul>
                </div>
              )}

              {requirements.length > 0 && (
                <div className="glass-card">
                  <h2 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Requirements</h2>
                  <ul className="space-y-2">
                    {requirements.map((item, i) => (
                      <li key={i} className="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary-500" />
                        {item}
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </motion.div>

            <motion.div
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ delay: 0.2 }}
            >
              <div className="glass-card sticky top-24">
                <h3 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Course Details</h3>
                <div className="space-y-4 text-sm">
                  <div className="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                    <User className="h-5 w-5 text-primary-500" />
                    <div><span className="text-gray-400">Instructor</span><p className="font-medium">{course.instructor}</p></div>
                  </div>
                  <div className="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                    <Clock className="h-5 w-5 text-primary-500" />
                    <div><span className="text-gray-400">Duration</span><p className="font-medium">{course.duration}</p></div>
                  </div>
                  <div className="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                    <Calendar className="h-5 w-5 text-primary-500" />
                    <div>
                      <span className="text-gray-400">Dates</span>
                      <p className="font-medium">{formatDate(course.start_date)} – {formatDate(course.end_date)}</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-3 text-gray-600 dark:text-gray-300">
                    <Users className="h-5 w-5 text-primary-500" />
                    <div>
                      <span className="text-gray-400">Seats Available</span>
                      <p className="font-medium">{course.seats_available} of {course.seats}</p>
                    </div>
                  </div>
                </div>

                <div className="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                  {course.seats_available > 0 ? (
                    <Link href={`/apply/${course.id}`}>
                      <Button className="w-full" size="lg">Apply Now</Button>
                    </Link>
                  ) : (
                    <Button className="w-full" disabled>No Seats Available</Button>
                  )}
                </div>
              </div>
            </motion.div>
          </div>
        </div>
      </main>
      <StudentFooter />
    </div>
  );
}
