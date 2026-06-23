const courseCategories = [
  'Programming',
  'Web Development',
  'Mobile Development',
  'Graphic Design',
  'Digital Marketing',
  'Artificial Intelligence',
  'Business Skills',
  'Entrepreneurship',
];

const genderOptions = [
  {'value': 'male', 'label': 'Male'},
  {'value': 'female', 'label': 'Female'},
  {'value': 'other', 'label': 'Other'},
  {'value': 'prefer_not', 'label': 'Prefer not to say'},
];

const educationOptions = [
  {'value': 'high_school', 'label': 'High School'},
  {'value': 'diploma', 'label': 'Diploma'},
  {'value': 'bachelors', 'label': "Bachelor's Degree"},
  {'value': 'masters', 'label': "Master's Degree"},
  {'value': 'phd', 'label': 'PhD'},
  {'value': 'other', 'label': 'Other'},
];

const scheduleOptions = [
  {'value': 'morning', 'label': 'Morning'},
  {'value': 'afternoon', 'label': 'Afternoon'},
  {'value': 'evening', 'label': 'Evening'},
  {'value': 'weekend', 'label': 'Weekend'},
  {'value': 'flexible', 'label': 'Flexible'},
];

String formatDate(String date) {
  try {
    final d = DateTime.parse(date);
    return '${_months[d.month - 1]} ${d.day}, ${d.year}';
  } catch (_) {
    return date;
  }
}

const _months = [
  'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
];

Color statusColor(String status) {
  switch (status) {
    case 'approved':
    case 'open':
      return const Color(0xFF22C55E);
    case 'rejected':
      return const Color(0xFFEF4444);
    case 'pending':
      return const Color(0xFFF59E0B);
    default:
      return const Color(0xFF6B7280);
  }
}
