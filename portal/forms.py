from django import forms
from django.contrib.auth.forms import AuthenticationForm
from core.models import SiteSettings


class PortalLoginForm(AuthenticationForm):
    username = forms.CharField(
        widget=forms.TextInput(
            attrs={"class": "form-input", "placeholder": "Username", "autofocus": True}
        )
    )
    password = forms.CharField(
        widget=forms.PasswordInput(
            attrs={"class": "form-input", "placeholder": "Password"}
        )
    )


class SiteSettingsForm(forms.ModelForm):
    class Meta:
        model = SiteSettings
        fields = [
            "organization_name",
            "logo",
            "website",
            "email",
            "phone",
            "address",
            "facebook",
            "twitter",
            "linkedin",
            "whatsapp",
            "footer_text",
            "smtp_host",
            "smtp_port",
            "smtp_user",
            "smtp_password",
            "smtp_use_tls",
        ]
        widgets = {
            "organization_name": forms.TextInput(attrs={"class": "form-input"}),
            "website": forms.URLInput(attrs={"class": "form-input"}),
            "email": forms.EmailInput(attrs={"class": "form-input"}),
            "phone": forms.TextInput(attrs={"class": "form-input"}),
            "address": forms.Textarea(attrs={"class": "form-input", "rows": 2}),
            "facebook": forms.URLInput(attrs={"class": "form-input"}),
            "twitter": forms.URLInput(attrs={"class": "form-input"}),
            "linkedin": forms.URLInput(attrs={"class": "form-input"}),
            "whatsapp": forms.TextInput(attrs={"class": "form-input"}),
            "footer_text": forms.Textarea(attrs={"class": "form-input", "rows": 2}),
            "smtp_host": forms.TextInput(attrs={"class": "form-input"}),
            "smtp_port": forms.NumberInput(attrs={"class": "form-input"}),
            "smtp_user": forms.TextInput(attrs={"class": "form-input"}),
            "smtp_password": forms.PasswordInput(
                attrs={"class": "form-input"}, render_value=True
            ),
        }
