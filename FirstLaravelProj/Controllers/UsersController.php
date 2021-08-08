<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Libraries\Payments\Invoices\InvoicesHandler;
use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class UsersController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::all();

        return response()->json($users);
    }

    public function show(int $id): JsonResponse
    {
        return User::findOrFail($id);
    }

    public function getAuthUserInfo(): JsonResponse
    {
        $user = request()->user();

        if ($user->isOnRegistrationFlow()) {
            $user->loadMissing('wikis');
        }

        return response()->json($user);
    }

    public function getAuthUserSetupIntent(): JsonResponse
    {
        $user = request()->user();
        $user->checkStripeCustomer();
        $setup_intent = $user->createSetupIntent();

        return response()->json(['setup_intent' => $setup_intent['client_secret'] ?? null]);
    }

    public function getAuthUserPaymentMethods(): JsonResponse
    {
        $user = request()->user();
        $user->checkStripeCustomer();
        $payment_methods = $user->paymentMethods();
        $default_payment_method = $user->defaultPaymentMethod();

        return response()->json([
            'payment_methods' => $payment_methods,
            'default_payment_method' => $default_payment_method,
        ]);
    }

    public function addAuthUserPaymentMethod(): JsonResponse
    {
        $validated_data = request()->validate([
            'payment_method' => 'required|string',
        ]);

        $payment_method = $validated_data['payment_method'];

        $user = request()->user();
        $user->checkStripeCustomer();
        $user->addPaymentMethod($payment_method);
        $user->updateDefaultPaymentMethod($payment_method);

        $payment_methods = $user->paymentMethods();
        $default_payment_method = $user->defaultPaymentMethod();

        return response()->json([
            'payment_methods' => $payment_methods,
            'default_payment_method' => $default_payment_method,
        ]);
    }

    public function deleteAuthUserPaymentMethod(string $id): JsonResponse
    {
        $user = request()->user();
        $user->checkStripeCustomer();
        $payment_methods = $user->paymentMethods();
        $default_payment_method = $user->defaultPaymentMethod();

        foreach ($payment_methods as $method) {
            if ($method->id === $id) {
                $method->delete();

                break;
            }
        }

        $payment_methods = $user->paymentMethods();
        $default_payment_method = $user->defaultPaymentMethod();

        return response()->json([
            'payment_methods' => $payment_methods,
            'default_payment_method' => $default_payment_method,
        ]);
    }

    public function getAuthUserInvoices(): JsonResponse
    {
        $page = request()->query('page') ?? 1;
        $per_page = request()->query('per_page') ?? 10;

        $user = request()->user();
        $invoices = $user->invoicesIncludingPending();

        $current_items = array_slice($invoices->toArray(), $per_page * ($page - 1), $per_page);
        $current_items = InvoicesHandler::prepareInvoiceListForShow($current_items, $user);
        $paginated_invoices = new LengthAwarePaginator($current_items, $invoices->count(), $per_page, $page);

        return response()->json($paginated_invoices);
    }
}
