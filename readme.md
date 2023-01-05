# Silverstripe Xero integration

## Configuration

In `.env`

```dotenv
XERO_CLIENT_ID='12345678987654321ABC'
XERO_SECRET='123456789MYXEROSECRETHERE987654321'
```

In your `xero.yml` (or however you call it)

```yaml
Firesphere\Xero\Services\XeroConfig:
  OrderFilter:
    Status:
      - 'PENDING'
      - 'PAID'
      - 'FULFILLED'
  Currency: 'NZD'
```

The OrderFilter can roughly be written as a Silverstripe query, e.g. if you want to _exclude_ on a field, use
```yaml
OrderFilter:
  - 'Name:not':
      - ExcludedUserName
```

Apply `Firesphere\Xero\Extensions\OrderExtension` to your Order class.
Apply `Firesphere\Xero\Extensions\ClientExtension` to your Client class.
The "Client class" is the class which contains the client details of the person that created the order.

# Creating a new Invoice or Order

All orders are fetched on running the Xero Invoice Export Task, according to the configuration above.


In your Order class, you _*must*_ define a `getContactForXero()` method, which _*must*_ return
a Xero contact.

An example of a Xero contact:

```php
class Order extends DataObject
{
    public function getContactForXero()
    {
        $contact = new \XeroAPI\XeroPHP\Models\Accounting\Contact();
        /** @var Member $client */
        $client = $this->Client();
        $contact->setName($client->Surname)
            ->setEmailAddress($client->Email)
            ->setContactId($client->XeroContactID);
        $phones = [];
        if ($client->phone) {
            $p = new Phone();
            $p->setPhoneType(Phone::PHONE_TYPE__DEFAULT);
            $p->setPhoneNumber($client->phone);
            $phones[] = $p;
        }
        if ($client->mobile) {
            $p = new Phone();
            $p->setPhoneType(Phone::PHONE_TYPE_MOBILE);
            $p->setPhoneNumber($client->mobile);
            $phones[] = $p;
        }
        $contact->setPhones($phones);
        $hasAddress = ($this->DeliveryAddress || ($client->exists() && $client->PostalAddressStreet));
        if ($hasAddress) {
            $address = new Address();
            $address->setAddressLine1(
                $client->exists() ? $client->PostalAddressStreet : $this->DeliveryAddress
            );
            $address->setAddressLine2(
                $client->exists() ? $client->PostalAddressSuburb : $this->Suburb
            );
            $address->setCity(
                $client->exists() ? $client->PostalAddressCity : $this->City
            );
            $address->setPostalCode(
                $client->exists() ? $client->PostalAddressPostCode : $this->PostCode
            );
            $contact->setAddresses([$address]);
        }
        return $contact;
    }
}
```

For orders, your Order object also needs the following methods:

- `getXeroOrderNumber` to return the Order Number as it should be displayed in Xero
- `getXeroDueDate` the date on which the invoice is due, formatted as YYYY-mm-dd (e.g. 2023-01-05)
- `getLineItems` The items to put in to this invoice



## Logging

All actions are logged to a XeroDebugLog.

If you want to run your own logging, you can extend, for example to log to another logger:

```php
class MyLogExtension extends DataExtension
{

    public function updateLog()
    {
        $logger = Injector::inst()->get(LoggerInterface::class);
        $logger->log($this->owner->Message, $this->owner->Level)
    }
}
```

