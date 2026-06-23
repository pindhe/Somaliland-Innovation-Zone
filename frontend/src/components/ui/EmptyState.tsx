'use client';

import { BookOpen, Inbox } from 'lucide-react';
import { Button } from './Button';

interface EmptyStateProps {
  title: string;
  description: string;
  icon?: 'inbox' | 'book';
  actionLabel?: string;
  onAction?: () => void;
}

export function EmptyState({ title, description, icon = 'inbox', actionLabel, onAction }: EmptyStateProps) {
  const Icon = icon === 'book' ? BookOpen : Inbox;

  return (
    <div className="flex flex-col items-center justify-center py-16 text-center animate-fade-in">
      <div className="mb-4 rounded-2xl bg-gray-100 p-6 dark:bg-gray-800">
        <Icon className="h-12 w-12 text-gray-400" />
      </div>
      <h3 className="mb-2 text-lg font-semibold text-gray-900 dark:text-gray-100">{title}</h3>
      <p className="mb-6 max-w-sm text-sm text-gray-500 dark:text-gray-400">{description}</p>
      {actionLabel && onAction && (
        <Button onClick={onAction}>{actionLabel}</Button>
      )}
    </div>
  );
}
