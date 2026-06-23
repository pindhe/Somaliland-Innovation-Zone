import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../config/theme.dart';
import '../providers/app_providers.dart';

/// Plain icon theme toggle — no background shape. Long-press for system/light/dark menu.
class ThemeToggleButton extends StatelessWidget {
  final Color? iconColor;
  final double iconSize;

  const ThemeToggleButton({super.key, this.iconColor, this.iconSize = 22});

  @override
  Widget build(BuildContext context) {
    final theme = context.watch<ThemeProvider>();
    final color = iconColor ?? context.palette.iconMuted;

    return IconButton(
      onPressed: theme.toggleLightDark,
      onLongPress: () => _showThemeMenu(context, theme),
      tooltip: 'Theme: ${theme.modeLabel} (long press for options)',
      padding: EdgeInsets.zero,
      constraints: const BoxConstraints(minWidth: 40, minHeight: 40),
      splashRadius: 20,
      style: IconButton.styleFrom(
        backgroundColor: Colors.transparent,
        foregroundColor: color,
        highlightColor: color.withValues(alpha: 0.08),
      ),
      icon: Icon(theme.toggleIcon, size: iconSize),
    );
  }

  void _showThemeMenu(BuildContext context, ThemeProvider theme) {
    showModalBottomSheet(
      context: context,
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Padding(
                padding: const EdgeInsets.all(16),
                child: Text('Appearance', style: Theme.of(ctx).textTheme.titleMedium),
              ),
              _ThemeOption(
                icon: Icons.light_mode_outlined,
                label: 'Light',
                selected: theme.mode == ThemeMode.light,
                onTap: () {
                  theme.setMode(ThemeMode.light);
                  Navigator.pop(ctx);
                },
              ),
              _ThemeOption(
                icon: Icons.dark_mode_outlined,
                label: 'Dark',
                selected: theme.mode == ThemeMode.dark,
                onTap: () {
                  theme.setMode(ThemeMode.dark);
                  Navigator.pop(ctx);
                },
              ),
              _ThemeOption(
                icon: Icons.brightness_auto_outlined,
                label: 'System default',
                selected: theme.mode == ThemeMode.system,
                onTap: () {
                  theme.setMode(ThemeMode.system);
                  Navigator.pop(ctx);
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ThemeOption extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _ThemeOption({
    required this.icon,
    required this.label,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final palette = context.palette;
    return ListTile(
      leading: Icon(icon, color: selected ? Theme.of(context).colorScheme.primary : palette.iconMuted),
      title: Text(label),
      trailing: selected ? Icon(Icons.check, color: Theme.of(context).colorScheme.primary) : null,
      onTap: onTap,
    );
  }
}
