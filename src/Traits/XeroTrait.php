<?php

namespace Firesphere\Xero\Traits;

use Firesphere\Xero\Models\Credentials;
use Firesphere\Xero\Models\Tenant;
use League\OAuth2\Client\Token\AccessTokenInterface;
use SilverStripe\SiteConfig\SiteConfig;

/**
 *
 */
trait XeroTrait
{
    /**
     * @var Tenant
     */
    protected $tenant;

    /**
     * @var Credentials
     */
    protected $credentials;

    /**
     * @var AccessTokenInterface
     */
    private $token;


    /**
     * @return Tenant|\SilverStripe\ORM\DataObject|null
     */
    public function getTenant()
    {
        $this->tenant = SiteConfig::current_site_config()->DefaultTenant();
        if (!$this->tenant->exists()) {
            $this->tenant = Tenant::get()->first();
        }

        return $this->tenant;
    }

    /**
     * @return Credentials
     */
    public function getCredentials(): Credentials
    {
        if (!$this->credentials) {
            $this->credentials = $this->getTenant()->Credentials();
        }

        return $this->credentials;
    }

    /**
     * @return Credentials
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function refreshCredentials(): Credentials
    {
        $credentials = $this->getCredentials();

        $this->token = $this->getXero()->getAccessToken('refresh_token', [
            'refresh_token' => $credentials->LongTermToken
        ]);
        // Store the newly received long term token
        $credentials->LongTermToken = $this->token->getRefreshToken();
        $credentials->write();
        $this->credentials = $credentials;
        return $credentials;
    }
}
