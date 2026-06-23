export interface Course {
  id: number;
  title: string;
  category: string;
  description: string;
  duration: string;
  training_type: 'free' | 'paid';
  instructor: string;
  seats: number;
  seats_available: number;
  image_url: string | null;
  requirements?: string;
  outcomes?: string;
  status: string;
  start_date: string;
  end_date: string;
  created_at?: string;
}

export interface Application {
  id: number;
  full_name: string;
  gender: string;
  date_of_birth: string;
  nationality: string;
  phone: string;
  email: string;
  address: string;
  education_level: string;
  institution: string;
  field_of_study: string;
  graduation_year: number;
  selected_course: number;
  course_title?: string;
  preferred_schedule: string;
  motivation: string;
  career_goals: string;
  comments: string;
  application_status: 'pending' | 'approved' | 'rejected';
  admin_notes?: string;
  submitted_at: string;
}

export interface ApplicationFormData {
  full_name: string;
  gender: string;
  date_of_birth: string;
  nationality: string;
  phone: string;
  email: string;
  address: string;
  education_level: string;
  institution: string;
  field_of_study: string;
  graduation_year: number | '';
  selected_course: number | '';
  preferred_schedule: string;
  motivation: string;
  career_goals: string;
  comments: string;
}

export interface AdminUser {
  id: number;
  username: string;
  email: string;
  role: string;
}

export interface DashboardStats {
  stats: {
    total_courses: number;
    active_courses: number;
    free_trainings: number;
    paid_courses: number;
    total_applications: number;
    approved_applications: number;
    rejected_applications: number;
    pending_applications: number;
  };
  recent_applications: Array<{
    id: number;
    full_name: string;
    course: string;
    email: string;
    status: string;
    submitted_at: string;
  }>;
  latest_courses: Course[];
  category_stats: Array<{ category: string; count: number }>;
  application_trends: Array<{ month: string; count: number }>;
  status_breakdown: { pending: number; approved: number; rejected: number };
  course_registration_analytics: Array<{ course: string; count: number }>;
}

export interface Notification {
  id: number;
  title: string;
  message: string;
  notification_type: string;
  recipient_type: string;
  recipient_email?: string;
  course?: number;
  sent_at: string;
  sent_by_name?: string;
}

export interface Category {
  value: string;
  label: string;
}
