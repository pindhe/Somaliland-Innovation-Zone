class Course {
  final int id;
  final String title;
  final String category;
  final String description;
  final String duration;
  final String trainingType;
  final String instructor;
  final int seats;
  final int seatsAvailable;
  final String? imageUrl;
  final String? requirements;
  final String? outcomes;
  final String status;
  final String startDate;
  final String endDate;

  Course({
    required this.id,
    required this.title,
    required this.category,
    required this.description,
    required this.duration,
    required this.trainingType,
    required this.instructor,
    required this.seats,
    required this.seatsAvailable,
    this.imageUrl,
    this.requirements,
    this.outcomes,
    required this.status,
    required this.startDate,
    required this.endDate,
  });

  factory Course.fromJson(Map<String, dynamic> json) => Course(
        id: json['id'] as int,
        title: json['title'] as String? ?? '',
        category: json['category'] as String? ?? '',
        description: json['description'] as String? ?? '',
        duration: json['duration'] as String? ?? '',
        trainingType: json['training_type'] as String? ?? 'free',
        instructor: json['instructor'] as String? ?? '',
        seats: json['seats'] as int? ?? 0,
        seatsAvailable: json['seats_available'] as int? ?? 0,
        imageUrl: json['image_url'] as String?,
        requirements: json['requirements'] as String?,
        outcomes: json['outcomes'] as String?,
        status: json['status'] as String? ?? 'open',
        startDate: json['start_date'] as String? ?? '',
        endDate: json['end_date'] as String? ?? '',
      );

  bool get isFree => trainingType == 'free';
  List<String> get outcomeList =>
      outcomes?.split('\n').where((s) => s.trim().isNotEmpty).toList() ?? [];
  List<String> get requirementList =>
      requirements?.split('\n').where((s) => s.trim().isNotEmpty).toList() ?? [];
}
