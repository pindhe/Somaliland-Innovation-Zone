'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { motion } from 'framer-motion';
import { GraduationCap, Eye, EyeOff } from 'lucide-react';
import { useAuth } from '@/contexts/AuthContext';
import { Input } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';
import { ThemeToggle } from '@/components/ui/ThemeToggle';

export default function AdminLoginPage() {
  const { login } = useAuth();
  const router = useRouter();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      await login(username, password, remember);
      router.push('/admin/dashboard');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex min-h-screen">
      <div className="hidden w-1/2 hero-gradient lg:flex lg:flex-col lg:items-center lg:justify-center lg:p-12">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="max-w-md text-center text-white"
        >
          <GraduationCap className="mx-auto mb-6 h-16 w-16" />
          <h1 className="mb-4 text-3xl font-bold">SIZSR Admin Panel</h1>
          <p className="text-primary-100">
            Manage courses, review applications, and oversee the student registration system.
          </p>
        </motion.div>
      </div>

      <div className="flex flex-1 flex-col items-center justify-center px-4 py-12">
        <div className="absolute right-4 top-4">
          <ThemeToggle />
        </div>

        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          className="w-full max-w-md"
        >
          <div className="mb-8 text-center lg:text-left">
            <div className="mb-4 flex items-center justify-center gap-2 lg:justify-start">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-600 text-white lg:hidden">
                <GraduationCap className="h-5 w-5" />
              </div>
              <h2 className="text-2xl font-bold text-gray-900 dark:text-white">Admin Login</h2>
            </div>
            <p className="text-gray-500">Sign in to access the admin dashboard</p>
          </div>

          <form onSubmit={handleSubmit} className="glass-card space-y-4">
            <Input
              label="Username"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              required
              autoComplete="username"
            />
            <div className="relative">
              <Input
                label="Password"
                type={showPassword ? 'text' : 'password'}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                autoComplete="current-password"
              />
              <button
                type="button"
                onClick={() => setShowPassword(!showPassword)}
                className="absolute right-3 top-9 text-gray-400 hover:text-gray-600"
              >
                {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
              </button>
            </div>

            <div className="flex items-center justify-between">
              <label className="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <input
                  type="checkbox"
                  checked={remember}
                  onChange={(e) => setRemember(e.target.checked)}
                  className="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
                Remember me
              </label>
              <Link href="/admin/forgot-password" className="text-sm text-primary-600 hover:underline">
                Forgot password?
              </Link>
            </div>

            {error && <p className="text-sm text-red-500">{error}</p>}

            <Button type="submit" className="w-full" loading={loading}>
              Sign In
            </Button>
          </form>

          <p className="mt-6 text-center text-sm text-gray-500">
            <Link href="/" className="text-primary-600 hover:underline">Back to student portal</Link>
          </p>
        </motion.div>
      </div>
    </div>
  );
}
