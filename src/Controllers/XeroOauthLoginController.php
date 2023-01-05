<?php

namespace Firesphere\Xero\Controllers;

use Firesphere\Xero\Models\XeroDebugLog;
use Firesphere\Xero\Services\ProviderFactory;
use Firesphere\Xero\Traits\XeroTrait;
use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Psr\Log\LoggerInterface;
use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Path;
use SilverStripe\ORM\ValidationException;
use XeroAPI\XeroPHP\Api\IdentityApi;
use XeroAPI\XeroPHP\ApiException;
use XeroAPI\XeroPHP\Configuration;

/**
 * Class \Firesphere\Xero\Controllers\XeroOauthLoginController
 *
 */
class XeroOauthLoginController extends Controller
{
    use XeroTrait;

    /**
     * @var string[]
     */
    private static $allowed_actions = [
        'login',
        'handle'
    ];

    /**
     * @var GenericProvider
     */
    protected $provider;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct()
    {
        parent::__construct();
        $provider = ProviderFactory::create();
        $this->getCredentials();

        $this->provider = $provider;
        $this->logger = Injector::inst()->get(LoggerInterface::class);
    }

    /**
     * @return void
     */
    public function index()
    {
        $request = $this->getRequest()->params();

        if (!$request['Action']) {
            $this->redirect($this->Link('login'));
        }
    }

    /**
     * @param null $action
     * @return string
     */
    public function Link($action = null): string
    {
        return Path::join('xero-oauth', $action);
    }

    /**
     * @return void
     */
    public function login()
    {
        $scopes = [];
        foreach ($this->tenant->TenantScopes() as $scope) {
            $scopes[] = $scope->getScopeForAuth();
        }
        $session = $this->getRequest()->getSession();
        $state = uniqid('', true);
        $session->set('oauth2state', $state);
        $session->save($this->getRequest());
        $this->provider->authorize([
            'scope' => implode(' ', $scopes),
            'state' => $state,
        ]);
    }

    /**
     * @param HTTPRequest $request
     * @return self|ContentController
     * @throws HTTPResponse_Exception
     * @throws IdentityProviderException
     * @throws ValidationException|ApiException
     */
    public function handle(HTTPRequest $request)
    {
        $code = $request->getVar('code');

        // security checks Skip for now
        if (!$this->checkSecurity($request, $code)) {
            return $this;
        }
        $this->token = $this->provider->getAccessToken('authorization_code', [
            'code'  => $code,
            'scope' => 'offline_access',
        ]);

        $credentials = $this->tenant->Credentials();
        $credentials->LongTermToken = $this->token->getRefreshToken();
        $credentials->write();

        $this->getTenantData();

        return $this->successPage();
    }

    /**
     * @return $this|ContentController
     * @throws \Exception
     */
    public function successPage()
    {
        if (!class_exists('\\Page')) {
            return $this;
        }
        $holderPage = Injector::inst()->create(\Page::class);
        $holderPage->Title = 'Xero authentication successful';
        /** @skipUpgrade */
        $holderPage->URLSegment = 'xero-oauth-success';
        // Disable ID-based caching  of the log-in page by making it a random number
        $holderPage->ID = -1 * random_int(1, 10000000);

        $controller = ModelAsController::controller_for($holderPage);
        $controller->setRequest($this->getRequest());
        $controller->doInit();

        return $controller;
    }

    /**
     * @param HTTPRequest $request
     * @param string $code
     * @return bool
     * @throws HTTPResponse_Exception
     */
    private function checkSecurity($request, $code): bool
    {
        // get code and state
        $state = $request->getVar('state');

        // log info
        $sessionState = $request->getSession()->get('oauth2state') ?? null;

        if (!$code) {
            XeroDebugLog::logXero([
                'Message'  => 'Invalid request, no return code received',
                'XeroUUID' => '',
                'Level'    => 'ERROR'
            ]);
            $this->getRequest()->getSession()->clear('oauth2state');
            $this->httpError(400, 'Invalid request. No code.');

            return false;
        }
        if (!$state || hash_equals($sessionState, $state)) {
            XeroDebugLog::logXero([
                'Message'  => 'State value is not the expected value',
                'XeroUUID' => '',
                'Level'    => 'ERROR'
            ]);
            $this->logger->error($state . ' is not ' . $sessionState);
            $this->getRequest()->getSession()->clear('oauth2state');
            $this->httpError(400, 'Invalid state');

            return false;
        }

        return true;
    }

    /**
     * @return void
     * @throws ValidationException
     * @throws ApiException
     */
    private function getTenantData()
    {
        $shortToken = $this->token->getToken();
        $config = Configuration::getDefaultConfiguration()->setAccessToken((string)$shortToken);
        $identityApi = new IdentityApi(
            new Client(),
            $config
        );
        $result = $identityApi->getConnections();

        $this->tenant->XeroID = $result[0]->getTenantId();
        $this->tenant->XeroName = $result[0]->getTenantName();
        $this->tenant->write();
    }
}
