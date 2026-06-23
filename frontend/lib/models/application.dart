class Application {
  final int id;
  final String fullName;
  final String gender;
  final String dateOfBirth;
  final String nationality;
  final String phone;
  final String email;
  final String address;
  final String educationLevel;
  final String institution;
  final String fieldOfStudy;
  final int graduationYear;
  final int? selectedCourse;
  final String? courseTitle;
  final String preferredSchedule;
  final String motivation;
  final String careerGoals;
  final String comments;
  final String applicationStatus;
  final String? adminNotes;
  final String submittedAt;

  Application({
    required this.id,
    required this.fullName,
    required this.gender,
    required this.dateOfBirth,
    required this.nationality,
    required this.phone,
    required this.email,
    required this.address,
    required this.educationLevel,
    required this.institution,
    required this.fieldOfStudy,
    required this.graduationYear,
    this.selectedCourse,
    this.courseTitle,
    required this.preferredSchedule,
    required this.motivation,
    required this.careerGoals,
    required this.comments,
    required this.applicationStatus,
    this.adminNotes,
    required this.submittedAt,
  });

  factory Application.fromJson(Map<String, dynamic> json) => Application(
        id: json['id'] as int,
        fullName: json['full_name'] as String? ?? '',
        gender: json['gender'] as String? ?? '',
        dateOfBirth: json['date_of_birth'] as String? ?? '',
        nationality: json['nationality'] as String? ?? '',
        phone: json['phone'] as String? ?? '',
        email: json['email'] as String? ?? '',
        address: json['address'] as String? ?? '',
        educationLevel: json['education_level'] as String? ?? '',
        institution: json['institution'] as String? ?? '',
        fieldOfStudy: json['field_of_study'] as String? ?? '',
        graduationYear: json['graduation_year'] as int? ?? 0,
        selectedCourse: json['selected_course'] as int?,
        courseTitle: json['course_title'] as String?,
        preferredSchedule: json['preferred_schedule'] as String? ?? 'flexible',
        motivation: json['motivation'] as String? ?? '',
        careerGoals: json['career_goals'] as String? ?? '',
        comments: json['comments'] as String? ?? '',
        applicationStatus: json['application_status'] as String? ?? 'pending',
        adminNotes: json['admin_notes'] as String?,
        submittedAt: json['submitted_at'] as String? ?? '',
      );
}

class ApplicationFormData {
  String fullName = '';
  String gender = '';
  String dateOfBirth = '';
  String nationality = '';
  String phone = '';
  String email = '';
  String address = '';
  String educationLevel = '';
  String institution = '';
  String fieldOfStudy = '';
  int graduationYear = DateTime.now().year;
  int selectedCourse = 0;
  String preferredSchedule = 'flexible';
  String motivation = '';
  String careerGoals = '';
  String comments = '';

  Map<String, dynamic> toJson() => {
        'full_name': fullName,
        'gender': gender,
        'date_of_birth': dateOfBirth,
        'nationality': nationality,
        'phone': phone,
        'email': email,
        'address': address,
        'education_level': educationLevel,
        'institution': institution,
        'field_of_study': fieldOfStudy,
        'graduation_year': graduationYear,
        'selected_course': selectedCourse,
        'preferred_schedule': preferredSchedule,
        'motivation': motivation,
        'career_goals': careerGoals,
        'comments': comments,
      };
}
