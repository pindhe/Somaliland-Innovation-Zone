'use client';

import { useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { motion, AnimatePresence } from 'framer-motion';
import { Check, ChevronLeft, ChevronRight } from 'lucide-react';
import { StudentNavbar } from '@/components/layout/StudentNavbar';
import { StudentFooter } from '@/components/layout/StudentFooter';
import { Input, Select, Textarea } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';
import { api } from '@/lib/api';
import type { ApplicationFormData, Course } from '@/lib/types';
import { GENDER_OPTIONS, EDUCATION_OPTIONS, SCHEDULE_OPTIONS, formatDate } from '@/lib/utils';

const STEPS = [
  'Personal Information',
  'Educational Information',
  'Course Selection',
  'Motivation',
  'Review & Submit',
];

const initialForm: ApplicationFormData = {
  full_name: '',
  gender: '',
  date_of_birth: '',
  nationality: '',
  phone: '',
  email: '',
  address: '',
  education_level: '',
  institution: '',
  field_of_study: '',
  graduation_year: '',
  selected_course: '',
  preferred_schedule: 'flexible',
  motivation: '',
  career_goals: '',
  comments: '',
};

export default function ApplyPage() {
  const params = useParams();
  const router = useRouter();
  const courseId = Number(params.id);
  const [step, setStep] = useState(0);
  const [form, setForm] = useState<ApplicationFormData>({ ...initialForm, selected_course: courseId });
  const [course, setCourse] = useState<Course | null>(null);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (courseId) {
      api.getCourse(courseId).then(setCourse).catch(() => setCourse(null));
    }
  }, [courseId]);

  const update = (field: keyof ApplicationFormData, value: string | number) => {
    setForm((prev) => ({ ...prev, [field]: value }));
    setErrors((prev) => ({ ...prev, [field]: '' }));
  };

  const validateStep = (): boolean => {
    const e: Record<string, string> = {};
    if (step === 0) {
      if (!form.full_name.trim()) e.full_name = 'Required';
      if (!form.gender) e.gender = 'Required';
      if (!form.date_of_birth) e.date_of_birth = 'Required';
      if (!form.nationality.trim()) e.nationality = 'Required';
      if (!form.phone.trim()) e.phone = 'Required';
      if (!form.email.trim() || !/\S+@\S+\.\S+/.test(form.email)) e.email = 'Valid email required';
      if (!form.address.trim()) e.address = 'Required';
    } else if (step === 1) {
      if (!form.education_level) e.education_level = 'Required';
      if (!form.institution.trim()) e.institution = 'Required';
      if (!form.field_of_study.trim()) e.field_of_study = 'Required';
      if (!form.graduation_year) e.graduation_year = 'Required';
    } else if (step === 2) {
      if (!form.selected_course) e.selected_course = 'Required';
      if (!form.preferred_schedule) e.preferred_schedule = 'Required';
    } else if (step === 3) {
      if (!form.motivation.trim()) e.motivation = 'Required';
      if (!form.career_goals.trim()) e.career_goals = 'Required';
    }
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const next = () => {
    if (validateStep()) setStep((s) => Math.min(s + 1, STEPS.length - 1));
  };

  const prev = () => setStep((s) => Math.max(s - 1, 0));

  const submit = async () => {
    if (!validateStep()) return;
    setSubmitting(true);
    try {
      await api.submitApplication({
        ...form,
        selected_course: Number(form.selected_course),
        graduation_year: Number(form.graduation_year),
      });
      router.push('/apply/success');
    } catch (err) {
      setErrors({ submit: err instanceof Error ? err.message : 'Submission failed' });
    } finally {
      setSubmitting(false);
    }
  };

  const renderStep = () => {
    switch (step) {
      case 0:
        return (
          <div className="grid gap-4 sm:grid-cols-2">
            <Input label="Full Name *" value={form.full_name} onChange={(e) => update('full_name', e.target.value)} error={errors.full_name} className="sm:col-span-2" />
            <Select label="Gender *" options={GENDER_OPTIONS} value={form.gender} onChange={(e) => update('gender', e.target.value)} error={errors.gender} />
            <Input label="Date of Birth *" type="date" value={form.date_of_birth} onChange={(e) => update('date_of_birth', e.target.value)} error={errors.date_of_birth} />
            <Input label="Nationality *" value={form.nationality} onChange={(e) => update('nationality', e.target.value)} error={errors.nationality} />
            <Input label="Phone Number *" type="tel" value={form.phone} onChange={(e) => update('phone', e.target.value)} error={errors.phone} />
            <Input label="Email Address *" type="email" value={form.email} onChange={(e) => update('email', e.target.value)} error={errors.email} />
            <Textarea label="Residential Address *" value={form.address} onChange={(e) => update('address', e.target.value)} error={errors.address} className="sm:col-span-2" />
          </div>
        );
      case 1:
        return (
          <div className="grid gap-4 sm:grid-cols-2">
            <Select label="Education Level *" options={EDUCATION_OPTIONS} value={form.education_level} onChange={(e) => update('education_level', e.target.value)} error={errors.education_level} className="sm:col-span-2" />
            <Input label="Institution Name *" value={form.institution} onChange={(e) => update('institution', e.target.value)} error={errors.institution} className="sm:col-span-2" />
            <Input label="Field of Study *" value={form.field_of_study} onChange={(e) => update('field_of_study', e.target.value)} error={errors.field_of_study} />
            <Input label="Graduation Year *" type="number" min="1990" max="2030" value={form.graduation_year} onChange={(e) => update('graduation_year', e.target.value)} error={errors.graduation_year} />
          </div>
        );
      case 2:
        return (
          <div className="grid gap-4">
            <div className="glass-card">
              <p className="text-sm text-gray-500">Selected Course</p>
              <p className="text-lg font-semibold text-gray-900 dark:text-white">{course?.title || 'Loading...'}</p>
            </div>
            <Select label="Preferred Learning Schedule *" options={SCHEDULE_OPTIONS} value={form.preferred_schedule} onChange={(e) => update('preferred_schedule', e.target.value)} error={errors.preferred_schedule} />
          </div>
        );
      case 3:
        return (
          <div className="grid gap-4">
            <Textarea label="Why do you want to join this course? *" value={form.motivation} onChange={(e) => update('motivation', e.target.value)} error={errors.motivation} />
            <Textarea label="Career Goals *" value={form.career_goals} onChange={(e) => update('career_goals', e.target.value)} error={errors.career_goals} />
            <Textarea label="Additional Comments" value={form.comments} onChange={(e) => update('comments', e.target.value)} />
          </div>
        );
      case 4:
        return (
          <div className="space-y-6">
            {[
              { title: 'Personal Information', items: [
                ['Full Name', form.full_name], ['Gender', form.gender], ['Date of Birth', form.date_of_birth],
                ['Nationality', form.nationality], ['Phone', form.phone], ['Email', form.email], ['Address', form.address],
              ]},
              { title: 'Education', items: [
                ['Education Level', form.education_level], ['Institution', form.institution],
                ['Field of Study', form.field_of_study], ['Graduation Year', String(form.graduation_year)],
              ]},
              { title: 'Course & Motivation', items: [
                ['Course', course?.title || ''], ['Schedule', form.preferred_schedule],
                ['Motivation', form.motivation], ['Career Goals', form.career_goals],
              ]},
            ].map((section) => (
              <div key={section.title} className="glass-card">
                <h3 className="mb-3 font-semibold text-gray-900 dark:text-white">{section.title}</h3>
                <dl className="grid gap-2 sm:grid-cols-2">
                  {section.items.map(([label, value]) => (
                    <div key={label}>
                      <dt className="text-xs text-gray-500">{label}</dt>
                      <dd className="text-sm font-medium text-gray-900 dark:text-gray-200">{value || '—'}</dd>
                    </div>
                  ))}
                </dl>
              </div>
            ))}
            {errors.submit && <p className="text-sm text-red-500">{errors.submit}</p>}
          </div>
        );
      default:
        return null;
    }
  };

  return (
    <div className="flex min-h-screen flex-col">
      <StudentNavbar />
      <main className="flex-1 bg-gray-50 dark:bg-gray-950">
        <div className="page-container py-8 sm:py-12">
          <div className="mx-auto max-w-2xl">
            <h1 className="mb-2 text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">Application Form</h1>
            <p className="mb-8 text-gray-500">Step {step + 1} of {STEPS.length}: {STEPS[step]}</p>

            {/* Progress */}
            <div className="mb-8 flex items-center justify-between">
              {STEPS.map((label, i) => (
                <div key={label} className="flex flex-1 items-center">
                  <div className={`flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition-colors ${
                    i < step ? 'bg-accent-500 text-white' :
                    i === step ? 'bg-primary-600 text-white' :
                    'bg-gray-200 text-gray-500 dark:bg-gray-700'
                  }`}>
                    {i < step ? <Check className="h-4 w-4" /> : i + 1}
                  </div>
                  {i < STEPS.length - 1 && (
                    <div className={`mx-1 h-0.5 flex-1 ${i < step ? 'bg-accent-500' : 'bg-gray-200 dark:bg-gray-700'}`} />
                  )}
                </div>
              ))}
            </div>

            <div className="glass-card">
              <AnimatePresence mode="wait">
                <motion.div
                  key={step}
                  initial={{ opacity: 0, x: 20 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: -20 }}
                  transition={{ duration: 0.3 }}
                >
                  {renderStep()}
                </motion.div>
              </AnimatePresence>

              <div className="mt-8 flex justify-between border-t border-gray-200 pt-6 dark:border-gray-700">
                <Button variant="secondary" onClick={prev} disabled={step === 0}>
                  <ChevronLeft className="h-4 w-4" /> Previous
                </Button>
                {step < STEPS.length - 1 ? (
                  <Button onClick={next}>
                    Next <ChevronRight className="h-4 w-4" />
                  </Button>
                ) : (
                  <Button onClick={submit} loading={submitting}>
                    Submit Application
                  </Button>
                )}
              </div>
            </div>
          </div>
        </div>
      </main>
      <StudentFooter />
    </div>
  );
}
