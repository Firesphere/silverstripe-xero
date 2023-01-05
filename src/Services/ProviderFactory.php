<?php

namespace Firesphere\Xero\Services;

use League\OAuth2\Client\Provider\GenericProvider;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;

/**
 *
 */
class ProviderFactory
{
    /**
     * @var GenericProvider
     */
    private static $provider;

    /**
     * @return GenericProvider
     */
    public static function create(): GenericProvider
    {
        if (!self::$provider) {
            self::build();
        }

        return self::$provider;
    }

    /**
     * @return void
     */
    private static function build()
    {
        $baseURL = Director::absoluteBaseURL();

        self::$provider = new GenericProvider([
            'clientId'                => Environment::getEnv('XERO_CLIENT_ID'),
            'clientSecret'            => Environment::getEnv('XERO_SECRET'),
            'redirectUri'             => $baseURL . 'xero-oauth/handle/',
            'urlAuthorize'            => 'https://login.xero.com/identity/connect/authorize',
            'urlAccessToken'          => 'https://identity.xero.com/connect/token',
            'urlResourceOwnerDetails' => 'https://api.xero.com/api.xro/2.0/Organisation',
        ]);
    }

    /**
     * @return GenericProvider
     */
    public static function getProvider()
    {
        return self::create();
    }

    /**
     * @param GenericProvider $provider
     */
    public static function setProvider($provider): void
    {
        self::$provider = $provider;
    }
}
