<?php

namespace Firesphere\Xero\Traits;

use Exception;
use Firesphere\Xero\Models\Credentials;
use Firesphere\Xero\Models\Tenant;
use Firesphere\Xero\Models\XeroDebugLog;
use Firesphere\Xero\Services\XeroConfig;
use XeroAPI\XeroPHP\Api\AccountingAPI;
use XeroAPI\XeroPHP\Models\Accounting\Contact;
use XeroAPI\XeroPHP\Models\Accounting\Invoice;
use XeroAPI\XeroPHP\Models\Accounting\Invoices;
use XeroAPI\XeroPHP\Models\Accounting\LineAmountTypes;
use XeroAPI\XeroPHP\Models\Accounting\LineItem;
use XeroAPI\XeroPHP\Models\Accounting\TaxType;

trait XeroOrderTrait
{

    /**
     * @param Contact $contact
     * @param DataObject $order
     * @param Tenant $tenant
     * @param AccountingAPI $accountingAPI
     * @return false|Invoice
     */
    public function createXeroInvoice($contact, $order, $tenant, $accountingAPI)
    {
        $invoice = new Invoice();
        $invoice->setContact($contact);
        $invoice->setType(Invoice::TYPE_ACCREC);
        $invoice->setCurrencyCode(XeroConfig::getCurrency());
        $invoice->setStatus(Invoice::STATUS_AUTHORISED);
        $invoice->setDate($order->Created);
        $invoice->setInvoiceNumber($order->getXeroOrderNumber());
        $invoice->setDueDate($order->getXeroDueDate());
        $invoice->setLineAmountTypes(LineAmountTypes::INCLUSIVE);

        $items = $order->getXeroLineItems();
        $invoice->setLineItems($items);

        try {
            /** @var Invoice $result */
            $result = $accountingAPI->createInvoices($tenant->XeroID, new Invoices([$invoice]));
        } catch (Exception $e) {
            XeroDebugLog::logXero([
                'Message' => 'Could not save Xero invoice: ' . $e->getMessage(),
                'Level'   => 'ERROR',
                'OrderID' => $order->ID,
            ]);

            return false;
        }

        $order->XeroInvoiceID = $result->getInvoiceID();

        return $invoice;
    }

    /**
     * @param $orderItem
     * @return LineItem
     */
    public function createItem($orderItem)
    {
        $item = new LineItem();
        $item->setDescription($orderItem->ProductName);
        $item->setQuantity($orderItem->Quantity);
        $item->setUnitAmount($orderItem->Price);
        $item->setLineAmount($orderItem->ExtendedPrice());
        $item->setAccountCode($orderItem->AccountCode);
        $item->setTaxType(TaxType::OUTPUT2);

        return $item;
    }
}