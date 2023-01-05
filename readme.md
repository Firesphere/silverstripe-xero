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

use \XeroAPI\XeroPHP\Models\Accounting\Phone;
use \XeroAPI\XeroPHP\Models\Accounting\Address;
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
        $addresses = [];
        // Repeat new address as needed
        $address = new Address();
        $address->setAddressLine1($this->DeliveryAddress);
        $address->setAddressLine2($this->Suburb);
        $address->setCity($this->City);
        $address->setPostalCode($this->PostCode);
        $addresses[] = $address;
        $contact->setAddresses($address);
        return $contact;
    }
}
```

For orders, your Order object also needs the following methods:

- `getXeroOrderNumber` to return the Order Number as it should be displayed in Xero
- `getXeroDueDate` the date on which the invoice is due, formatted as YYYY-mm-dd (e.g. 2023-01-05)
- `getXeroLineItems` The items to put in to this invoice

Example LineItems:

```php
    public function getXeroLineItems($order)
    {
        $items = [];
        foreach ($order->Items as $lineItem) {
            $item = new XeroAPI\XeroPHP\Models\Accounting\LineItem();
            $item->setDescription($lineItem->ProductName);
            $item->setQuantity($lineItem->Quantity);
            $item->setUnitAmount($lineItem->Price);
            $item->setLineAmount($lineItem->Quantity * $lineItem->Price);
            $item->setAccountCode($lineItem->AccountCode);
            $item->setTaxType(TaxType::OUTPUT2);
            $items[] = $item;
        }
        return $items;
    }
```

### Why create your own contact and order etc.?

Because everyone might implement things differently, thus I can't enforce a standard.



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

