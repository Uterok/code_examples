<?php

namespace App\Libraries\Stripe;

use Stripe\Collection as StripeCollection;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\StripeObject;

class StripeHandler
{
    public const INTERVAL_YEAR = 'year';
    public const INTERVAL_MONTH = 'month';

    public const STRIPE_ITEM_TYPE_PLAN = 'stripe_plan';
    public const STRIPE_ITEM_TYPE_TAX_RATE = 'stripe_tax_rate';
    public const STRIPE_ITEM_TYPE_WEBHOOK = 'stripe_webhook';

    public const FIELDS_ALLOWED_ON_CREATE = [
        'amount',
        'currency',
        'interval',
        'product',
        'active',
        'metadata',
        'nickname',
        'id',
        'tiers',
        'tiers_mode',
        'aggregate_usage',
        'amount_decimal',
        'billing_scheme',
        'interval_count',
        'transform_usage',
        'trial_period_days',
        'usage_type',
    ];

    public const FIELDS_ALLOWED_ON_UPDATE = [
        'active',
        'meatdata',
        'nickname',
        // 'product',
        'trial_period_days',
    ];

    public const FIELDS_ALLOWED_ON_CREATE_TAX_RATE = [
        'display_name',
        'inclusive',
        'percentage',
        'active',
        'country',
        'description',
        'jurisdiction',
        'metadata',
        'state',
        'tax_type',
    ];

    public const FIELDS_ALLOWED_ON_UPDATE_TAX_RATE = [
        'active',
        'country',
        'description',
        'display_name',
        'jurisdiction',
        'metadata',
        'state',
        'tax_type',
    ];

    public const FIELDS_ALLOWED_ON_CREATE_WEBHOOK = [
        'enabled_events',
        'url',
        'api_version',
        'description',
        'metadata',
        'connect',
    ];

    public const FIELDS_ALLOWED_ON_UPDATE_WEBHOOK = [
        'description',
        'enabled_events',
        'metadata',
        'url',
        'disabled',
    ];

    public static function getStripeClient(?string $key = null): StripeClient
    {
        $key = $key ?? config('services.stripe.secret');
        return new StripeClient($key);
    }

    public static function configureApiKey(?string $key = null): void
    {
        $key = $key ?? config('services.stripe.secret');
        Stripe::setApiKey($key);
    }

    public static function stripeAmountToNormal(int | float | null $stripe_amount): int | float | null
    {
        return is_numeric($stripe_amount) ? $stripe_amount / 100 : $stripe_amount;
    }

    public static function normalAmountToStripe(int | float | null $normal_amount): int | float | null
    {
        return is_numeric($normal_amount) ? $normal_amount * 100 : $normal_amount;
    }

    public static function getArrayFromStripeItem(StripeObject $stripe_item): array
    {
        $stripe_item_array = $stripe_item->toArray();
        static::decodeStripeItemMetadataFields($stripe_item_array);
        return $stripe_item_array;
    }

    public static function getArrayFromStripeCollection(StripeCollection $stripe_collection): array
    {
        $stripe_collection_data = $stripe_collection->data;
        $result_array = [];

        foreach ($stripe_collection_data as $item) {
            $result_array[] = static::getArrayFromStripeItem($item);
        }

        return $result_array;
    }

    public static function decodeStripeItemMetadataFields(array &$stripe_item_info): void
    {
        if (isset($stripe_item_info['metadata']) && is_array($stripe_item_info['metadata'])) {
            foreach ($stripe_item_info['metadata'] as $key => $value) {
                $stripe_item_info['metadata'][$key] = validateJSON($stripe_item_info['metadata'][$key]) ?
                                               json_decode($stripe_item_info['metadata'][$key]) :
                                               $stripe_item_info['metadata'][$key];
            }
        }
    }

