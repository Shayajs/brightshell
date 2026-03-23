<?php

namespace App\Http\Controllers\Docs;

use App\Http\Controllers\Controller;
use App\Models\DocNode;
use App\Support\DocAccessResolver;
use App\Support\StudentMaterials\StudentMaterialsMarkdownRenderer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageController extends Controller
{
    public function __construct(
        private readonly DocAccessResolver $access,
        private readonly StudentMaterialsMarkdownRenderer $markdown,
    ) {}

    public function show(Request $request, string $path): View
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        $segments = array_values(array_filter(explode('/', trim($path, '/'))));
        $node = DocNode::findByPathSegments($segments);
        if ($node === null) {
            throw new NotFoundHttpException;
        }

        if (! $this->access->can($user, $node)) {
            abort(403, 'Vous n’avez pas accès à cette page.');
        }

        if ($node->is_folder) {
            $children = $node->children()->orderBy('sort_order')->orderBy('id')->get();
            $children = $this->access->filterAccessible($user, $children);

            return view('portals.docs.folder', [
                'node' => $node,
                'children' => $children,
            ]);
        }

        $body = (string) ($node->body ?? '');
        $html = $body !== '' ? $this->markdown->toHtml($body) : '';

        return view('portals.docs.page', [
            'node' => $node,
            'html' => $html,
        ]);
    }
}
