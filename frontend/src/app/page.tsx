import { StudentNavbar } from '@/components/layout/StudentNavbar';
import { StudentFooter } from '@/components/layout/StudentFooter';
import { HomePage } from '@/components/home/HomePage';

export default function Page() {
  return (
    <div className="flex min-h-screen flex-col">
      <StudentNavbar />
      <main className="flex-1">
        <HomePage />
      </main>
      <StudentFooter />
    </div>
  );
}
