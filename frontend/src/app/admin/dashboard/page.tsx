'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { motion } from 'framer-motion';
import {
  BookOpen, GraduationCap, Users, FileText, CheckCircle, XCircle, Clock,
  Plus, ArrowRight,
} from 'lucide-react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
  PieChart, Pie, Cell, LineChart, Line,
} from 'recharts';
import { AdminGuard } from '@/components/admin/AdminGuard';
import { api } from '@/lib/api';
import type { DashboardStats } from '@/lib/types';
import { formatDateTime, STATUS_COLORS } from '@/lib/utils';
import { Badge } from '@/components/ui/Badge';

const COLORS = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

function StatCard({ icon: Icon, label, value, color }: {
  icon: React.ElementType; label: string; value: number; color: string;
}) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      className="stat-card"
    >
      <div className={`flex h-10 w-10 items-center justify-center rounded-xl ${color}`}>
        <Icon className="h-5 w-5 text-white" />
      </div>
      <p className="text-2xl font-bold text-gray-900 dark:text-white">{value}</p>
      <p className="text-sm text-gray-500">{label}</p>
    </motion.div>
  );
}

function DashboardContent() {
  const [data, setData] = useState<DashboardStats | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.getDashboardStats()
      .then(setData)
      .catch(() => setData(null))
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return (
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {[1, 2, 3, 4, 5, 6, 7, 8].map((n) => (
          <div key={n} className="stat-card h-28 animate-pulse" />
        ))}
      </div>
    );
  }

  if (!data) {
    return <p className="text-gray-500">Failed to load dashboard data.</p>;
  }

  const { stats } = data;
  const pieData = [
    { name: 'Pending', value: stats.pending_applications },
    { name: 'Approved', value: stats.approved_applications },
    { name: 'Rejected', value: stats.rejected_applications },
  ];

  const trendData = data.application_trends.map((t) => ({
    month: new Date(t.month).toLocaleDateString('en-US', { month: 'short', year: '2-digit' }),
    count: t.count,
  }));

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
          <p className="text-gray-500">Overview of your training platform</p>
        </div>
        <div className="flex gap-2">
          <Link href="/admin/courses/new" className="btn-primary !py-2.5 text-sm">
            <Plus className="h-4 w-4" /> Add Course
          </Link>
        </div>
      </div>

      {/* Stats Grid */}
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard icon={BookOpen} label="Total Courses" value={stats.total_courses} color="bg-primary-600" />
        <StatCard icon={GraduationCap} label="Active Courses" value={stats.active_courses} color="bg-accent-500" />
        <StatCard icon={Users} label="Free Trainings" value={stats.free_trainings} color="bg-blue-500" />
        <StatCard icon={BookOpen} label="Paid Courses" value={stats.paid_courses} color="bg-indigo-600" />
        <StatCard icon={FileText} label="Total Applications" value={stats.total_applications} color="bg-purple-600" />
        <StatCard icon={CheckCircle} label="Approved" value={stats.approved_applications} color="bg-accent-500" />
        <StatCard icon={XCircle} label="Rejected" value={stats.rejected_applications} color="bg-red-500" />
        <StatCard icon={Clock} label="Pending" value={stats.pending_applications} color="bg-amber-500" />
      </div>

      {/* Charts */}
      <div className="grid gap-6 lg:grid-cols-2">
        <div className="glass-card">
          <h3 className="mb-4 font-semibold text-gray-900 dark:text-white">Application Status</h3>
          <ResponsiveContainer width="100%" height={250}>
            <PieChart>
              <Pie data={pieData} cx="50%" cy="50%" innerRadius={60} outerRadius={90} dataKey="value" label>
                {pieData.map((_, i) => <Cell key={i} fill={COLORS[i]} />)}
              </Pie>
              <Tooltip />
            </PieChart>
          </ResponsiveContainer>
        </div>

        <div className="glass-card">
          <h3 className="mb-4 font-semibold text-gray-900 dark:text-white">Monthly Registration Growth</h3>
          <ResponsiveContainer width="100%" height={250}>
            <LineChart data={trendData}>
              <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
              <XAxis dataKey="month" tick={{ fontSize: 12 }} />
              <YAxis tick={{ fontSize: 12 }} />
              <Tooltip />
              <Line type="monotone" dataKey="count" stroke="#3b82f6" strokeWidth={2} dot={{ fill: '#3b82f6' }} />
            </LineChart>
          </ResponsiveContainer>
        </div>

        <div className="glass-card lg:col-span-2">
          <h3 className="mb-4 font-semibold text-gray-900 dark:text-white">Course Registration Analytics</h3>
          <ResponsiveContainer width="100%" height={250}>
            <BarChart data={data.course_registration_analytics}>
              <CartesianGrid strokeDasharray="3 3" stroke="#e5e7eb" />
              <XAxis dataKey="course" tick={{ fontSize: 11 }} />
              <YAxis tick={{ fontSize: 12 }} />
              <Tooltip />
              <Bar dataKey="count" fill="#3b82f6" radius={[4, 4, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Widgets */}
      <div className="grid gap-6 lg:grid-cols-2">
        <div className="glass-card">
          <div className="mb-4 flex items-center justify-between">
            <h3 className="font-semibold text-gray-900 dark:text-white">Recent Applications</h3>
            <Link href="/admin/applications" className="text-sm text-primary-600 hover:underline flex items-center gap-1">
              View all <ArrowRight className="h-3 w-3" />
            </Link>
          </div>
          <div className="space-y-3">
            {data.recent_applications.length > 0 ? data.recent_applications.map((app) => (
              <div key={app.id} className="flex items-center justify-between rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
                <div>
                  <p className="text-sm font-medium text-gray-900 dark:text-white">{app.full_name}</p>
                  <p className="text-xs text-gray-500">{app.course}</p>
                </div>
                <Badge className={STATUS_COLORS[app.status]}>{app.status}</Badge>
              </div>
            )) : (
              <p className="text-sm text-gray-500">No applications yet</p>
            )}
          </div>
        </div>

        <div className="glass-card">
          <div className="mb-4 flex items-center justify-between">
            <h3 className="font-semibold text-gray-900 dark:text-white">Latest Courses</h3>
            <Link href="/admin/courses" className="text-sm text-primary-600 hover:underline flex items-center gap-1">
              View all <ArrowRight className="h-3 w-3" />
            </Link>
          </div>
          <div className="space-y-3">
            {data.latest_courses.map((course) => (
              <div key={course.id} className="flex items-center justify-between rounded-xl bg-gray-50 p-3 dark:bg-gray-800/50">
                <div>
                  <p className="text-sm font-medium text-gray-900 dark:text-white">{course.title}</p>
                  <p className="text-xs text-gray-500">{course.category}</p>
                </div>
                <Badge className={STATUS_COLORS[course.status]}>{course.status}</Badge>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="glass-card">
        <h3 className="mb-4 font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
        <div className="flex flex-wrap gap-3">
          <Link href="/admin/courses/new" className="btn-primary !py-2 text-sm"><Plus className="h-4 w-4" /> Add Course</Link>
          <Link href="/admin/applications" className="btn-secondary !py-2 text-sm"><FileText className="h-4 w-4" /> Review Applications</Link>
          <Link href="/admin/notifications" className="btn-secondary !py-2 text-sm"><Users className="h-4 w-4" /> Send Notification</Link>
        </div>
      </div>
    </div>
  );
}

export default function DashboardPage() {
  return (
    <AdminGuard>
      <DashboardContent />
    </AdminGuard>
  );
}
