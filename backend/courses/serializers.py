from rest_framework import serializers

from .models import Course, CourseCategory


class CourseListSerializer(serializers.ModelSerializer):
    seats_available = serializers.IntegerField(read_only=True)
    image_url = serializers.SerializerMethodField()

    class Meta:
        model = Course
        fields = (
            'id', 'title', 'category', 'description', 'duration',
            'training_type', 'instructor', 'seats', 'seats_available',
            'image_url', 'status', 'start_date', 'end_date',
        )

    def get_image_url(self, obj):
        if obj.image:
            request = self.context.get('request')
            if request:
                return request.build_absolute_uri(obj.image.url)
            return obj.image.url
        return None


class CourseDetailSerializer(CourseListSerializer):
    class Meta(CourseListSerializer.Meta):
        fields = CourseListSerializer.Meta.fields + (
            'requirements', 'outcomes', 'created_at', 'updated_at',
        )


class CourseWriteSerializer(serializers.ModelSerializer):
    class Meta:
        model = Course
        fields = (
            'title', 'category', 'description', 'duration',
            'training_type', 'instructor', 'seats', 'image',
            'requirements', 'outcomes', 'status', 'start_date', 'end_date',
        )

    def validate(self, data):
        start = data.get('start_date', getattr(self.instance, 'start_date', None))
        end = data.get('end_date', getattr(self.instance, 'end_date', None))
        if start and end and end < start:
            raise serializers.ValidationError({
                'end_date': 'End date must be after start date.',
            })
        return data


class CourseCategorySerializer(serializers.Serializer):
    value = serializers.CharField()
    label = serializers.CharField()


def get_category_choices():
    return [
        {'value': c.value, 'label': c.label}
        for c in CourseCategory
    ]
