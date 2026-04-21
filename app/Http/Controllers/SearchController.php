<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SearchController extends Controller
{
    private Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => config('services.ai_service.url', 'http://127.0.0.1:8000'),
            'timeout'  => 15.0,
        ]);
    }

    /**
     * Receive a natural language query, translate it via the AI microservice,
     * then filter the MySQL inventory using the structured parameters.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|max:500']);

        // ── Step 1: Call the Python microservice ──────────────────────────
        try {
            $aiResponse = $this->http->post('/translate-query', [
                'json' => ['query' => $request->input('query')],
            ]);

            $params = json_decode($aiResponse->getBody()->getContents(), true);

        } catch (RequestException $e) {
            return response()->json([
                'error'   => 'AI service unavailable.',
                'detail'  => $e->getMessage(),
            ], 503);
        }

        // ── Step 2: Build the Eloquent query from structured params ───────
        $query = InventoryItem::query();

        // Filter by category if Gemini identified one
        if (!empty($params['category'])) {
            $query->where('category', $params['category']);
        }

        // Filter inside the JSON `attributes` column
        if (!empty($params['attribute_filters'])) {
            foreach ($params['attribute_filters'] as $key => $value) {
                $this->applyAttributeFilter($query, $key, $value);
            }
        }

        // Fallback keyword search on name and JSON attributes
        if (!empty($params['keywords'])) {
            $query->where(function (Builder $q) use ($params) {
                foreach ($params['keywords'] as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%")
                      ->orWhereRaw(
                          "LOWER(JSON_UNQUOTE(JSON_EXTRACT(attributes, '$'))) LIKE ?",
                          ['%' . strtolower($keyword) . '%']
                      );
                }
            });
        }

        // Always prefer available items first
        $query->orderByRaw("FIELD(status, 'available', 'maintenance', 'checked_out')");

        $results = $query->get();

        return response()->json([
            'query'          => $request->input('query'),
            'intent_summary' => $params['intent_summary'] ?? '',
            'parsed_params'  => $params,
            'total'          => $results->count(),
            'results'        => $results,
        ]);
    }

    /**
     * Apply a filter to the JSON `attributes` column.
     * Supports exact match and range operators: gte, lte, eq.
     */
    private function applyAttributeFilter(Builder $query, string $key, mixed $value): void
    {
        $jsonPath = "attributes->>{$key}";   // Uses MySQL ->> operator (unquoted)

        if (is_array($value)) {
            // Range / comparison operators
            if (isset($value['gte'])) {
                $query->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(attributes, '$.{$key}')) AS DECIMAL) >= ?", [$value['gte']]);
            }
            if (isset($value['lte'])) {
                $query->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(attributes, '$.{$key}')) AS DECIMAL) <= ?", [$value['lte']]);
            }
            if (isset($value['eq'])) {
                $query->where($jsonPath, $value['eq']);
            }
        } else {
            // Exact match (case-insensitive for strings)
            $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(attributes, '$.{$key}'))) = ?",
                [strtolower((string) $value)]
            );
        }
    }
}
