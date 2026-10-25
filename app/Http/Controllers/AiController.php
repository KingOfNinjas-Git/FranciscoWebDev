<?php

namespace App\Http\Controllers;

use App\Models\AiGeneration;
use App\Models\Post;
use App\Services\Ai\AiWriter;
use App\Services\Ai\SiteContextBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * JSON endpoints behind the "Draft with AI" panel (admin only).
 */
class AiController extends Controller
{
    // Shows exactly what the AI is given before it writes: the parsed live site, projects and existing posts
    public function context(Request $request, SiteContextBuilder $context): JsonResponse
    {
        return response()->json($context->build($request->boolean('fresh')));
    }

    public function draft(Request $request): JsonResponse
    {
        $data = $request->validate([
            'topic' => 'required|string|min:10|max:2000',
            'type' => ['required', Rule::in(array_keys(AiWriter::TYPES))],
            'tone' => ['required', Rule::in(array_keys(AiWriter::TONES))],
            'length' => ['required', Rule::in(array_keys(AiWriter::LENGTHS))],
            'project' => 'nullable|string|max:255',
            'fresh_only' => 'sometimes|boolean',
        ]);

        return $this->start('draft', $data);
    }

    public function translate(Request $request): JsonResponse
    {
        // A blog post needs its body; a project may have just a short description or just the details
        $isProject = $request->input('kind') === 'project';

        $data = $request->validate([
            'kind' => 'nullable|in:post,project',
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:5000',
            'body' => [$isProject ? 'nullable' : 'required', 'string', 'max:100000'],
            'locales' => 'required|array|min:1',
            'locales.*' => Rule::in(Post::TRANSLATION_LOCALES),
        ]);
        $data['kind'] = $isProject ? 'project' : 'post';
        $data['excerpt'] = $data['excerpt'] ?? '';
        $data['body'] = $data['body'] ?? '';

        return $this->start('translate', $data);
    }

    public function status(int $id): JsonResponse
    {
        $job = AiGeneration::findOrFail($id);

        if ($job->isStale()) {
            $job->update(['status' => 'failed', 'error' => 'The request did not finish. Please try again.']);
        }

        return response()->json([
            'status' => $job->status,
            'error' => $job->error,
            'result' => $job->result,
            'model' => $job->model,
            'input_tokens' => $job->input_tokens,
            'output_tokens' => $job->output_tokens,
            'context_stats' => $job->context_stats,
        ]);
    }

    private function start(string $kind, array $params): JsonResponse
    {
        // Cost guard
        $today = AiGeneration::where('created_at', '>=', now()->startOfDay())->count();
        if ($today >= config('ai.daily_limit')) {
            return response()->json(['message' => 'Daily AI limit reached (' . config('ai.daily_limit') . '). Raise AI_DAILY_LIMIT in .env if you need more.'], 429);
        }

        if (blank(config('ai.api_key'))) {
            return response()->json(['message' => 'The AI is not configured yet: set ' . config('ai.key_name') . ' in the .env file.'], 503);
        }

        $job = AiGeneration::create(['kind' => $kind, 'status' => 'pending', 'params' => $params]);

        // Runs after this response has been sent, so a slow AI call never hits a web-server timeout;
        // the browser polls /admin/ai/{id} for the result.
        dispatch(function () use ($job) {
            app(AiWriter::class)->run($job->fresh());
        })->afterResponse();

        return response()->json(['id' => $job->id]);
    }
}
