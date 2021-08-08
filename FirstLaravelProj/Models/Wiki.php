<?php

namespace App\Models\Wikis;

use App\Libraries\Plans\PlansHandler;
use App\Models\Traits\ModelExtended;
use App\Models\Users\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;
use ProWiki\WikiInfra\Domain\Model\Plan;

/**
 * @property string subdomain
 * @property string|null subscription_id
 * @property DateTime|null trial_expires_at
 * @property bool active
 * @property array plans_info
 * @property int user_id
 * @property bool completed
 * @property User user
 * @property bool is_trial
 */
class Wiki extends Model
{
    use HasFactory, SoftDeletes, ModelExtended;

    public const PLAN_FIELD = 'plan';
    public const ADDONS_FIELD = 'addons';

    public const SUBDOMAIN_LENGTH_MIN = 4;
    public const SUBDOMAIN_LENGTH_MAX = 50;

    public const NO_PLAN_WIKI = 'NO_PLAN';

    protected $table = 'wikis';

    protected $fillable = [
        'user_id',
        'name',
        'lang',
        'logo',
        'subdomain',
        'plans_info',
        'trial_expires_at',
    ];

    protected $casts = [
        'plans_info' => 'array',
        'trial_expires_at' => 'datetime',
        'completed' => 'boolean',
        'active' => 'boolean',
    ];

    protected $appends = ['plan', 'addons', 'is_trial', 'trial_days_left', 'is_paid'];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($instance) {
            if (! $instance->isPlanAssigned()) {
                $instance->trial_expires_at = Carbon::now()->addDays(30);
            }

            return true;
        });

        static::saving(function ($instance) {
            if ($instance->isPlanAssigned()) {
                $instance->trial_expires_at = null;
            }

            return true;
        });

        static::saved(function ($instance) {
            do {
                if (!$instance->isCompleted()) {
                    break;
                }
                $instance->loadMissing('user');
                $wiki_user = $instance->user ?? $instance->user()->first();
                if (
                    isset($wiki_user) &&
                    $wiki_user->isOnRegistrationFlow()
                ) {
                    $wiki_user->resetRegistrationFlow(true);
                }
            } while (false);
        });
    }

    // scopes
    public function scopeAllowed(Builder $query): void
    {
        $query->when(request()->user(), function ($query) {
            $query->where('user_id', request()->user()->id);
        });
    }

    public function scopeTrial(Builder $query): void
    {
        $query->whereNull('plans_info->' . self::PLAN_FIELD)
            ->whereNotNull('trial_expires_at')
            ->active();
    }

    public function scopePaid(Builder $query): void
    {
        $query->whereNotNull('subscription_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    // getters setters
    public function getPlanAttribute(): ?array
    {
        $plan_id = $this->plans_info[self::PLAN_FIELD] ?? null;

        if (! isset($plan_id)) {
            return null;
        }

        return PlansHandler::getPlansInfoById(PlansHandler::PLAN_TYPE_PLAN, $plan_id);
    }

    public function getAddonsAttribute(): array
    {
        $addons_ids_list = $this->plans_info['addons'] ?? null;

        if (! is_array($addons_ids_list)) {
            return [];
        }

        return PlansHandler::getPlansInfoById(PlansHandler::PLAN_TYPE_ADDON, $addons_ids_list);
    }

    public function getIsTrialAttribute(): bool
    {
        return ! $this->isPlanAssigned() && isset($this->trial_expires_at);
    }

    public function getIsPaidAttribute(): bool
    {
        return is_string($this->subscription_id) && strlen($this->subscription_id);
    }

    public function getTrialDaysLeftAttribute(): int
    {
        $now = Carbon::now();
        if (! isset($this->trial_expires_at) || ($this->trial_expires_at <= $now)) {
            return 0;
        }
        return $this->trial_expires_at->diff($now)->days + 1;
    }

    public function deactivateTrial(?bool $save = true): void
    {
        $this->trial_expires_at = null;
        $this->active = false;

        if ($save) {
            $this->save();
        }
    }

    public function deactivate(?bool $save = true): void
    {
        $this->trial_expires_at = null;
        $this->active = false;
        $this->subscription_id = null;
        $this->clearPlansInfo();

        if ($save) {
            $this->save();
        }
    }

    public function isPlanAssigned(): bool
    {
        return is_array($this->plans_info) &&
               array_key_exists(self::PLAN_FIELD, $this->plans_info) &&
               is_string($this->plans_info[self::PLAN_FIELD]) &&
               strlen($this->plans_info[self::PLAN_FIELD]);
    }

    public function isAddonsAssigned(): bool
    {
        return is_array($this->plans_info) &&
               array_key_exists(self::ADDONS_FIELD, $this->plans_info) &&
               is_array($this->plans_info[self::ADDONS_FIELD]) &&
               count($this->plans_info[self::ADDONS_FIELD]);
    }

    public function isAuthUserWiki(): bool
    {
        return $this->user_id === request()->user()->id;
    }

    public function isCompleted(): bool
    {
        return (bool) $this->completed;
    }

    public function isBecomeCompleted(): bool
    {
        return ! $this->getOriginal('completed') && $this->completed;
    }

    public function isActive(): bool
    {
        return (bool) $this->active;
    }

    public function changePlansInfo(string $plan, array $addons = []): void
    {
        $plans_info = $this->plans_info;
        $plans_info[self::PLAN_FIELD] = $plan;
        $plans_info[self::ADDONS_FIELD] = $addons;

        $this->plans_info = $plans_info;
    }

    public function clearPlansInfo(): void
    {
        $this->plans_info = null;
    }

    public function addAddonsToWiki(array $addons_to_set = []): void
    {
        $plans_info = $this->plans_info;
        $addons = $plans_info['addons'] ?? [];
        $addons = array_merge($addons, $addons_to_set);
        $addons = array_unique($addons);
        $plans_info['addons'] = $addons;
        $this->plans_info = $plans_info;
    }

    public function removeAddonsFromWiki(array $addons_to_remove = []): void
    {
        $plans_info = $this->plans_info;
        $addons = $plans_info['addons'] ?? [];
        $addons = array_diff($addons, $addons_to_remove);
        $addons = array_values($addons);
        $plans_info['addons'] = $addons;
        $this->plans_info = $plans_info;
    }

    public function getPlansListForSubscription(): array
    {
        $result = [];
        do {
            $plans_info = $this->plans_info;

            if (! $this->isPlanAssigned()) {
                break;
            }

            $result[] = $plans_info[self::PLAN_FIELD];

            if (! $this->isAddonsAssigned()) {
                break;
            }

            foreach ($plans_info[self::ADDONS_FIELD] as $addon_id) {
                $result[] = $addon_id;
            }
        } while (false);

        return $result;
    }

    public function getSubscription(): ?Subscription
    {
        $user = $this->user;
        return $user->subscriptions()->where('stripe_id', $this->subscription_id)->first();
    }

    // relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function getDomainName(): string
    {
        return $this->subdomain . '.pro.wiki';
    }

    /**
     * @return string Payment-agnostic plan ID, ie "maker"
     */
    public function getPlanId(): string
    {
        return $this->plans_info[self::PLAN_FIELD] ?? ($this->is_trial ? Plan::FREE_TRAIL : static::NO_PLAN_WIKI);
    }

    /**
     * @return array<int, string> Payment-agnostic addon IDs, ie "advanced-scripting" "structured-data"
     */
    public function getAddonIds(): array
    {
        return $this->plans_info[self::ADDONS_FIELD] ?? [];
    }

}
