class AdminUser {
  final int id;
  final String username;
  final String email;
  final String role;

  AdminUser({
    required this.id,
    required this.username,
    required this.email,
    required this.role,
  });

  factory AdminUser.fromJson(Map<String, dynamic> json) => AdminUser(
        id: json['id'] as int,
        username: json['username'] as String? ?? '',
        email: json['email'] as String? ?? '',
        role: json['role'] as String? ?? 'admin',
      );
}

class DashboardStats {
  final Map<String, int> stats;
  final List<RecentApplication> recentApplications;
  final List<Map<String, dynamic>> categoryStats;
  final List<Map<String, dynamic>> applicationTrends;
  final Map<String, int> statusBreakdown;
  final List<Map<String, dynamic>> courseRegistrationAnalytics;

  DashboardStats({
    required this.stats,
    required this.recentApplications,
    required this.categoryStats,
    required this.applicationTrends,
    required this.statusBreakdown,
    required this.courseRegistrationAnalytics,
  });

  factory DashboardStats.fromJson(Map<String, dynamic> json) => DashboardStats(
        stats: Map<String, int>.from(
          (json['stats'] as Map<String, dynamic>).map(
            (k, v) => MapEntry(k, v as int),
          ),
        ),
        recentApplications: (json['recent_applications'] as List<dynamic>)
            .map((e) => RecentApplication.fromJson(e as Map<String, dynamic>))
            .toList(),
        categoryStats:
            List<Map<String, dynamic>>.from(json['category_stats'] as List),
        applicationTrends:
            List<Map<String, dynamic>>.from(json['application_trends'] as List),
        statusBreakdown: Map<String, int>.from(
          (json['status_breakdown'] as Map<String, dynamic>).map(
            (k, v) => MapEntry(k, v as int),
          ),
        ),
        courseRegistrationAnalytics: List<Map<String, dynamic>>.from(
          json['course_registration_analytics'] as List,
        ),
      );
}

class RecentApplication {
  final int id;
  final String fullName;
  final String course;
  final String email;
  final String status;
  final String submittedAt;

  RecentApplication({
    required this.id,
    required this.fullName,
    required this.course,
    required this.email,
    required this.status,
    required this.submittedAt,
  });

  factory RecentApplication.fromJson(Map<String, dynamic> json) =>
      RecentApplication(
        id: json['id'] as int,
        fullName: json['full_name'] as String? ?? '',
        course: json['course'] as String? ?? '',
        email: json['email'] as String? ?? '',
        status: json['status'] as String? ?? '',
        submittedAt: json['submitted_at'] as String? ?? '',
      );
}

class NotificationItem {
  final int id;
  final String title;
  final String message;
  final String notificationType;
  final String recipientType;
  final String sentAt;

  NotificationItem({
    required this.id,
    required this.title,
    required this.message,
    required this.notificationType,
    required this.recipientType,
    required this.sentAt,
  });

  factory NotificationItem.fromJson(Map<String, dynamic> json) =>
      NotificationItem(
        id: json['id'] as int,
        title: json['title'] as String? ?? '',
        message: json['message'] as String? ?? '',
        notificationType: json['notification_type'] as String? ?? '',
        recipientType: json['recipient_type'] as String? ?? '',
        sentAt: json['sent_at'] as String? ?? '',
      );
}
