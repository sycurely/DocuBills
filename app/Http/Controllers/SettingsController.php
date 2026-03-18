<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Show the settings page.
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Show payment methods settings page.
     */
    public function paymentMethods()
    {
        $this->ensurePaymentMethodsAccess();

        return view('settings.payment-methods');
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            SettingService::set($key, $value);
        }

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    /**
     * Update payment methods settings.
     */
    public function updatePaymentMethods(Request $request)
    {
        $this->ensurePaymentMethodsAccess();

        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.stripe_publishable_key' => 'nullable|string|max:255',
            'settings.stripe_secret_key' => 'nullable|string|max:255',
            'settings.stripe_webhook_secret' => 'nullable|string|max:255',
            'settings.test_mode' => 'nullable|in:0,1',
            'settings.bank_account_name' => 'nullable|string|max:255',
            'settings.bank_name' => 'nullable|string|max:255',
            'settings.bank_account_number' => 'nullable|string|max:255',
            'settings.bank_iban' => 'nullable|string|max:255',
            'settings.bank_swift' => 'nullable|string|max:255',
            'settings.bank_routing' => 'nullable|string|max:255',
            'settings.bank_additional_info' => 'nullable|string|max:1000',
        ]);

        $settings = $validated['settings'];

        $cardKeys = [
            'stripe_publishable_key',
            'stripe_secret_key',
            'stripe_webhook_secret',
            'test_mode',
        ];

        $bankKeys = [
            'bank_account_name',
            'bank_name',
            'bank_account_number',
            'bank_iban',
            'bank_swift',
            'bank_routing',
            'bank_additional_info',
        ];

        if (!has_permission('manage_card_payments')) {
            foreach ($cardKeys as $key) {
                unset($settings[$key]);
            }
        }

        if (!has_permission('manage_bank_details')) {
            foreach ($bankKeys as $key) {
                unset($settings[$key]);
            }
        }

        foreach ($settings as $key => $value) {
            SettingService::set($key, $value);
        }

        return redirect()->route('settings.payment-methods')->with('success', 'Payment methods updated successfully.');
    }

    private function ensurePaymentMethodsAccess(): void
    {
        if (
            !has_permission('manage_payment_methods') &&
            !has_permission('update_basic_settings') &&
            !has_permission('manage_card_payments') &&
            !has_permission('manage_bank_details')
        ) {
            abort(403, 'Unauthorized action.');
        }
    }
}
