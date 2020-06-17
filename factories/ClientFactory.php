<?php

/**
 * Register a file manager for this factory
 *
 * @var \Database\Support\FileManager $fileManager
 */

use App\Clients\Client;
use App\Clients\ClientStatus;
use App\Clients\LeadSource;
use App\Clients\MarketSegment;
use App\Finances\BankAccount;
use App\Users\User;
use Database\Support\FileManager;
use Faker\Generator as Faker;

$fileManager = app()->make(FileManager::class, [
    'destinationDirectory' => public_path(),
]);

$factory->define(Client::class, function (Faker $faker) {
    return [
        'status_id'         => function () use ($faker) {
            $name = $faker->randomElement(config('client.statuses'))['name'];
            return ClientStatus::where('name', $name)->firstOrFail()->id;
        },
        'lead_source_id'    => factory(LeadSource::class)->create()->id,
        'market_segment_id' => factory(MarketSegment::class)->create()->id,
        'bank_account_id'   => function () {
            return factory(BankAccount::class)->create()->id;
        },
        'name'              => $faker->company,
        'address_1'         => $faker->streetAddress,
        'address_2'         => $faker->boolean ? '' : $faker->streetAddress,
        'address_3'         => $faker->boolean ? '' : $faker->streetAddress,
        'town'              => $faker->city,
        'postcode'          => $faker->postcode,
        'phone_number'      => $faker->phoneNumber,
        'fax_number'        => $faker->phoneNumber,
        'company_id'        => $faker->boolean ? '' : $faker->company,
        'vip_amount'        => $faker->boolean ? '' : $faker->randomDigitNotNull,
        'savings_made'      => $faker->boolean ? '' : $faker->randomDigitNotNull,
        'internal_info'     => $faker->boolean ? '' : $faker->text(),
        'web'               => 'http://' . $faker->unique()->domainName,
        'manager_id'        => function () {
            return factory(User::class)->states('role:senior-management')->create()->id;
        },
        'email' => function () use ($faker) {
            $validateUniqueEmail = function ($value) {
                return app()->make(Client::class)->where('email', '=', $value)->first() === null;
            };

            return $faker->valid($validateUniqueEmail)->safeEmail;
        },
        'logo' => resource_path('images/photograph-woman.jpg'),
    ];
});

$factory->state(Client::class, 'client:status:live', function (Faker $faker) {
    return [
        'status_id' => config('client.statuses.live.id'),
    ];
});

$factory->afterMaking(Client::class, function ($client) use ($fileManager) {
    $client->logo = config('client.images') . $fileManager->addWithRandomName($client->logo);
});

$factory->afterCreating(Client::class, function ($client) use ($fileManager) {
    $fileManager->save(basename($client->logo), config('client.images'));
});
