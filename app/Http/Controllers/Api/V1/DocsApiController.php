<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DocNode;
use App\Support\DocAccessResolver;
use App\Support\StudentMaterials\StudentMaterialsMarkdownRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocsApiController extends Controller
{
    public function nav(Request $request, DocAccessResolver $access): JsonResponse
    {
        $user = $request->user();
        $tree = $access->accessibleNavTree($user);

        return response()->json(['data' => $this->serializeNavTree($tree)]);
    }

    public function show(Request $request, DocAccessResolver $access, StudentMaterialsMarkdownRenderer $markdown, string $path): JsonResponse
    {
        $user = $request->user();
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));
        $node = DocNode::findByPathSegments($segments);
        if ($node === null) {
            throw new NotFoundHttpException;
        }

        if (! $access->can($user, $node)) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        if ($node->is_folder) {
            $children = $node->children()->orderBy('sort_order')->orderBy('id')->get();
            $children = $access->filterAccessible($user, $children);

            return response()->json([
                'data' => [
                    'type' => 'folder',
                    'id' => $node->id,
                    'slug' => $node->slug,
                    'title' => $node->title,
                    'path' => implode('/', $node->pathSegments()),
                    'children' => $children->map(fn (DocNode $c) => [
                        'id' => $c->id,
                        'slug' => $c->slug,
                        'title' => $c->title,
                        'is_folder' => $c->is_folder,
                    ]),
                ],
            ]);
        }

        $body = (string) ($node->body ?? '');
        $html = $body !== '' ? $markdown->toHtml($body) : '';

        return response()->json([
            'data' => [
                'type' => 'page',
                'id' => $node->id,
                'slug' => $node->slug,
                'title' => $node->title,
                'path' => implode('/', $node->pathSegments()),
                'body_markdown' => $body,
                'body_html' => $html,
            ],
        ]);
    }

    /**
     * @param  list<array{node: DocNode, children: mixed}>  $branches
     * @return list<array<string, mixed>>
     */
    private function serializeNavTree(array $branches): array
    {
        $out = [];
        foreach ($branches as $b) {
            /** @var DocNode $node */
            $node = $b['node'];
            $out[] = [
                'id' => $node->id,
                'slug' => $node->slug,
                'title' => $node->title,
                'is_folder' => $node->is_folder,
                'children' => $this->serializeNavTree($b['children'] ?? []),
            ];
        }

        return $out;
    }
}
