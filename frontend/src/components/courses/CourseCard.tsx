'use client';

import Link from 'next/link';
import Image from 'next/image';
import { motion } from 'framer-motion';
import { Clock, User, Users, ArrowRight } from 'lucide-react';
import type { Course } from '@/lib/types';
import { Badge } from '@/components/ui/Badge';
import { STATUS_COLORS } from '@/lib/utils';

interface CourseCardProps {
  course: Course;
  index?: number;
}

export function CourseCard({ course, index = 0 }: CourseCardProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay: index * 0.1, duration: 0.4 }}
      className="group glass-card overflow-hidden !p-0"
    >
      <div className="relative h-48 overflow-hidden bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/40 dark:to-primary-800/40">
        {course.image_url ? (
          <Image
            src={course.image_url}
            alt={course.title}
            fill
            className="object-cover transition-transform duration-500 group-hover:scale-105"
          />
        ) : (
          <div className="flex h-full items-center justify-center">
            <span className="text-4xl font-bold text-primary-300">{course.title.charAt(0)}</span>
          </div>
        )}
        <div className="absolute left-3 top-3 flex gap-2">
          <Badge className={course.training_type === 'free' ? 'bg-accent-500 text-white' : 'bg-primary-600 text-white'}>
            {course.training_type === 'free' ? 'Free' : 'Paid'}
          </Badge>
        </div>
      </div>

      <div className="p-5">
        <Badge className="mb-2 bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
          {course.category}
        </Badge>
        <h3 className="mb-2 line-clamp-2 text-lg font-semibold text-gray-900 dark:text-white">
          {course.title}
        </h3>
        <p className="mb-4 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">
          {course.description}
        </p>

        <div className="mb-4 flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
          <span className="flex items-center gap-1"><Clock className="h-3.5 w-3.5" /> {course.duration}</span>
          <span className="flex items-center gap-1"><User className="h-3.5 w-3.5" /> {course.instructor}</span>
          <span className="flex items-center gap-1"><Users className="h-3.5 w-3.5" /> {course.seats_available} seats</span>
        </div>

        <Link
          href={`/courses/${course.id}`}
          className="btn-primary w-full !py-2.5 text-sm group-hover:shadow-md"
        >
          View & Apply
          <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
        </Link>
      </div>
    </motion.div>
  );
}
