import 'package:flutter/material.dart';
import '../config/theme.dart';

/// Brand logo with plain icon — no background shape.
class AppLogo extends StatelessWidget {
  final double iconSize;
  final bool showSubtitle;
  final VoidCallback? onTap;

  const AppLogo({
    super.key,
    this.iconSize = 28,
    this.showSubtitle = true,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final palette = context.palette;
    final primary = Theme.of(context).colorScheme.primary;

    final content = Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(Icons.school_outlined, size: iconSize, color: primary),
        const SizedBox(width: 10),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'SIZSR',
              style: TextStyle(
                fontSize: iconSize * 0.58,
                fontWeight: FontWeight.w800,
                color: palette.textPrimary,
                height: 1.1,
              ),
            ),
            if (showSubtitle)
              Text(
                'Innovation Zone',
                style: TextStyle(
                  fontSize: iconSize * 0.32,
                  color: palette.textSecondary,
                  height: 1.2,
                ),
              ),
          ],
        ),
      ],
    );

    if (onTap == null) return content;
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Padding(padding: const EdgeInsets.symmetric(vertical: 4), child: content),
    );
  }
}
