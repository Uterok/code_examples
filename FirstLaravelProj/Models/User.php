<?php

namespace App\Models\Users;

use App\Libraries\Taxes\TaxesHandler;
use App\Models\Payments\Transaction;
use App\Models\Traits\ModelExtended;
use App\Models\Wikis\Wiki;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\Billable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens, Billable, ModelExtended;

    public const LOCK_TIMEOUT_DEFAULT = 5;

    public const REGISTRATION_FLOW_TRIAL = 'trial';
    public const REGISTRATION_FLOW_PLAN = 'plan';

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hash_password_on_change = true;

    //custom methods
    public function disableHashPasswordOnChange(): void
    {
        $this->hash_password_on_change = false;
    }

    public static function checkRegistrationFlow(?string $flow): bool
    {
        return $flow !== static::REGISTRATION_FLOW_TRIAL ||
               $flow !== static::REGISTRATION_FLOW_PLAN;
    }

    public function assignRegistrationFlow(?string $flow, ?bool $save = false): void
    {
        if (!static::checkRegistrationFlow($flow)) {
            return;
        }

        $this->registration_flow = $flow;

        if ($save) {
            $this->save();
        }
    }

    public function resetRegistrationFlow(?bool $save = false): void
    {
        $this->registration_flow = null;

        if ($save) {
            $this->save();
        }
    }

    public function checkStripeCustomer(): mixed
    {
        $lock = Cache::lock('checkStripeCustomer', self::LOCK_TIMEOUT_DEFAULT);

        if ($lock->get()) {
            if (is_null($this->stripe_id)) {
                return $this->createAsStripeCustomer();
            }

            $lock->release();
        }

        return null;
    }

    /**
     * Find user using social provider's id
     *
     * @param string $provider Provider name as requested from oauth e.g. facebook
     * @param string $id User id of social provider
     *
     * @return User
     */
    public static function findForPassportSocialite(string $provider, string $provider_user_id, string $email): ?static
    {
        return static::where('email', $email)
            ->orWhereHas('social', function ($q) use ($provider_user_id, $provider) {
                $q->providerAccount($provider, $provider_user_id);
            })->first();
    }

    public function hasSocialLinked(string $service): bool
    {
        return (bool) $this->social->where('service', $service)->count();
    }

    public static function findByStripeId(string $user_stripe_id): ?static
    {
        return is_string($user_stripe_id) ?
            static::where('stripe_id', $user_stripe_id)
                ->withTrashed()
                ->first() :
            null;
    }

    public static function hashUserPassword(string $user_password): string
    {
        return bcrypt($user_password);
    }

    public function hashRawPassword(): void
    {
        $this->password = static::hashUserPassword($this->password);
    }

    public function isOnRegistrationFlow(): bool
    {
        return isset($this->registration_flow);
    }

    /**
     * The tax rates that should apply to the customer's subscriptions.
     *
     * @return array
     */
    public function taxRates(): array
    {
        $tax_rates = TaxesHandler::getTaxRatesForSubscription();

        return $tax_rates;
    }

    // relations
    public function wikis(): HasMany
    {
        return $this->hasMany(Wiki::class, 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function social(): HasMany
    {
        return $this->hasMany(SocialAccount::class, 'user_id', 'id');
    }
}