    public static function encodeStripeItemMetadataFields(array &$stripe_item_info): void
    {
        if (! isset($stripe_item_info['metadata']) || ! is_array($stripe_item_info['metadata'])) {
            $stripe_item_info['metadata'] = [];
        } else {
            foreach ($stripe_item_info['metadata'] as $key => $value) {
                if (is_string($value)) {
                    continue;
                }
                $stripe_item_info['metadata'][$key] = json_encode($stripe_item_info['metadata'][$key]);
            }
        }
    }

    public static function filterStripeItemInfoFields(array $stripe_item_info, array $allowed_fields): array
    {
        return array_filter(
            $stripe_item_info,
            function ($v, $k) use ($allowed_fields) {
                return in_array($k, $allowed_fields);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    public static function filterStripeItemsFieldsForCreate(array $stripe_item_info, string $type): array
    {
        $allowed_fields = [];

        switch ($type) {
            case self::STRIPE_ITEM_TYPE_PLAN:
                $allowed_fields = self::FIELDS_ALLOWED_ON_CREATE;
                break;
            case self::STRIPE_ITEM_TYPE_TAX_RATE:
                $allowed_fields = self::FIELDS_ALLOWED_ON_CREATE_TAX_RATE;
                break;
            case self::STRIPE_ITEM_TYPE_WEBHOOK:
                $allowed_fields = self::FIELDS_ALLOWED_ON_CREATE_WEBHOOK;
                break;
        }

        return static::filterStripeItemInfoFields($stripe_item_info, $allowed_fields);
    }

    public static function filterStripeItemsFieldsForUpdate(array $stripe_item_info, string $type): array
    {
        $allowed_fields = [];

        switch ($type) {
            case self::STRIPE_ITEM_TYPE_PLAN:
                $allowed_fields = self::FIELDS_ALLOWED_ON_UPDATE;
                break;
            case self::STRIPE_ITEM_TYPE_TAX_RATE:
                $allowed_fields = self::FIELDS_ALLOWED_ON_UPDATE_TAX_RATE;
                break;
            case self::STRIPE_ITEM_TYPE_WEBHOOK:
                $allowed_fields = self::FIELDS_ALLOWED_ON_UPDATE_WEBHOOK;
                break;
        }

        return static::filterStripeItemInfoFields($stripe_item_info, $allowed_fields);
    }

    // ######PRODUCTS######
    public static function getProductsList(?bool $active = true): array
    {
        $stripe = static::getStripeClient();
        $params = [];
        if (isset($active)) {
            $params['active'] = (bool) $active;
        }
        $products_response = $stripe->products->all($params);
        return static::getArrayFromStripeCollection($products_response);
    }

    public static function getProduct(string $product_id): ?array
    {
        $producst_list = static::getProductsList(null);
        $producst_list = collect($producst_list);

        return $producst_list->firstWhere('id', $product_id);
    }

    public static function filterPlanFieldsForCreate(array $plan_info): array
    {
        return array_filter(
            $plan_info,
            function ($v, $k) {
                return in_array($k, self::FIELDS_ALLOWED_ON_CREATE);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    public static function filterPlanFieldsForUpdate(array $plan_info): array
    {
        return array_filter(
            $plan_info,
            function ($v, $k) {
                return in_array($k, self::FIELDS_ALLOWED_ON_UPDATE);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    // ######PLANS######
    public static function getAllPlans(?string $product = null, ?bool $active = null): array
    {
        $stripe = static::getStripeClient();

        $params = ['limit' => 100];

        if (isset($product)) {
            $params['product'] = $product;
        }

        if (isset($active)) {
            $params['active'] = $active;
        }

        $plans_response = $stripe->plans->all($params);
        return static::getArrayFromStripeCollection($plans_response);
    }

    public static function getPlanById(string $plan_id): array
    {
        $stripe = static::getStripeClient();

        $response = $stripe->plans->retrieve(
            $plan_id,
            []
        );

        return $response->toArray();
    }

    public static function checkProductBeforePushToStripe(array &$data_to_push, array $product_info): void
    {
        $product_id = $product_info['id'];
        $existed_product = static::getProduct($product_id);

        $data_to_push['product'] = ! isset($existed_product) ? $product_info : $product_info['id'];
    }

    public static function createPlan(array $data, array $product): array
    {
        static::checkProductBeforePushToStripe($data, $product);
        // dd($data);

        $stripe = static::getStripeClient();
        $data_to_send = static::filterPlanFieldsForCreate($data);
        static::encodeStripeItemMetadataFields($data_to_send);
        // dd($data_to_send);
        $created_plan = $stripe->plans->create($data_to_send);

        return $created_plan->toArray();
    }

    public static function updatePlan(array $data, array $product): array
    {
        static::checkProductBeforePushToStripe($data, $product);

        if (! isset($data['id'])) {
            return [];
        }

        $data_to_send = static::filterPlanFieldsForUpdate($data);
        static::encodeStripeItemMetadataFields($data_to_send);

        $stripe = static::getStripeClient();

        $updated_plan = $stripe->plans->update(
            $data['id'],
            $data_to_send
        );

        return $updated_plan->toArray();
    }

    public static function deletePlan(string $plan_id): bool
    {
        $stripe = static::getStripeClient();

        $response = $stripe->plans->delete(
            $plan_id,
            []
        );

        return $response['deleted'] ?? false;
    }

    public static function checkPlanExistense(string $plan_id): bool
    {
        $all_plans = static::getAllPlans();
        $all_plans = collect($all_plans);
        $existed_plan = $all_plans->firstWhere('id', $plan_id);
        return isset($existed_plan);
    }

    public static function storePlan(array $data, array $product): array
    {
        // dd($data);
        $plan_id = $data['id'] ?? null;
        $create_plan = ! isset($plan_id) || ! static::checkPlanExistense($plan_id);

        // transform amount to cents
        if (isset($data['amount']) && is_numeric($data['amount'])) {
            $data['amount'] *= 100;
        }

        $method_name = $create_plan ? 'createPlan' : 'updatePlan';
        return static::{$method_name}($data, $product);
    }

    // ######CUSTOMERS######
    public static function deleteCustomer(string $customer_id, ?bool $check_existense = true): void
    {
        $stripe = static::getStripeClient();

        do {
            if (! $check_existense) {
                break;
            }
        } while (false);

        try {
            $stripe->customers->delete(
                $customer_id,
                []
            );
        } catch (\Exception $e) {
            \Log::info('DELETE STRIPE CUSTOMER ' . $customer_id . ' ERROR. ' . $e->getMessage());
        }
    }

    // ######TAXES######
    public static function getAllTaxRates(?bool $active = true): array
    {
        $stripe = static::getStripeClient();
        $params = ['limit' => 100];
        if (isset($active)) {
            $params['active'] = (bool) $active;
        }
        $taxes_response = $stripe->taxRates->all($params);
        return static::getArrayFromStripeCollection($taxes_response);
    }

    public static function getTaxRate(string $tax_rate_unique_name): ?array
    {
        $tax_rates_list = static::getAllTaxRates(null);
        $tax_rates_list = collect($tax_rates_list);

        return $tax_rates_list->firstWhere('metadata.unique_name', $tax_rate_unique_name);
    }

    public static function createTaxRate(array $data): array
    {
        // dd($data);

        $stripe = static::getStripeClient();
        $data_to_send = static::filterStripeItemsFieldsForCreate($data, self::STRIPE_ITEM_TYPE_TAX_RATE);
        static::encodeStripeItemMetadataFields($data_to_send);
        // dd($data_to_send);
        $created_tax_rate = $stripe->taxRates->create($data_to_send);

        return static::getArrayFromStripeItem($created_tax_rate);
    }

    public static function updateTaxRate(string $stripe_tax_rate_id, array $data): array
    {
        $stripe = static::getStripeClient();
        $data_to_send = static::filterStripeItemsFieldsForUpdate($data, self::STRIPE_ITEM_TYPE_TAX_RATE);
        static::encodeStripeItemMetadataFields($data_to_send);
        // dd($stripe_tax_rate_id);

        $updated_tax_rate = $stripe->taxRates->update(
            $stripe_tax_rate_id,
            $data_to_send
        );

        return static::getArrayFromStripeItem($updated_tax_rate);
    }

    public static function checkTaxRateExistense(string $tax_rate_unique_name): array
    {
        $existed_tax_rate = static::getTaxRate($tax_rate_unique_name);
        return isset($existed_tax_rate);
    }

    public static function storeTaxRate(array $data): ?array
    {
        $tax_rate_unique_name = $data['metadata']['unique_name'] ?? null;

        if (! isset($tax_rate_unique_name) || ! is_string($tax_rate_unique_name) || ! strlen($tax_rate_unique_name)) {
            return null;
        }

        $stripe_tax_rate = static::getTaxRate($tax_rate_unique_name);
        $stripe_tax_rate_id = $stripe_tax_rate['id'] ?? null;

        $result_tax_rate = null;

        if (! isset($stripe_tax_rate_id)) {
            $result_tax_rate = static::createTaxRate($data);
        } else {
            $result_tax_rate = static::updateTaxRate($stripe_tax_rate_id, $data);
        }

        return $result_tax_rate;
    }

    // ######CHARGES######
    public static function getChargeInfo(string $id): array
    {
        $stripe = static::getStripeClient();
        $charge = null;
        try {
            $charge = $stripe->charges->retrieve(
                $id,
                []
            );
        } catch (\Exception $e) {
            \Log::error('Error when try get charge from stripe. ' . $e->getMessage());
        }

        return static::getArrayFromStripeItem($charge);
    }

    // ######WEBHOOKS######
    public static function getAllWebhooks(): array
    {
        $stripe = static::getStripeClient();
        $params = ['limit' => 100];
        $webhooks_response = $stripe->webhookEndpoints->all($params);
        // dd($webhooks_response);
        return static::getArrayFromStripeCollection($webhooks_response);
    }

    public static function getWebhook(string $webhook_unique_name): ?array
    {
        $webhooks_list = static::getAllWebhooks();
        $webhooks_list = collect($webhooks_list);
        // dd($webhooks_list);

        return $webhooks_list->firstWhere('metadata.unique_name', $webhook_unique_name);
    }

    public static function createWebhook(array $data): array
    {
        $stripe = static::getStripeClient();
        $data_to_send = static::filterStripeItemsFieldsForCreate($data, self::STRIPE_ITEM_TYPE_WEBHOOK);
        static::encodeStripeItemMetadataFields($data_to_send);
        // dd($data_to_send);
        $created_webhook = $stripe->webhookEndpoints->create($data_to_send);

        return static::getArrayFromStripeItem($created_webhook);
    }

    public static function updateWebhook(string $stripe_webhook_id, array $data): array
    {
        $stripe = static::getStripeClient();
        $data_to_send = static::filterStripeItemsFieldsForUpdate($data, self::STRIPE_ITEM_TYPE_WEBHOOK);
        static::encodeStripeItemMetadataFields($data_to_send);
        // dd($stripe_webhook_id);

        $updated_webhook = $stripe->webhookEndpoints->update(
            $stripe_webhook_id,
            $data_to_send
        );

        return static::getArrayFromStripeItem($updated_webhook);
    }

    public static function checkWebhookExistense(string $webhook_unique_name): bool
    {
        $existed_webhook = static::getWebhook($webhook_unique_name);
        return isset($existed_webhook);
    }

    public static function storeWebhook(array $data): ?array
    {
        $webhook_unique_name = $data['metadata']['unique_name'] ?? null;

        if (! isset($webhook_unique_name) || ! is_string($webhook_unique_name) || ! strlen($webhook_unique_name)) {
            return null;
        }

        $stripe_webhook = static::getWebhook($webhook_unique_name);
        $stripe_webhook_id = $stripe_webhook['id'] ?? null;

        $result_webhook = null;

        if (! isset($stripe_webhook_id)) {
            $result_webhook = static::createWebhook($data);
        } else {
            $result_webhook = static::updateWebhook($stripe_webhook_id, $data);
        }

        return $result_webhook;
    }
}
