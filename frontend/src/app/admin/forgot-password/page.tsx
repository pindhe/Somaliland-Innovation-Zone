'use client';

import { useState } from 'react';
import Link from 'next/link';
import { api } from '@/lib/api';
import { Input } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';
import { ThemeToggle } from '@/components/ui/ThemeToggle';

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState('');
  const [sent, setSent] = useState(false);
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      await api.forgotPassword(email);
      setSent(true);
    } catch {
      setSent(true);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center px-4">
      <div className="absolute right-4 top-4"><ThemeToggle /></div>
      <div className="w-full max-w-md">
        <h1 className="mb-2 text-2xl font-bold text-gray-900 dark:text-white">Forgot Password</h1>
        <p className="mb-6 text-gray-500">Enter your email to receive a reset link</p>

        {sent ? (
          <div className="glass-card text-center">
            <p className="text-gray-600 dark:text-gray-300">
              If an account exists with this email, a reset link has been sent.
            </p>
            <Link href="/admin/login" className="btn-primary mt-4 inline-flex">Back to Login</Link>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="glass-card space-y-4">
            <Input label="Email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
            <Button type="submit" className="w-full" loading={loading}>Send Reset Link</Button>
          </form>
        )}
      </div>
    </div>
  );
}
