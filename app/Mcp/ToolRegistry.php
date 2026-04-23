<?php

namespace App\Mcp;

use App\Mcp\Tools\AddDiscussionCommentTool;
use App\Mcp\Tools\AddTicketCommentTool;
use App\Mcp\Tools\CreateDailyReportTool;
use App\Mcp\Tools\CreateTicketTool;
use App\Mcp\Tools\GetDailyReportTool;
use App\Mcp\Tools\GetDiscussionTool;
use App\Mcp\Tools\GetTicketTool;
use App\Mcp\Tools\ListDailyReportsTool;
use App\Mcp\Tools\ListDiscussionsTool;
use App\Mcp\Tools\ListProjectsTool;
use App\Mcp\Tools\ListTicketStatusesTool;
use App\Mcp\Tools\ListTicketsTool;
use App\Mcp\Tools\ListUsersTool;
use App\Mcp\Tools\UpdateTicketStatusTool;

class ToolRegistry
{
    /** @var array<string, Tool> */
    private array $tools;

    public function __construct()
    {
        $classes = [
            ListTicketsTool::class,
            GetTicketTool::class,
            CreateTicketTool::class,
            UpdateTicketStatusTool::class,
            AddTicketCommentTool::class,
            ListDiscussionsTool::class,
            GetDiscussionTool::class,
            AddDiscussionCommentTool::class,
            ListDailyReportsTool::class,
            GetDailyReportTool::class,
            CreateDailyReportTool::class,
            ListProjectsTool::class,
            ListTicketStatusesTool::class,
            ListUsersTool::class,
        ];

        $this->tools = [];
        foreach ($classes as $class) {
            $instance = app($class);
            $this->tools[$instance->name()] = $instance;
        }
    }

    /** @return array<int, Tool> */
    public function all(): array
    {
        return array_values($this->tools);
    }

    public function get(string $name): ?Tool
    {
        return $this->tools[$name] ?? null;
    }
}
