import Link from 'next/link';
import { GraduationCap, Mail, MapPin, Phone } from 'lucide-react';

export function StudentFooter() {
  return (
    <footer className="border-t border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
      <div className="page-container py-12">
        <div className="grid gap-8 md:grid-cols-3">
          <div>
            <div className="mb-4 flex items-center gap-2">
              <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600 text-white">
                <GraduationCap className="h-4 w-4" />
              </div>
              <span className="font-bold text-gray-900 dark:text-white">SIZSR</span>
            </div>
            <p className="text-sm text-gray-500 dark:text-gray-400">
              Somaliland Innovation Zone Student Registration System. Empowering students through professional training.
            </p>
          </div>
          <div>
            <h4 className="mb-3 font-semibold text-gray-900 dark:text-white">Quick Links</h4>
            <div className="flex flex-col gap-2 text-sm text-gray-500 dark:text-gray-400">
              <Link href="/" className="hover:text-primary-600">Home</Link>
              <Link href="/courses" className="hover:text-primary-600">Courses</Link>
            </div>
          </div>
          <div>
            <h4 className="mb-3 font-semibold text-gray-900 dark:text-white">Contact</h4>
            <div className="flex flex-col gap-2 text-sm text-gray-500 dark:text-gray-400">
              <span className="flex items-center gap-2"><MapPin className="h-4 w-4" /> Hargeisa, Somaliland</span>
              <span className="flex items-center gap-2"><Mail className="h-4 w-4" /> info@sizsr.local</span>
              <span className="flex items-center gap-2"><Phone className="h-4 w-4" /> +252 XX XXX XXXX</span>
            </div>
          </div>
        </div>
        <div className="mt-8 border-t border-gray-200 pt-8 text-center text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
          &copy; {new Date().getFullYear()} Somaliland Innovation Zone. All rights reserved.
        </div>
      </div>
    </footer>
  );
}
