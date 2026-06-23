import 'package:flutter_test/flutter_test.dart';
import 'package:sizsr/app.dart';

void main() {
  testWidgets('App loads home screen', (tester) async {
    await tester.pumpWidget(const SizsrApp());
    await tester.pumpAndSettle();
    expect(find.text('SIZSR'), findsWidgets);
  });
}
