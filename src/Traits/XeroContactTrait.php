<?php

namespace Firesphere\Xero\Traits;

use Exception;
use Firesphere\Xero\Models\Tenant;
use Firesphere\Xero\Models\XeroDebugLog;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Models\Accounting\Contact;
use XeroAPI\XeroPHP\Models\Accounting\Contacts;

trait XeroContactTrait
{
    /**
     * @param DataObject $order
     * @param Contact $client To avoid ambiguation between Xero Contact and local generated, it's named "$client"
     * @param Tenant $tenant
     * @param AccountingApi $accountingAPI
     * @return false|void
     * @throws ValidationException
     */
    public function getXeroContact($order, $client, $tenant, $accountingAPI)
    {
        $contact = $this->getContactFromXero($order, $client, $tenant, $accountingAPI);
        /** @var DataObject $localClient */
        $localClient = $order->Client();

        if ($contact !== false) {
            $localClient->XeroContactID = $contact->getContactId();
            $localClient->write();
            return $contact;
        }

        XeroDebugLog::logXero([
            'Message'  => sprintf('Creating contact %d in Xero', $localClient->ID),
            'Level'    => 'INFO',
            'ClientID' => $localClient->ID,
            'OrderID'  => $order->ID,
        ]);

        $contact = $client;
        try {
            $contacts = new Contacts();
            $contacts->setContacts([$contact]);
            $result = $accountingAPI->createContacts($tenant->XeroID, $contacts);
        } catch (Exception $e) {
            XeroDebugLog::logXero([
                'Message'  => $e->getMessage(),
                'Level'    => 'ERROR',
                'OrderID'  => $order->ID,
                'ClientID' => $localClient->ID
            ]);

            return false;
        }

        /** @var Contact $result */
        $result = $result[0];
        if ($result->getHasValidationErrors()) {
            XeroDebugLog::logXero([
                'Message'  => 'Could not save Xero contact:' . $result->getValidationErrors()[0]->getMessage(),
                'Level'    => 'ERROR',
                'OrderID'  => $order->ID,
                'ClientID' => $localClient->ID
            ]);

            return false;
        }

        if ($localClient->exists()) {
            $id = $result->getContactId();
            $localClient->XeroContactID = $id;
            $localClient->write();
        } else {
            XeroDebugLog::logXero([
                'Message' => 'Could not save Xero contact. Local profile does not seem to exist.',
                'Level'   => 'ERROR',
                'OrderID' => $order->ID,
            ]);

            return false;
        }

        return $result;
    }

    /**
     * @param DataObject $order
     * @param Contact $client
     * @param Tenant $tenant
     * @param AccountingApi $accountingAPI
     * @return false|mixed
     */
    protected function getContactFromXero($order, $client, $tenant, $accountingAPI)
    {
        $contact = false;
        $localClient = $order->Client();
        if ($client->getContactId()) {
            try {
                $contact = $accountingAPI->getContact($tenant->XeroID, $client->getContactId());
            } catch (Exception $e) {
                XeroDebugLog::logXero([
                    'Message'  => 'Contact with Xero ID not found in Xero',
                    'ClientID' => $localClient->ID,
                    'OrderID'  => $order->ID,
                    'Level'    => 'INFO',
                ]);
            }
        }
        if ($contact === false && $client->getPhones()) {
            try {
                $contact = $accountingAPI->getContactByContactNumber($tenant->XeroID, $client->getPhones()[0]);
            } catch (Exception $e) {
                XeroDebugLog::logXero([
                    'Message' => sprintf('No contact found for %d by phone', $localClient->ID),
                    'Level'   => 'INFO',
                    'OrderID' => $order->ID,
                    'ClientID' => $localClient->ID
                ]);
            }
        }

        // We can return false here safely, so just return whatever we got
        return $contact;
    }
}
