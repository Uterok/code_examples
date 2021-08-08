<?php

namespace App\Libraries\Plans;

use App\Libraries\Stripe\StripeHandler;
use App\Libraries\Traits\Consoleable;

class PlansHandler
{
    use Consoleable;

    public const PRODUCT_ID_PLANS_MONTHLY = 'plans-monthly';
    public const PRODUCT_ID_PLANS_YEARLY = 'plans-yearly';
    public const PRODUCT_ID_ADDONS_MONTHLY = 'addons-monthly';
    public const PRODUCT_ID_ADDONS_YEARLY = 'addons-yearly';

    public const PLAN_TYPE_PLAN = 'plan';
    public const PLAN_TYPE_ADDON = 'addon';

    public const PLAN_ID_DIVIDER = '_';

    public const STORAGE_SPACE_GROUP_NAME = 'storage-space';

    public static function getProductNamesToStore(): array
    {
        return [
            self::PRODUCT_ID_PLANS_MONTHLY,
            self::PRODUCT_ID_PLANS_YEARLY,
            self::PRODUCT_ID_ADDONS_MONTHLY,
            self::PRODUCT_ID_ADDONS_YEARLY,
        ];
    }

    public static function getValidPlanTypes(): array
    {
        return [
            self::PLAN_TYPE_PLAN,
            self::PLAN_TYPE_ADDON,
        ];
    }

    public static function getValidPlanIntervals(): array
    {
        return [
            StripeHandler::INTERVAL_YEAR,
            StripeHandler::INTERVAL_MONTH,
        ];
    }

    public static function isPlanTypeValid(?string $plan_type): bool
    {
        return in_array($plan_type, static::getValidPlanTypes());
    }

    public static function isIntervalValid(?string $interval): bool
    {
        return in_array($interval, static::getValidPlanIntervals());
    }

    public static function createPlanId(?string $product_id, string $plan_name): string
    {
        return $product_id . self::PLAN_ID_DIVIDER . $plan_name;
    }

    public static function getPlanProduct(?string $plan_product_id): array
    {
        $products_list = config('plans.products');

        if (! is_array($products_list) || ! count($products_list)) {
            return [];
        }

        $products_list = collect($products_list);
        return $products_list->firstWhere('id', $plan_product_id);
    }

    public static function pushPlanInfoToStripe(array $plan_info, ?string $plan_product_id = null): array
    {
        $plan_product_id = $plan_product_id ?? ($plan_info['product_id'] ?? null);

        $plan_product = static::getPlanProduct($plan_product_id);

        return StripeHandler::storePlan($plan_info, $plan_product);
    }

    public static function storeProductsWithPlans(array $product_plans_info, mixed $console = null): void
    {
        if (! is_array($product_plans_info) || ! count($product_plans_info)) {
            return;
        }

        foreach ($product_plans_info as $product_id => $product_info) {
            foreach ($product_info as $plan_key => $plan_info) {
                $plan_id = $plan_info['id'];
                static::showConsoleInfoMsg(
                    "Sync product - {$product_id}, plan - {$plan_id}.",
                    $console
                );
                static::pushPlanInfoToStripe($plan_info, $product_id);
            }
        }
    }

    public static function storePlans(mixed $console = null): void
    {
        static::showConsoleInfoMsg('Try sync plans.', $console);
        $plans = config('plans.plans');
        static::storeProductsWithPlans($plans, $console);
        static::showConsoleInfoMsg('Try sync addons.', $console);
        $addons = config('plans.addons');
        static::storeProductsWithPlans($addons, $console);
    }

    public static function storeTstPlan(): void
    {
        $tst_plans = config('plans.tst');
        static::storeProductsWithPlans($tst_plans);
    }

    public static function getFullPlansListFromConfig(?bool $active = true): array
    {
        $list1 = static::getPlansListFromConfig(self::PLAN_TYPE_PLAN, StripeHandler::INTERVAL_MONTH, $active);
        $list2 = static::getPlansListFromConfig(self::PLAN_TYPE_PLAN, StripeHandler::INTERVAL_YEAR, $active);

        return array_merge($list1, $list2);
    }

    public static function getFullAddonsListFromConfig(?bool $active = true): array
    {
        $list1 = static::getPlansListFromConfig(self::PLAN_TYPE_ADDON, StripeHandler::INTERVAL_MONTH, $active);
        $list2 = static::getPlansListFromConfig(self::PLAN_TYPE_ADDON, StripeHandler::INTERVAL_YEAR, $active);

        return array_merge($list1, $list2);
    }

