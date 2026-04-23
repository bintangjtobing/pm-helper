<?php

namespace App\Mcp;

use App\Models\User;

interface Tool
{
    public function name(): string;

    public function description(): string;

    public function inputSchema(): array;

    public function execute(User $user, array $args): mixed;
}
