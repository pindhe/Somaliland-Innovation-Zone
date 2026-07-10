from django import forms
from .models import Application


class ApplicationForm(forms.ModelForm):
    class Meta:
        model = Application
        fields = [
            "first_name",
            "last_name",
            "gender",
            "date_of_birth",
            "nationality",
            "national_id",
            "passport_number",
            "profile_photo",
            "phone",
            "whatsapp",
            "email",
            "current_address",
            "city",
            "country",
            "parent_name",
            "parent_phone",
            "highest_education",
            "institution",
            "graduation_year",
            "current_status",
            "field_of_study",
            "gpa",
            "skills",
            "experience",
            "motivation",
            "career_goals",
            "hear_about",
            "cv",
            "transcript",
            "certificate",
            "national_id_doc",
            "declaration",
        ]
        widgets = {
            "first_name": forms.TextInput(attrs={"class": "form-input"}),
            "last_name": forms.TextInput(attrs={"class": "form-input"}),
            "gender": forms.Select(attrs={"class": "form-input"}),
            "date_of_birth": forms.DateInput(attrs={"class": "form-input", "type": "date"}),
            "nationality": forms.TextInput(attrs={"class": "form-input"}),
            "national_id": forms.TextInput(attrs={"class": "form-input"}),
            "passport_number": forms.TextInput(attrs={"class": "form-input"}),
            "phone": forms.TextInput(attrs={"class": "form-input"}),
            "whatsapp": forms.TextInput(attrs={"class": "form-input"}),
            "email": forms.EmailInput(attrs={"class": "form-input"}),
            "current_address": forms.Textarea(attrs={"class": "form-input", "rows": 2}),
            "city": forms.TextInput(attrs={"class": "form-input"}),
            "country": forms.TextInput(attrs={"class": "form-input"}),
            "parent_name": forms.TextInput(attrs={"class": "form-input"}),
            "parent_phone": forms.TextInput(attrs={"class": "form-input"}),
            "highest_education": forms.TextInput(attrs={"class": "form-input"}),
            "institution": forms.TextInput(attrs={"class": "form-input"}),
            "graduation_year": forms.NumberInput(attrs={"class": "form-input"}),
            "current_status": forms.Select(attrs={"class": "form-input"}),
            "field_of_study": forms.TextInput(attrs={"class": "form-input"}),
            "gpa": forms.TextInput(attrs={"class": "form-input"}),
            "skills": forms.Textarea(attrs={"class": "form-input", "rows": 3}),
            "experience": forms.Textarea(attrs={"class": "form-input", "rows": 3}),
            "motivation": forms.Textarea(attrs={"class": "form-input", "rows": 4}),
            "career_goals": forms.Textarea(attrs={"class": "form-input", "rows": 4}),
            "hear_about": forms.Select(attrs={"class": "form-input"}),
            "declaration": forms.CheckboxInput(attrs={"class": "form-checkbox"}),
        }

    def clean_declaration(self):
        val = self.cleaned_data.get("declaration")
        if not val:
            raise forms.ValidationError("You must confirm that all information is correct.")
        return val

    def clean_cv(self):
        return self._validate_file(self.cleaned_data.get("cv"), ["pdf"], 5)

    def clean_transcript(self):
        return self._validate_file(self.cleaned_data.get("transcript"), ["pdf"], 5)

    def clean_certificate(self):
        return self._validate_file(self.cleaned_data.get("certificate"), ["pdf"], 5)

    def clean_national_id_doc(self):
        return self._validate_file(
            self.cleaned_data.get("national_id_doc"),
            ["pdf", "jpg", "jpeg", "png", "webp"],
            5,
        )

    def clean_profile_photo(self):
        return self._validate_file(
            self.cleaned_data.get("profile_photo"),
            ["jpg", "jpeg", "png", "webp"],
            3,
        )

    def _validate_file(self, f, allowed_ext, max_mb):
        if not f:
            return f
        name = getattr(f, "name", "") or ""
        ext = name.rsplit(".", 1)[-1].lower() if "." in name else ""
        if ext not in allowed_ext:
            raise forms.ValidationError(
                f"Invalid file type. Allowed: {', '.join(allowed_ext)}"
            )
        if f.size > max_mb * 1024 * 1024:
            raise forms.ValidationError(f"File too large. Max {max_mb}MB.")
        return f


class ApplicationStatusForm(forms.Form):
    application_number = forms.CharField(
        max_length=32,
        widget=forms.TextInput(
            attrs={"class": "form-input", "placeholder": "SIZ-2026-000001"}
        ),
    )
    email = forms.EmailField(
        widget=forms.EmailInput(attrs={"class": "form-input", "placeholder": "you@email.com"})
    )


class ApplicationReviewForm(forms.ModelForm):
    class Meta:
        model = Application
        fields = ["status", "admin_notes", "admin_comments", "internal_rating"]
        widgets = {
            "status": forms.Select(attrs={"class": "form-input"}),
            "admin_notes": forms.Textarea(attrs={"class": "form-input", "rows": 3}),
            "admin_comments": forms.Textarea(attrs={"class": "form-input", "rows": 3}),
            "internal_rating": forms.NumberInput(
                attrs={"class": "form-input", "min": 1, "max": 5}
            ),
        }
