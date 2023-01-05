<?php

namespace Firesphere\Xero\Tasks;

use Firesphere\Xero\Models\XeroDebugLog;
use Firesphere\Xero\Services\ProviderFactory;
use Firesphere\Xero\Services\XeroConfig;
use Firesphere\Xero\Traits\XeroContactTrait;
use Firesphere\Xero\Traits\XeroOrderTrait;
use Firesphere\Xero\Traits\XeroShadowTrait;
use Firesphere\Xero\Traits\XeroCredentialsTrait;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\ValidationException;
use XeroAPI\XeroPHP\Api\AccountingAPI;
use XeroAPI\XeroPHP\Configuration;
use XeroAPI\XeroPHP\Models\Accounting\Contact;

/**
 *
 */
class XeroInvoiceExportTask extends BuildTask
{
    use XeroCredentialsTrait;
    use XeroContactTrait;
    use XeroOrderTrait;
    use XeroShadowTrait;

    /**
     * @var string
     */
    private static $segment = 'xero-orders';

    /**
     * @var GenericProvider
     */
    protected $xero;

    /**
     * @var bool|mixed
     */
    protected $shadowCopy = false;

    public function __construct()
    {
        parent::__construct();
        $this->shadowCopy = XeroConfig::getShadowCopy();
        // The accounting API is a weird one that's somehow not included in things
        require_once Director::getAbsFile('vendor/xeroapi/xero-php-oauth2/lib/Api/AccountingApi.php');

    }

    /**
     * @param GenericProvider $application
     * @return $this
     */
    public function setXero(GenericProvider $application): self
    {
        $this->xero = $application;

        return $this;
    }

    /**
     * @return GenericProvider
     */
    public function getXero(): GenericProvider
    {
        if (!$this->xero) {
            $this->xero = ProviderFactory::create();
        }

        return $this->xero;
    }

    /**
     * @param HTTPRequest $request
     * @return void
     * @throws IdentityProviderException
     * @throws ValidationException
     */
    public function run($request)
    {
        XeroDebugLog::logXero(['Message' => 'Starting new run', 'Level' => 'INFO']);

        $this->refreshCredentials();
        XeroDebugLog::logXero(['Message' => 'New tokens acquired from Xero', 'Level' => 'INFO']);

        $config = Configuration::getDefaultConfiguration()->setAccessToken($this->token->getToken());
        $accountingAPI = new AccountingAPI($this->getXero()->getHttpClient(), $config);

        $orders = XeroConfig::getOrders();

        foreach ($orders as $order) {
            if ($this->token->hasExpired()) {
                $this->refreshCredentials();
            }
            $client = $order->getContactForXero();
            $contact = $this->getXeroContact($order, $client, $this->tenant, $accountingAPI);
            if (!$contact instanceof Contact) {
                continue;
            }
            $invoice = $this->createXeroInvoice($contact, $order, $this->tenant, $accountingAPI);

            if ($this->shadowCopy) {
                $copy = $this->copyContact($contact, $this->tenant->ID);
                $original = $order->Client();
                $original->CopyContactID = $copy;
                $original->write();
                // @todo: Do the same for invoices
            }
        }
    }
}
