'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { motion } from 'framer-motion';
import {
  ArrowRight, BookOpen, Award, Users, Sparkles,
  Code, Smartphone, Brain, Megaphone,
} from 'lucide-react';
import { api } from '@/lib/api';
import type { Course } from '@/lib/types';
import { CourseCard } from '@/components/courses/CourseCard';

const features = [
  { icon: BookOpen, title: 'Expert-Led Courses', desc: 'Learn from industry professionals with real-world experience.' },
  { icon: Award, title: 'Certified Programs', desc: 'Earn recognized certificates upon successful completion.' },
  { icon: Users, title: 'Community Learning', desc: 'Join a vibrant community of innovators and entrepreneurs.' },
  { icon: Sparkles, title: 'Hands-On Projects', desc: 'Build practical skills through project-based learning.' },
];

const categories = [
  { icon: Code, name: 'Web Development', color: 'from-blue-500 to-blue-600' },
  { icon: Smartphone, name: 'Mobile Development', color: 'from-purple-500 to-purple-600' },
  { icon: Brain, name: 'Artificial Intelligence', color: 'from-indigo-500 to-indigo-600' },
  { icon: Megaphone, name: 'Digital Marketing', color: 'from-pink-500 to-pink-600' },
];

export function HomePage() {
  const [courses, setCourses] = useState<Course[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.getFeaturedCourses()
      .then(setCourses)
      .catch(() => setCourses([]))
      .finally(() => setLoading(false));
  }, []);

  return (
    <>
      {/* Hero */}
      <section className="hero-gradient relative overflow-hidden">
        <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djItaDJ2LTJoLTJ6bTAtNHYyaDJ2LTJoLTJ6bTAtNHYyaDJ2LTJoLTJ6bTAtNHYyaDJ2LTJoLTJ6bTAtNHYyaDJ2LTJoLTJ6bTAtNHYyaDJ2LTJoLTJ6bTAtNHYyaDJ2LTJoLTJ6bTAtNHYyaDJ2LTJoLTJ6bTAtNHYyaDJ2LTJoLTJ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30" />
        <div className="page-container relative py-20 sm:py-28 lg:py-36">
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="mx-auto max-w-3xl text-center"
          >
            <span className="mb-4 inline-block rounded-full bg-white/10 px-4 py-1.5 text-sm font-medium text-white/90 backdrop-blur-sm">
              Somaliland Innovation Zone
            </span>
            <h1 className="mb-6 text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
              Transform Your Future with{' '}
              <span className="text-accent-300">Professional Training</span>
            </h1>
            <p className="mb-8 text-lg text-primary-100 sm:text-xl">
              Discover world-class training programs designed to equip you with in-demand skills.
              Apply today and join the next generation of innovators.
            </p>
            <div className="flex flex-col items-center justify-center gap-4 sm:flex-row">
              <Link href="/courses" className="btn-accent !px-8 !py-4 text-base shadow-lg">
                Browse Courses
                <ArrowRight className="h-5 w-5" />
              </Link>
              <Link href="/courses" className="rounded-xl border-2 border-white/30 bg-white/10 px-8 py-4 text-base font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/20">
                Apply Now
              </Link>
            </div>
          </motion.div>
        </div>
        <div className="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-gray-50 to-transparent dark:from-gray-950" />
      </section>

      {/* Features */}
      <section className="page-container py-16 sm:py-20">
        <div className="mb-12 text-center">
          <h2 className="mb-3 text-3xl font-bold text-gray-900 dark:text-white">Why Choose SIZSR?</h2>
          <p className="text-gray-500 dark:text-gray-400">Everything you need to launch your career</p>
        </div>
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          {features.map((f, i) => (
            <motion.div
              key={f.title}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: i * 0.1 }}
              className="glass-card text-center"
            >
              <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                <f.icon className="h-6 w-6" />
              </div>
              <h3 className="mb-2 font-semibold text-gray-900 dark:text-white">{f.title}</h3>
              <p className="text-sm text-gray-500 dark:text-gray-400">{f.desc}</p>
            </motion.div>
          ))}
        </div>
      </section>

      {/* Categories */}
      <section className="bg-gray-100/50 py-16 dark:bg-gray-900/50 sm:py-20">
        <div className="page-container">
          <div className="mb-12 text-center">
            <h2 className="mb-3 text-3xl font-bold text-gray-900 dark:text-white">Explore Categories</h2>
            <p className="text-gray-500 dark:text-gray-400">Find the perfect program for your goals</p>
          </div>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {categories.map((cat, i) => (
              <motion.div
                key={cat.name}
                initial={{ opacity: 0, scale: 0.95 }}
                whileInView={{ opacity: 1, scale: 1 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.1 }}
              >
                <Link
                  href={`/courses?category=${encodeURIComponent(cat.name)}`}
                  className={`flex items-center gap-4 rounded-2xl bg-gradient-to-r ${cat.color} p-5 text-white shadow-soft transition-transform hover:scale-[1.02]`}
                >
                  <cat.icon className="h-8 w-8 shrink-0" />
                  <span className="font-semibold">{cat.name}</span>
                </Link>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Featured Courses */}
      <section className="page-container py-16 sm:py-20">
        <div className="mb-12 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
          <div>
            <h2 className="mb-2 text-3xl font-bold text-gray-900 dark:text-white">Featured Programs</h2>
            <p className="text-gray-500 dark:text-gray-400">Start your learning journey today</p>
          </div>
          <Link href="/courses" className="btn-secondary !py-2.5">
            View All Courses <ArrowRight className="h-4 w-4" />
          </Link>
        </div>

        {loading ? (
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {[1, 2, 3].map((n) => (
              <div key={n} className="glass-card h-80 animate-pulse !p-0">
                <div className="h-48 bg-gray-200 dark:bg-gray-700" />
                <div className="space-y-3 p-5">
                  <div className="h-4 w-1/3 rounded bg-gray-200 dark:bg-gray-700" />
                  <div className="h-5 w-3/4 rounded bg-gray-200 dark:bg-gray-700" />
                  <div className="h-4 w-full rounded bg-gray-200 dark:bg-gray-700" />
                </div>
              </div>
            ))}
          </div>
        ) : courses.length > 0 ? (
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {courses.map((course, i) => (
              <CourseCard key={course.id} course={course} index={i} />
            ))}
          </div>
        ) : (
          <div className="glass-card text-center py-12">
            <p className="text-gray-500">No courses available at the moment. Check back soon!</p>
          </div>
        )}
      </section>

      {/* CTA */}
      <section className="page-container pb-16 sm:pb-20">
        <div className="hero-gradient relative overflow-hidden rounded-3xl p-8 text-center sm:p-12">
          <h2 className="mb-4 text-2xl font-bold text-white sm:text-3xl">Ready to Start Your Journey?</h2>
          <p className="mb-6 text-primary-100">Join hundreds of students already transforming their careers.</p>
          <Link href="/courses" className="btn-accent !px-8">
            Get Started Today <ArrowRight className="h-5 w-5" />
          </Link>
        </div>
      </section>
    </>
  );
}
