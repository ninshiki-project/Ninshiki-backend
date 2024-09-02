<?php

namespace App\Observers;

use App\Models\User;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;

class UserObserver
{
    /**
     * Handle the "created" event.
     *
     * @param  User  $user
     *
     * @throws ExceptionInterface
     */
    public function created(User $user): void
    {
        /**
         * Types of wallet
         *  1. Ninshiki Wallet - Main Wallet. It will only have a fund once being recognized by colleague
         *  2. Spend Wallet - Will be used when recognizing other colleague, and the fund amount will be reset every end day of the month
         *  3. Currency Wallet - Will be used once the user exchange his earned coins into a real currency
         */
        $defaultWallet = $user->createWallet([
            'name' => 'Ninshiki Wallet',
            'slug' => 'ninshiki-wallet',
        ]);

        $spendWallet = $user->createWallet([
            'name' => 'Spend Wallet',
            'slug' => 'spend-wallet',
        ]);

        $spendWallet->deposit(config('ninshiki.fund.normal'), [
            'title' => 'Spend Wallet',
            'description' => 'Added funds to your account.',
        ]);

        $currencyWallet = $user->createWallet([
            'name' => 'Currency Wallet',
            'slug' => 'currency-wallet',
        ]);
    }
}