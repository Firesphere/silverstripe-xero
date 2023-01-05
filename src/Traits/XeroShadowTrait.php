<?php

namespace Firesphere\Xero\Traits;

use Firesphere\Xero\Models\Xero\Address;
use Firesphere\Xero\Models\Xero\Contact;
use Firesphere\Xero\Models\Xero\ContactPerson;
use Firesphere\Xero\Models\Xero\Invoice;
use Firesphere\Xero\Models\Xero\Phone;
use SilverStripe\ORM\DataObject;
use XeroAPI\XeroPHP\Models\Accounting\Contact as XeroContact;

trait XeroShadowTrait
{
    private function doCopy($original, &$copy)
    {
        $schema = DataObject::getSchema();
        $dbFields = $schema->databaseFields($copy->ClassName);
        foreach ($dbFields as $fieldName => $fieldType) {
            $method = sprintf('get%s', $fieldName);
            if (method_exists($original, $method)) {
                $copy->$fieldName = $original->$method();
            }
            $copy->write();
        }
    }

    /**
     * Find or create a local shadow copy to prepare for
     * importing all the data
     * @param DataObject $class
     * @param array $filter
     * @param array $create
     * @return DataObject|null
     */
    private function findOrCreate($class, $filter, $create)
    {
        $copy = $class::get()->filter($filter)->first();
        if (!$copy) {
            $copy = $class::create($create);
        }

        return $copy;
    }

    /**
     * Copy a contact, and it's related parts (addresses, persons, etc.) to a shadow
     * copy to hold locally.
     *
     * @param XeroContact $contact
     * @param int $tenantID
     * @return int
     */
    public function copyContact($contact, $tenantID)
    {
        $filter = ['ContactID' => $contact->getContactID()];
        $create = ['TenantID' => $tenantID];
        $copyContact = $this->findOrCreate(Contact::class, $filter, $create);

        $this->doCopy($contact, $copyContact);
        
        $addresses = $contact->getAddresses();
        foreach ($addresses as $address) {
            $filter = ['AddressLine1' => $address->getAddressLine1(), 'ContactID' => $copyContact->ID];
            $create = ['ContactID' => $copyContact->ID];
            $copyAddress = $this->findOrCreate(Address::class, $filter, $create);
            $this->doCopy($address, $copyAddress);
        }
        
        $phones = $contact->getPhones();
        foreach ($phones as $phone) {
            $filter = ['PhoneLine1' => $phone->getPhoneLine1(), 'ContactID' => $copyContact->ID];
            $create = ['ContactID' => $copyContact->ID];
            $copyPhone = $this->findOrCreate(Phone::class, $filter, $create);
            $this->doCopy($phone, $copyPhone);
        }
        
        $contactPersons = $contact->getContactPersons();
        foreach ($contactPersons as $person) {
            $filter = ['EmailAddress' => $person->getEmailAddress(), 'ContactID' => $copyContact->ID];
            $create = ['ContactID' => $copyContact->ID];
            $copyContactPerson = $this->findOrCreate(ContactPerson::class, $filter, $create);
            $this->doCopy($person, $copyContactPerson);
        }

        return $copyContact->ID;
    }

    /**
     * @param \XeroAPI\XeroPHP\Models\Accounting\Invoice $original
     * @param int $contactID
     * @return int
     */
    public function copyInvoice($original, $contactID)
    {
        $filter = ['InvoiceID' => $original->getInvoiceId()];
        $create = ['ContactID' => $contactID];
        $copyInvoice = $this->findOrCreate(Invoice::class, $filter, $create);
        $this->doCopy($original, $copyInvoice);

        return $copyInvoice->ID;
    }
}