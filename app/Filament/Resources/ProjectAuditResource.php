<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectAuditResource\Pages;
use App\Models\Project;
use App\Services\ProjectAuditService;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Filament\Tables\Actions;

class ProjectAuditResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $slug = 'project-audit';

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?int $navigationSort = 3;

    protected static function getNavigationLabel(): string
    {
        return __('Project Audit');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Workspace');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->label(__('Project Name'))
                    ->searchable()
                    ->sortable(),

                Columns\TextColumn::make('health_score')
                    ->label(__('Health Score'))
                    ->getStateUsing(function ($record) {
                        try {
                            $service = new ProjectAuditService($record);
                            $score = $service->calculateHealthScore();
                            $color = $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger');

                            return view('components.health-score', [
                                'score' => $score,
                                'color' => $color
                            ])->render();
                        } catch (\Exception $e) {
                            return '<span class="text-gray-500">-</span>';
                        }
                    })
                    ->html(),

                Columns\TextColumn::make('overdue_tickets')
                    ->label(__('Overdue'))
                    ->getStateUsing(function ($record) {
                        try {
                            $service = new ProjectAuditService($record);
                            $overdue = $service->getOverdueTicketsCount();
                            $total = $record->tickets()->count();

                            if ($total === 0) return '0 (0%)';

                            $percentage = round(($overdue / $total) * 100, 1);
                            $color = $percentage > 20 ? 'text-red-600' : ($percentage > 10 ? 'text-yellow-600' : 'text-green-600');

                            return "<span class='{$color} font-medium'>{$overdue} ({$percentage}%)</span>";
                        } catch (\Exception $e) {
                            return '<span class="text-gray-500">-</span>';
                        }
                    })
                    ->html(),

                Columns\TextColumn::make('completion_rate')
                    ->label(__('Completion Rate'))
                    ->getStateUsing(function ($record) {
                        try {
                            $service = new ProjectAuditService($record);
                            $rate = $service->getCompletionRate();
                            $completed = $service->getCompletedTicketsCount();
                            $total = $service->getTotalTicketsCount();
                            $barColor = $rate >= 80 ? '#22c55e' : ($rate >= 60 ? '#eab308' : '#ef4444');
                            $textColor = $rate >= 80 ? 'text-green-600 dark:text-green-400' : ($rate >= 60 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');

                            return '<div class="space-y-1">'
                                . '<div class="flex items-center justify-between">'
                                . '<span class="text-sm font-semibold ' . $textColor . '">' . $rate . '%</span>'
                                . '<span class="text-xs text-gray-500 dark:text-gray-400">' . $completed . '/' . $total . '</span>'
                                . '</div>'
                                . '<div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">'
                                . '<div class="h-full rounded-full" style="width:' . min($rate, 100) . '%;background:' . $barColor . '"></div>'
                                . '</div></div>';
                        } catch (\Exception $e) {
                            return '<span class="text-gray-500">-</span>';
                        }
                    })
                    ->html(),

                Columns\TextColumn::make('avg_cycle_time')
                    ->label(__('Avg Cycle Time'))
                    ->getStateUsing(function ($record) {
                        try {
                            $service = new ProjectAuditService($record);
                            $days = $service->getAverageCycleTime();

                            if ($days == 0) return '<span class="text-gray-400 dark:text-gray-500">-</span>';

                            $color = $days <= 7 ? 'text-green-600 dark:text-green-400' : ($days <= 14 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                            $label = $days == 1 ? '1 day' : $days . ' days';
                            return "<span class='{$color} font-medium'>{$label}</span>";
                        } catch (\Exception $e) {
                            return '<span class="text-gray-500">-</span>';
                        }
                    })
                    ->html(),

                Columns\TextColumn::make('bottleneck_status')
                    ->label(__('Main Bottleneck'))
                    ->getStateUsing(function ($record) {
                        try {
                            $service = new ProjectAuditService($record);
                            $bottleneck = $service->getMainBottleneck();

                            if (!$bottleneck['status']) return '<span class="text-gray-400 dark:text-gray-500">-</span>';

                            $color = e($bottleneck['color'] ?? '#6B7280');
                            return '<span class="inline-flex items-center gap-1.5">'
                                . '<span class="w-2 h-2 rounded-full flex-shrink-0" style="background:' . $color . '"></span>'
                                . '<span class="font-medium">' . e($bottleneck['status']) . '</span>'
                                . '<span class="text-xs text-gray-400 dark:text-gray-500">(' . $bottleneck['count'] . ')</span>'
                                . '</span>';
                        } catch (\Exception $e) {
                            return '-';
                        }
                    })
                    ->html(),
            ])
            ->filters([
                Filters\SelectFilter::make('health_level')
                    ->label(__('Health Level'))
                    ->options([
                        'critical' => __('Critical (0-40)'),
                        'warning' => __('Warning (41-70)'),
                        'good' => __('Good (71-100)'),
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['value'])) return $query;

                        // This would need to be implemented with raw SQL or computed values
                        // For now, just return the query as-is
                        return $query;
                    }),
            ])
            ->actions([
                Actions\Action::make('view_audit')
                    ->label(__('View Audit'))
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (Project $record): string => route('filament.resources.project-audit.view', $record))
                    ->openUrlInNewTab(false),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjectAudits::route('/'),
            'view' => Pages\ViewProjectAudit::route('/{record}/audit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}