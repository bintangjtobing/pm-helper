<?php

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Mcp\ToolRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class McpController extends Controller
{
    public const PROTOCOL_VERSION = '2024-11-05';

    public function __invoke(Request $request, ToolRegistry $registry): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->currentAccessToken()?->can('mcp:*')) {
            return $this->rpcError(null, -32001, 'Token lacks mcp:* ability', 403);
        }

        // Update last_used_at timestamp for visibility in the UI.
        if ($token = $user->currentAccessToken()) {
            $token->forceFill(['last_used_at' => now()])->save();
        }

        $payload = $request->all();

        // Batch request support (JSON-RPC 2.0): array of requests.
        if (array_is_list($payload) && isset($payload[0]['jsonrpc'])) {
            $responses = [];
            foreach ($payload as $call) {
                $responses[] = $this->handleRpc($registry, $user, $call);
            }
            $responses = array_values(array_filter($responses));
            return response()->json($responses);
        }

        $response = $this->handleRpc($registry, $user, $payload);

        if ($response === null) {
            return response()->json(null, 204);
        }

        return response()->json($response);
    }

    private function handleRpc(ToolRegistry $registry, $user, array $call): ?array
    {
        $id = $call['id'] ?? null;
        $method = $call['method'] ?? '';
        $params = $call['params'] ?? [];

        // Notifications have no "id" and expect no response.
        $isNotification = ! array_key_exists('id', $call);

        try {
            $result = match ($method) {
                'initialize' => $this->initialize(),
                'notifications/initialized', 'initialized' => null,
                'ping' => new \stdClass(),
                'tools/list' => $this->listTools($registry),
                'tools/call' => $this->callTool($registry, $user, $params),
                default => throw new \RuntimeException("Method not found: {$method}", -32601),
            };

            if ($isNotification) {
                return null;
            }

            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result ?? new \stdClass(),
            ];
        } catch (Throwable $e) {
            Log::warning('MCP dispatch error', [
                'method' => $method,
                'message' => $e->getMessage(),
            ]);

            if ($isNotification) {
                return null;
            }

            $code = $e->getCode();
            if (! is_int($code) || $code === 0) {
                $code = -32000;
            }

            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => $code,
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }

    private function initialize(): array
    {
        return [
            'protocolVersion' => self::PROTOCOL_VERSION,
            'capabilities' => [
                'tools' => new \stdClass(),
            ],
            'serverInfo' => [
                'name' => 'pmhelper',
                'version' => config('app.version', '1.0.0'),
            ],
        ];
    }

    private function listTools(ToolRegistry $registry): array
    {
        return [
            'tools' => array_map(fn ($t) => [
                'name' => $t->name(),
                'description' => $t->description(),
                'inputSchema' => $t->inputSchema(),
            ], $registry->all()),
        ];
    }

    private function callTool(ToolRegistry $registry, $user, array $params): array
    {
        $name = $params['name'] ?? '';
        $args = $params['arguments'] ?? [];

        $tool = $registry->get($name);
        if (! $tool) {
            throw new \RuntimeException("Tool not found: {$name}", -32602);
        }

        try {
            $output = $tool->execute($user, $args);

            return [
                'content' => [[
                    'type' => 'text',
                    'text' => is_string($output)
                        ? $output
                        : json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]],
                'isError' => false,
            ];
        } catch (Throwable $e) {
            return [
                'content' => [[
                    'type' => 'text',
                    'text' => $e->getMessage(),
                ]],
                'isError' => true,
            ];
        }
    }

    private function rpcError(?int $id, int $code, string $message, int $httpStatus = 200): JsonResponse
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], $httpStatus);
    }
}
