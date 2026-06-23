import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import './globals.css';
import { ThemeProvider } from '@/contexts/ThemeContext';
import { AuthProvider } from '@/contexts/AuthContext';

const inter = Inter({ subsets: ['latin'] });

export const metadata: Metadata = {
  title: {
    default: 'SIZSR - Somaliland Innovation Zone Student Registration',
    template: '%s | SIZSR',
  },
  description:
    'Apply for professional training programs at Somaliland Innovation Zone. Browse courses, submit applications, and start your learning journey.',
  keywords: ['student registration', 'training', 'courses', 'Somaliland', 'innovation'],
  openGraph: {
    title: 'SIZSR - Student Registration System',
    description: 'Professional training and course management platform',
    type: 'website',
  },
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body className={inter.className}>
        <ThemeProvider>
          <AuthProvider>
            {children}
          </AuthProvider>
        </ThemeProvider>
      </body>
    </html>
  );
}