    public static function checkPlansInfoExistence(string $type, mixed $id_info, bool $active = true): array
    {
        $id_info = is_array($id_info) ? $id_info : [$id_info];

        $input_ids = collect($id_info)->map(function ($item) {
            if (is_string($item)) {
                return $item;
            }
            if (is_array($item) && array_key_exists('id', $item) && is_string($item['id']) && strlen($item['id'])) {
                return $item['id'];
            }
            return null;
        })
            ->filter(function ($item) {
                return $item !== null;
            })
            ->values();
        $existed_plans = static::getPlansInfoById($type, $input_ids->toArray(), $active);
        $existed_ids = collect($existed_plans)->pluck('id')->values();
        $diff = $input_ids->diff($existed_ids)->values()->all();
        return $diff;
    }

    public static function getDiffAddonsList(
        array $input_addons_list_ids,
        string $interval,
        bool $active = true
    ): array {
        $input_addons_list_ids = count($input_addons_list_ids) ? $input_addons_list_ids : [];
        $interval = static::isIntervalValid($interval) ? $interval : StripeHandler::INTERVAL_MONTH;

        $all_addons_list = static::getPlansListFromConfig(self::PLAN_TYPE_ADDON, $interval, $active);
        $all_addons_list_ids = collect($all_addons_list)->pluck('id')->all();
        $diff = array_diff($all_addons_list_ids, $input_addons_list_ids);
        $diff = array_unique($diff);
        $diff = array_values($diff);

        return $diff;
    }

    public static function isPlansExists(mixed $plans_info, ?bool $active = true): bool
    {
        $not_existed = static::checkPlansInfoExistence(self::PLAN_TYPE_PLAN, $plans_info, $active);
        return ! count($not_existed);
    }

    public static function isAddonsExists(array $plans_info, ?bool $active = true): bool
    {
        $not_existed = static::checkPlansInfoExistence(self::PLAN_TYPE_ADDON, $plans_info, $active);
        return ! count($not_existed);
    }

    public static function getPlansInfoById(?string $type, mixed $id_info, ?bool $active = true): ?array
    {
        $method = $type === self::PLAN_TYPE_ADDON ? 'getFullAddonsListFromConfig' : 'getFullPlansListFromConfig';

        $plans_list = static::{$method}($active);
        $plans_list = collect($plans_list);

        if (is_array($id_info)) {
            $plans_result = $plans_list->whereIn('id', $id_info)->values()->toArray();
        } else {
            $plans_result = $plans_list->firstWhere('id', $id_info);
        }

        return $plans_result;
    }

    public static function getPlansListFromConfig(
        ?string $type = null,
        ?string $interval = null,
        ?bool $active = true
    ): array {
        $type = static::isPlanTypeValid($type) ? $type : self::PLAN_TYPE_PLAN;
        $interval = static::isIntervalValid($interval) ? $interval : StripeHandler::INTERVAL_MONTH;
        $product = null;

        if (
            ($type === self::PLAN_TYPE_PLAN) &&
            ($interval === StripeHandler::INTERVAL_YEAR)
        ) {
            $product = self::PRODUCT_ID_PLANS_YEARLY;
        } elseif (
            ($type === self::PLAN_TYPE_ADDON) &&
            ($interval === StripeHandler::INTERVAL_MONTH)
        ) {
            $product = self::PRODUCT_ID_ADDONS_MONTHLY;
        } elseif (
            ($type === self::PLAN_TYPE_ADDON) &&
            ($interval === StripeHandler::INTERVAL_YEAR)
        ) {
            $product = self::PRODUCT_ID_ADDONS_YEARLY;
        } else {
            $product = self::PRODUCT_ID_PLANS_MONTHLY;
        }

        $plans_list = config('plans.' . $type . 's.' . $product);

        if (! is_array($plans_list)) {
            return [];
        }

        if (isset($active)) {
            $plans_list = array_filter(
                $plans_list,
                function ($item) use ($active) {
                    return is_array($item) && isset($item['active']) && ((bool) $item['active'] === (bool) $active);
                }
            );
            $plans_list = array_values($plans_list);
        }

        return $plans_list;
    }

    public static function checkIsStripePriceAPlan(string $stripe_price_id): bool
    {
        if (! is_string($stripe_price_id)) {
            return false;
        }

        return str_starts_with($stripe_price_id, self::PRODUCT_ID_PLANS_MONTHLY) ||
               str_starts_with($stripe_price_id, self::PRODUCT_ID_PLANS_YEARLY);
    }
}
