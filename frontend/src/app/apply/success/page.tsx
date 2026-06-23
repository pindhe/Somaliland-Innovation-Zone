'use client';

import Link from 'next/link';
import { motion } from 'framer-motion';
import { CheckCircle, Home, BookOpen } from 'lucide-react';
import { StudentNavbar } from '@/components/layout/StudentNavbar';
import { StudentFooter } from '@/components/layout/StudentFooter';
import { Button } from '@/components/ui/Button';

export default function SuccessPage() {
  return (
    <div className="flex min-h-screen flex-col">
      <StudentNavbar />
      <main className="flex flex-1 items-center justify-center px-4 py-16">
        <motion.div
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 0.5 }}
          className="mx-auto max-w-lg text-center"
        >
          <motion.div
            initial={{ scale: 0 }}
            animate={{ scale: 1 }}
            transition={{ delay: 0.2, type: 'spring', stiffness: 200 }}
            className="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-full bg-accent-100 dark:bg-accent-900/30"
          >
            <CheckCircle className="h-14 w-14 text-accent-500" />
          </motion.div>

          <h1 className="mb-4 text-3xl font-bold text-gray-900 dark:text-white">
            Application Submitted!
          </h1>

          <div className="glass-card mb-8 text-left">
            <p className="mb-4 text-gray-600 dark:text-gray-300 leading-relaxed">
              Thank you for your application.
            </p>
            <p className="mb-4 text-gray-600 dark:text-gray-300 leading-relaxed">
              Your registration has been successfully submitted and is currently under review by our administration team.
            </p>
            <p className="mb-4 text-gray-600 dark:text-gray-300 leading-relaxed">
              You will be contacted soon regarding the outcome of your application.
            </p>
            <p className="text-gray-600 dark:text-gray-300 leading-relaxed">
              Please wait for further communication.
            </p>
          </div>

          <div className="flex flex-col gap-3 sm:flex-row sm:justify-center">
            <Link href="/">
              <Button variant="primary" className="w-full sm:w-auto">
                <Home className="h-4 w-4" /> Return Home
              </Button>
            </Link>
            <Link href="/courses">
              <Button variant="secondary" className="w-full sm:w-auto">
                <BookOpen className="h-4 w-4" /> Browse More Courses
              </Button>
            </Link>
          </div>
        </motion.div>
      </main>
      <StudentFooter />
    </div>
  );
}
