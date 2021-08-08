<?php

namespace App\Http\Controllers\Api\Wikis;

use App\Http\Controllers\Controller;
use App\Libraries\Plans\PlansHandler;
use App\Libraries\Subscriptions\SubscriptionsHandler;
use App\Libraries\Wikis\WikisHandler;
use App\Models\Wikis\Wiki;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WikisController extends Controller
{
    public function index(): JsonResponse
    {
        $wikis = Wiki::allowed()->get();

        return response()->json($wikis);
    }

    public function store(Request $request): JsonResponse
    {
        $validated_data = $request->validate([
            'name' => 'required|string',
            'lang' => 'required|string',
            'logo' => 'nullable|string',
            'subdomain' => [
                'required', 
                'string', 
                'min:' . Wiki::SUBDOMAIN_LENGTH_MIN, 
                'max:' . Wiki::SUBDOMAIN_LENGTH_MAX, 
                Rule::unique('wikis')
            ],
            'admin_username' => 'required|string',
            'admin_password' => 'required|string',
        ]);

        if (request()->user()->wikis()->trial()->exists()) {
            return response()->json(['message' => 'Auth user already has trial plan.'], 400);
        }

        $model = WikisHandler::saveWikiInfo($validated_data);

        return response()->json($model);
    }

    public function show(int $id): JsonResponse
    {
        $wiki = Wiki::allowed()
            ->where('id', $id)
            ->firstOrFail();

        \Log::info('return wiki');
        \Log::info($wiki);
        \Log::info($wiki->toArray());

        return response()->json($wiki);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $raw_data = $request->all();
        $validated_data = $request->validate([
            'id' => 'required|exists:wikis,id',
            'name' => 'required|string',
            'lang' => 'required|string',
            'logo' => 'nullable|string',
            'subdomain' => [
                'required', 
                'string', 
                'min:' . Wiki::SUBDOMAIN_LENGTH_MIN,
                'max:' . Wiki::SUBDOMAIN_LENGTH_MAX,
                Rule::unique('wikis')->ignore($raw_data['id'], 'id')
            ],
            'admin_username' => 'required|string',
            'admin_password' => 'required|string',
        ]);

        $wiki = Wiki::find($validated_data['id']);
        if (! $wiki->isAuthUserWiki()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $model = WikisHandler::saveWikiInfo($validated_data);

        return response()->json($model);
    }

    public function destroy(int $id): JsonResponse
    {
        Wiki::deleteSoftModel($id);

        return response()->json(null, 204);
    }

    public function checkSubdomainUnique(): JsonResponse
    {
        $wiki_id = request()->query('id');
        $subdomain = request()->query('subdomain');

        if (
            ! isset($subdomain) ||
            (is_string($subdomain) && ! strlen($subdomain))
        ) {
            return response()->json(['correct' => false]);
        }

        $is_correct = ! Wiki::when(is_numeric($wiki_id), function ($query) use ($wiki_id) {
            $query->where('id', '<>', $wiki_id);
        })
            ->where('subdomain', $subdomain)
            ->exists();

        return response()->json(['correct' => $is_correct]);
    }

    public function createDraftWiki(): JsonResponse
    {
        $validated_data = request()->validate([
            'plan_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (! PlansHandler::isPlansExists($value)) {
                        $fail("Plan with id {$value} is not exists.");
                    }
                },
            ],
            'addons' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    if (! count($value)) {
                        return;
                    }

                    if (! PlansHandler::isAddonsExists($value)) {
                        $fail('Some addons in list is not exists.');
                    }
                },
            ],
        ]);

        $addons = isset($validated_data['addons']) && is_array($validated_data['addons']) ?
                  $validated_data['addons'] :
                  [];

        $wiki = new Wiki();
        $wiki->user_id = request()->user()->id;
        $wiki->changePlansInfo($validated_data['plan_id'], $addons);
        $wiki->save();

        return response()->json($wiki);
    }

    public function assignPlanToWiki(): JsonResponse
    {
        $validated_data = request()->validate([
            'wiki_id' => 'required|exists:wikis,id',
            'plan_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (! PlansHandler::isPlansExists($value)) {
                        $fail("Plan with id {$value} is not exists.");
                    }
                },
            ],
            'addons' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    if (! count($value)) {
                        return;
                    }

                    if (! PlansHandler::isAddonsExists($value)) {
                        $fail('Some addons in list is not exists.');
                    }
                },
            ],
        ]);

        $addons = isset($validated_data['addons']) && is_array($validated_data['addons']) ?
                  $validated_data['addons'] :
                  [];

        $wiki = Wiki::allowed()
            ->where('id', $validated_data['wiki_id'])
            ->firstOrFail();
        $wiki->changePlansInfo($validated_data['plan_id'], $addons);
        $wiki->save();

        return response()->json($wiki);
    }

    public function addAddonsToWiki(): JsonResponse
    {
        $validated_data = request()->validate([
            'wiki_id' => 'required|exists:wikis,id',
            'addons' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    if (! count($value)) {
                        return;
                    }

                    if (! PlansHandler::isAddonsExists($value)) {
                        $fail('Some addons in list is not exists.');
                    }
                },
            ],
        ]);

        $wiki = Wiki::allowed()
            ->where('id', $validated_data['wiki_id'])
            ->firstOrFail();

        $wiki->addAddonsToWiki($validated_data['addons']);
        $wiki->save();

        SubscriptionsHandler::syncWikiSubscriptionWithStripe($wiki);

        return response()->json($wiki);
    }

    public function removeAddonsFromWiki(): JsonResponse
    {
        $validated_data = request()->validate([
            'wiki_id' => 'required|exists:wikis,id',
            'addons' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    if (! count($value)) {
                        return;
                    }

                    if (! PlansHandler::isAddonsExists($value)) {
                        $fail('Some addons in list is not exists.');
                    }
                },
            ],
        ]);

        $wiki = Wiki::allowed()
            ->where('id', $validated_data['wiki_id'])
            ->firstOrFail();

        $wiki->removeAddonsFromWiki($validated_data['addons']);
        $wiki->save();

        SubscriptionsHandler::syncWikiSubscriptionWithStripe($wiki);

        return response()->json($wiki);
    }

    public function getNotWikiAddons(): JsonResponse
    {
        $wiki_id = request()->query('wiki_id');
        $interval = request()->query('interval');

        \Log::info('WIKI NOT ADDONS');
        \Log::info($wiki_id);

        $wiki = Wiki::allowed()
            ->where('id', $wiki_id)
            ->firstOrFail();

        $addons_diff_ids = PlansHandler::getDiffAddonsList(collect($wiki->addons)->pluck('id')->all(), $interval);
        $addons_diff = PlansHandler::getPlansInfoById(PlansHandler::PLAN_TYPE_ADDON, $addons_diff_ids);

        return response()->json($addons_diff);
    }

    public function subscribePlan(): JsonResponse
    {
        $validated_data = request()->validate([
            'wiki_id' => 'nullable|exists:wikis,id',
            'payment_method' => 'required|string',
            'plan_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (! PlansHandler::isPlansExists($value)) {
                        $fail("Plan with id {$value} is not exists.");
                    }
                },
            ],
            'addons' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    if (! count($value)) {
                        return;
                    }

                    if (! PlansHandler::isAddonsExists($value)) {
                        $fail('Some addons in list is not exists.');
                    }
                },
            ],
        ]);

        $wiki = WikisHandler::assignSubscriptionForWiki(
            $validated_data['wiki_id'] ?? null,
            $validated_data['payment_method'],
            $validated_data['plan_id'],
            $validated_data['addons']
        );

        return response()->json($wiki);
    }

    public function deactivate(): JsonResponse
    {
        $validated_data = request()->validate([
            'wiki_id' => 'required|exists:wikis,id',
        ]);

        WikisHandler::deactivateWiki($validated_data['wiki_id']);

        return response()->json(['success' => true]);
    }

    public function getWikiLangsList(): JsonResponse
    {
        $wiki_lang_list = config('langs');

        return response()->json($wiki_lang_list);
    }
}
