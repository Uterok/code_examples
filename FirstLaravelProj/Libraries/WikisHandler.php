<?php

namespace App\Libraries\Wikis;

use App\Libraries\Subscriptions\SubscriptionsHandler;
use App\Models\Wikis\Wiki;
use App\Notifications\Wikis\WikiTrialExpired;
use App\Notifications\Wikis\WikiTrialExpiredTomorrow;
use Laravel\Cashier\Subscription;

class WikisHandler
{
    public const WIKI_SUBSCRIPTION_NAME_PREFIX = 'wiki';
    public const WIKI_SUBSCRIPTION_NAME_DELIMITER = '-';

    public static function saveWikiInfo(array $data_to_save): Wiki
    {
        $model = null;

        try {
            \DB::beginTransaction();

            if (! isset($data_to_save['id'])) {
                $model = new Wiki();
                $data_to_save['user_id'] = request()->user()->id;
            } else {
                $model = Wiki::find($data_to_save['id']);
            }

            $incompleted = ! $model->completed;

            $model->fill($data_to_save);
            $model->completed = true;
            $model->save();

            if ($incompleted) {
                WikiInfraHandler::createWiki(
                    $model,
                    $data_to_save['admin_username'],
                    $data_to_save['admin_password']
                );
            }

            \DB::commit();
        } catch (\Exception $e) {
            //rollback transaction
            \DB::rollback();
            throw $e;
        }

        return $model;
    }

    public static function assignSubscriptionForWiki(
        ?int $wiki_id,
        string $payment_method,
        string $plan_id,
        $addons = []
    ): Wiki {
        $user = request()->user();
        $wiki_subscription = null;
        try {
            \DB::beginTransaction();

            $wiki = Wiki::allowed()->where('id', $wiki_id)->first();
            if (! isset($wiki)) {
                $wiki = new Wiki();
                $wiki->user_id = $user->id;
                $wiki->save();
            }

            $wiki->changePlansInfo($plan_id, $addons);

            $wiki_subscription = SubscriptionsHandler::syncWikiSubscriptionWithStripe(
                $wiki,
                $payment_method,
                $wiki_subscription
            );
            if (! isset($wiki_subscription)) {
                \DB::rollback();
                return $wiki;
            }

            $wiki->subscription_id = $wiki_subscription->stripe_id ?? null;
            $wiki->active = true;
            $wiki->save();

            \DB::commit();
        } catch (\Exception $e) {
            //rollback transaction
            \DB::rollback();
            if ($wiki_subscription instanceof Subscription) {
                $wiki_subscription = $wiki_subscription->replicate();
                $wiki_subscription->cancelNow();
            }
            throw $e;
        }

        $user->updateDefaultPaymentMethod($payment_method);

        return $wiki;
    }

    public static function collectWikiSubscriptionName(Wiki $wiki): string
    {
        $prefix = self::WIKI_SUBSCRIPTION_NAME_PREFIX;
        $now_ts = now()->timestamp;
        $delimiter = self::WIKI_SUBSCRIPTION_NAME_DELIMITER;
        return "{$prefix}{$delimiter}{$wiki->id}{$delimiter}{$now_ts}";
    }

    public static function getWikiIdFromSubscriptionName(?string $name): string
    {
        $name_parsed = static::parseWikiSubscriptionName($name);
        return $name_parsed['id'];
    }

    public static function deactivateWiki($wiki): Wiki
    {
        $wiki = is_a($wiki, Wiki::class) ?
                $wiki :
                Wiki::allowed()->where('id', $wiki)->first();

        $wiki->clearPlansInfo();
        SubscriptionsHandler::syncWikiSubscriptionWithStripe($wiki);
        $wiki->subscription_id = null;
        $wiki->active = false;
        $wiki->save();

        return $wiki;
    }

    public static function checkTrialWikis(): void
    {
        $now = now();

        $trials_expired = Wiki::trial()
            ->where('trial_expires_at', '<=', $now)
            ->with('user')
            ->get();

        foreach ($trials_expired as $wiki) {
            $wiki->deactivateTrial();

            $user = $wiki->user;
            if (! isset($user)) {
                continue;
            }
            $user->notify(
                new WikiTrialExpired($wiki)
            );
        }

        $trials_expired_tomorrow = Wiki::trial()
            ->where('trial_expires_at', '>', $now)
            ->where('trial_expires_at', '<', $now->addDay())
            ->with('user')
            ->get();

        foreach ($trials_expired_tomorrow as $wiki) {
            $user = $wiki->user;
            if (! isset($user)) {
                continue;
            }
            $user->notify(
                new WikiTrialExpiredTomorrow($wiki)
            );
        }
    }

    private static function parseWikiSubscriptionName(?string $name): array
    {
        $result = [
            'id' => null,
            'ts' => null,
        ];

        if (! is_string($name) || ! strlen($name)) {
            return $result;
        }

        $exploded = explode(self::WIKI_SUBSCRIPTION_NAME_DELIMITER, $name);
        if (! isset($exploded[0]) || $exploded[0] !== self::WIKI_SUBSCRIPTION_NAME_PREFIX) {
            return $result;
        }
        $result['id'] = $exploded[1] ? (int) $exploded[1] : null;
        $result['ts'] = $exploded[2] ? (int) $exploded[2] : null;
        return $result;
    }
}
