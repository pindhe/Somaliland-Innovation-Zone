from django import forms
from django.forms import inlineformset_factory
from courses.models import Course, CourseModule, Department, Category


class CourseForm(forms.ModelForm):
    class Meta:
        model = Course
        fields = [
            "title",
            "subtitle",
            "course_code",
            "department",
            "category",
            "instructor",
            "description",
            "learning_outcomes",
            "skills",
            "duration",
            "start_date",
            "end_date",
            "class_days",
            "class_time",
            "language",
            "certificate_available",
            "max_seats",
            "registration_deadline",
            "location",
            "training_mode",
            "thumbnail",
            "banner",
            "requirements",
            "who_can_apply",
            "benefits",
            "pricing_type",
            "price",
            "scholarship_available",
            "status",
        ]
        widgets = {
            "title": forms.TextInput(attrs={"class": "form-input"}),
            "subtitle": forms.TextInput(attrs={"class": "form-input"}),
            "course_code": forms.TextInput(attrs={"class": "form-input"}),
            "department": forms.Select(attrs={"class": "form-input"}),
            "category": forms.Select(attrs={"class": "form-input"}),
            "instructor": forms.TextInput(attrs={"class": "form-input"}),
            "description": forms.Textarea(attrs={"class": "form-input rich-text", "rows": 5}),
            "learning_outcomes": forms.Textarea(attrs={"class": "form-input rich-text", "rows": 4}),
            "skills": forms.Textarea(attrs={"class": "form-input", "rows": 3}),
            "duration": forms.TextInput(attrs={"class": "form-input"}),
            "start_date": forms.DateInput(attrs={"class": "form-input", "type": "date"}),
            "end_date": forms.DateInput(attrs={"class": "form-input", "type": "date"}),
            "class_days": forms.TextInput(attrs={"class": "form-input", "placeholder": "Mon, Wed, Fri"}),
            "class_time": forms.TextInput(attrs={"class": "form-input", "placeholder": "09:00 – 12:00"}),
            "language": forms.TextInput(attrs={"class": "form-input"}),
            "max_seats": forms.NumberInput(attrs={"class": "form-input"}),
            "registration_deadline": forms.DateTimeInput(
                attrs={"class": "form-input", "type": "datetime-local"},
                format="%Y-%m-%dT%H:%M",
            ),
            "location": forms.TextInput(attrs={"class": "form-input"}),
            "training_mode": forms.Select(attrs={"class": "form-input"}),
            "requirements": forms.Textarea(attrs={"class": "form-input rich-text", "rows": 4}),
            "who_can_apply": forms.Textarea(attrs={"class": "form-input rich-text", "rows": 4}),
            "benefits": forms.Textarea(attrs={"class": "form-input rich-text", "rows": 4}),
            "pricing_type": forms.Select(attrs={"class": "form-input"}),
            "price": forms.NumberInput(attrs={"class": "form-input", "step": "0.01"}),
            "status": forms.Select(attrs={"class": "form-input"}),
            "certificate_available": forms.CheckboxInput(attrs={"class": "form-checkbox"}),
            "scholarship_available": forms.CheckboxInput(attrs={"class": "form-checkbox"}),
        }

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields["registration_deadline"].input_formats = [
            "%Y-%m-%dT%H:%M",
            "%Y-%m-%d %H:%M:%S",
            "%Y-%m-%d %H:%M",
        ]


CourseModuleFormSet = inlineformset_factory(
    Course,
    CourseModule,
    fields=["title", "description", "order"],
    extra=1,
    can_delete=True,
    widgets={
        "title": forms.TextInput(attrs={"class": "form-input", "placeholder": "Module title"}),
        "description": forms.Textarea(
            attrs={"class": "form-input", "rows": 2, "placeholder": "Module description"}
        ),
        "order": forms.NumberInput(attrs={"class": "form-input w-20"}),
    },
)


class DepartmentForm(forms.ModelForm):
    class Meta:
        model = Department
        fields = ["name", "description", "is_active"]
        widgets = {
            "name": forms.TextInput(attrs={"class": "form-input"}),
            "description": forms.Textarea(attrs={"class": "form-input", "rows": 3}),
        }


class CategoryForm(forms.ModelForm):
    class Meta:
        model = Category
        fields = ["name"]
        widgets = {"name": forms.TextInput(attrs={"class": "form-input"})}
