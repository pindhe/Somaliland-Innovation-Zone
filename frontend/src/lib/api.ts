const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

class ApiClient {
  private getToken(): string | null {
    if (typeof window === 'undefined') return null;
    return localStorage.getItem('access_token');
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {},
  ): Promise<T> {
    const token = this.getToken();
    const headers: Record<string, string> = {
      ...(options.headers as Record<string, string>),
    };

    if (!(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
    }

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${API_URL}${endpoint}`, {
      ...options,
      headers,
    });

    if (!response.ok) {
      const error = await response.json().catch(() => ({ detail: 'Request failed' }));
      throw new Error(error.detail || error.message || 'Request failed');
    }

    if (response.status === 204) return {} as T;
    return response.json();
  }

  // Auth
  async login(username: string, password: string) {
    return this.request<{ access: string; refresh: string; user: import('./types').AdminUser }>(
      '/auth/login/',
      { method: 'POST', body: JSON.stringify({ username, password }) },
    );
  }

  async getMe() {
    return this.request<import('./types').AdminUser>('/auth/me/');
  }

  async forgotPassword(email: string) {
    return this.request('/auth/forgot-password/', {
      method: 'POST',
      body: JSON.stringify({ email }),
    });
  }

  async resetPassword(email: string, token: string, new_password: string) {
    return this.request('/auth/reset-password/', {
      method: 'POST',
      body: JSON.stringify({ email, token, new_password }),
    });
  }

  // Courses
  async getCourses(params?: Record<string, string>) {
    const query = params ? '?' + new URLSearchParams(params).toString() : '';
    const data = await this.request<{ results?: import('./types').Course[] } | import('./types').Course[]>(
      `/courses/${query}`,
    );
    return Array.isArray(data) ? data : data.results || [];
  }

  async getFeaturedCourses() {
    return this.request<import('./types').Course[]>('/courses/featured/');
  }

  async getCourse(id: number) {
    return this.request<import('./types').Course>(`/courses/${id}/`);
  }

  async getCategories() {
    return this.request<import('./types').Category[]>('/courses/categories/');
  }

  async createCourse(data: FormData) {
    return this.request<import('./types').Course>('/courses/', {
      method: 'POST',
      body: data,
      headers: {},
    });
  }

  async updateCourse(id: number, data: FormData) {
    return this.request<import('./types').Course>(`/courses/${id}/`, {
      method: 'PATCH',
      body: data,
      headers: {},
    });
  }

  async publishCourse(id: number) {
    return this.request(`/courses/${id}/publish/`, { method: 'POST' });
  }

  async archiveCourse(id: number) {
    return this.request(`/courses/${id}/archive/`, { method: 'POST' });
  }

  async deleteCourse(id: number) {
    return this.request(`/courses/${id}/`, { method: 'DELETE' });
  }

  // Applications
  async getApplications(params?: Record<string, string>) {
    const query = params ? '?' + new URLSearchParams(params).toString() : '';
    const data = await this.request<{ results?: import('./types').Application[] } | import('./types').Application[]>(
      `/applications/${query}`,
    );
    return Array.isArray(data) ? data : data.results || [];
  }

  async getApplication(id: number) {
    return this.request<import('./types').Application>(`/applications/${id}/`);
  }

  async submitApplication(data: import('./types').ApplicationFormData) {
    return this.request<import('./types').Application>('/applications/', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async approveApplication(id: number, admin_notes?: string) {
    return this.request(`/applications/${id}/approve/`, {
      method: 'POST',
      body: JSON.stringify({ status: 'approved', admin_notes }),
    });
  }

  async rejectApplication(id: number, admin_notes?: string) {
    return this.request(`/applications/${id}/reject/`, {
      method: 'POST',
      body: JSON.stringify({ status: 'rejected', admin_notes }),
    });
  }

  async exportApplications(params?: Record<string, string>) {
    const query = params ? '?' + new URLSearchParams(params).toString() : '';
    const token = this.getToken();
    const response = await fetch(`${API_URL}/applications/export/${query}`, {
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    });
    if (!response.ok) throw new Error('Export failed');
    return response.blob();
  }

  // Dashboard
  async getDashboardStats() {
    return this.request<import('./types').DashboardStats>('/dashboard/stats/');
  }

  // Notifications
  async getNotifications() {
    const data = await this.request<{ results?: import('./types').Notification[] } | import('./types').Notification[]>(
      '/notifications/',
    );
    return Array.isArray(data) ? data : data.results || [];
  }

  async sendNotification(data: Partial<import('./types').Notification>) {
    return this.request('/notifications/', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }
}

export const api = new ApiClient();
