'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import {
  LayoutDashboard, BookOpen, FileText, Bell, LogOut,
  GraduationCap, Menu, X, ChevronLeft,
} from 'lucide-react';
import { useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { ThemeToggle } from '@/components/ui/ThemeToggle';
import { cn } from '@/lib/utils';

const navItems = [
  { href: '/admin/dashboard', label: 'Dashboard', icon: LayoutDashboard },
  { href: '/admin/courses', label: 'Courses', icon: BookOpen },
  { href: '/admin/applications', label: 'Applications', icon: FileText },
  { href: '/admin/notifications', label: 'Notifications', icon: Bell },
];

export function AdminSidebar() {
  const pathname = usePathname();
  const { user, logout } = useAuth();
  const [collapsed, setCollapsed] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);

  const sidebar = (
    <aside className={cn(
      'flex h-full flex-col border-r border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 transition-all duration-300',
      collapsed ? 'w-[72px]' : 'w-64',
    )}>
      <div className="flex h-16 items-center justify-between border-b border-gray-200 px-4 dark:border-gray-800">
        {!collapsed && (
          <Link href="/admin/dashboard" className="flex items-center gap-2">
            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600 text-white">
              <GraduationCap className="h-4 w-4" />
            </div>
            <span className="font-bold text-gray-900 dark:text-white">SIZSR Admin</span>
          </Link>
        )}
        <button
          onClick={() => setCollapsed(!collapsed)}
          className="hidden rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 lg:block dark:hover:bg-gray-800"
        >
          <ChevronLeft className={cn('h-5 w-5 transition-transform', collapsed && 'rotate-180')} />
        </button>
      </div>

      <nav className="flex-1 space-y-1 p-3">
        {navItems.map((item) => {
          const active = pathname.startsWith(item.href);
          return (
            <Link
              key={item.href}
              href={item.href}
              onClick={() => setMobileOpen(false)}
              className={cn(
                'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors',
                active
                  ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300'
                  : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800',
              )}
            >
              <item.icon className="h-5 w-5 shrink-0" />
              {!collapsed && item.label}
            </Link>
          );
        })}
      </nav>

      <div className="border-t border-gray-200 p-3 dark:border-gray-800">
        {!collapsed && user && (
          <div className="mb-3 px-3">
            <p className="text-sm font-medium text-gray-900 dark:text-white">{user.username}</p>
            <p className="text-xs text-gray-500">{user.email}</p>
          </div>
        )}
        <button
          onClick={logout}
          className="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
        >
          <LogOut className="h-5 w-5 shrink-0" />
          {!collapsed && 'Logout'}
        </button>
      </div>
    </aside>
  );

  return (
    <>
      {/* Mobile toggle */}
      <div className="fixed left-0 right-0 top-0 z-40 flex h-14 items-center border-b border-gray-200 bg-white px-4 lg:hidden dark:border-gray-800 dark:bg-gray-900">
        <button onClick={() => setMobileOpen(true)} className="rounded-lg p-2">
          <Menu className="h-5 w-5" />
        </button>
        <span className="ml-3 font-bold">SIZSR Admin</span>
        <div className="ml-auto"><ThemeToggle /></div>
      </div>

      {/* Mobile overlay */}
      {mobileOpen && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="absolute inset-0 bg-black/50" onClick={() => setMobileOpen(false)} />
          <div className="relative h-full w-64">
            <button onClick={() => setMobileOpen(false)} className="absolute right-2 top-3 z-10 rounded-lg p-2 text-white">
              <X className="h-5 w-5" />
            </button>
            {sidebar}
          </div>
        </div>
      )}

      {/* Desktop sidebar */}
      <div className="hidden lg:block fixed left-0 top-0 h-screen z-30">
        {sidebar}
      </div>
    </>
  );
}

export function AdminLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-950">
      <AdminSidebar />
      <div className="lg:pl-64 transition-all duration-300">
        <header className="hidden h-16 items-center justify-end border-b border-gray-200 bg-white px-6 lg:flex dark:border-gray-800 dark:bg-gray-900">
          <ThemeToggle />
        </header>
        <main className="p-4 pt-16 lg:p-6 lg:pt-6">
          {children}
        </main>
      </div>
    </div>
  );
}
