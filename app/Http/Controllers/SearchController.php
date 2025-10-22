<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Typesense\Client;

class SearchController extends BaseController
{
    protected $typesense;

    public function __construct()
    {
        $this->typesense = new Client([
            'api_key' => env('TYPESENSE_API_KEY', ''),
            'nodes' => [
                [
                    'host' => env('TYPESENSE_HOST', 'localhost'),
                    'port' => env('TYPESENSE_PORT', '8108'),
                    'path' => env('TYPESENSE_PATH', ''),
                    'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
                ],
            ],
            'connectionTimeoutSeconds' => 2
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/search",
     *     tags={"Поиск"},
     *     @OA\Response(response="200", description="")
     * )
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        //$normalizedQuery = $this->normalizeQuery($query);
        $normalizedQuery = $query;

        if (strlen($normalizedQuery) < 3) {
            return response()->json([]);
        }

        $limit = 10; // Лимит результатов

        // Retrieve all collections
        $collections = $this->typesense->collections->retrieve();

        // Prepare multi-search queries
        $searchRequests = [];
        foreach ($collections as $collection) {
            $searchRequests[] = [
                'collection' => $collection['name'],
                'q' => $normalizedQuery,
                'query_by' => 'name',
                'sort_by' => '_text_match:desc',
                'num_typos' => 2,
                'prefix' => 'true',
                'boost_by' => 'popularity:10',
                'limit' => $limit
            ];
        }

        // Perform multi-search
        $searchResults = $this->typesense->multiSearch->perform(['searches' => $searchRequests]);

        // Extract and group results by collection
        $groupedResults = [];
        foreach ($searchResults['results'] as $result) {
            if (!isset($result['searches'])) {
                continue; // Skip if 'searches' key is not present
            }
            foreach ($result['searches'] as $searchResult) {
                $collectionName = $searchResult['collection'];
                if (!isset($groupedResults[$collectionName])) {
                    $groupedResults[$collectionName] = [];
                }
                $groupedResults[$collectionName] = array_merge($groupedResults[$collectionName], array_map(function ($hit) use ($collectionName) {
                    $document = $hit['document'];
                    return array_merge($document, [
                        'entity_type' => $collectionName,
                        '_text_match' => $hit['text_match'] ?? 0
                    ]);
                }, $searchResult['hits']));
            }
        }

        return ApiResponse::success($groupedResults);
    }

    private function normalizeQuery($query)
    {
        // Массив стоп-слов для тематики мотошин
        $stopWords = [

        ];

        // Заменяем все неалфавитно-цифровые символы, кроме кириллических букв, на пробелы
        $normalized = preg_replace('/[^\p{L}\p{N}]/u', ' ', $query);

        // Разбиваем строку на массив слов
        $words = explode(' ', $normalized);

        // Удаляем стоп-слова
        $filteredWords = array_filter($words, function ($word) use ($stopWords) {
            return !in_array(mb_strtolower($word), $stopWords);
        });

        // Соединяем оставшиеся слова в строку
        return trim(implode(' ', $filteredWords));
    }
}
