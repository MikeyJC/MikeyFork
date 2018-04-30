<?php
require_once 'custom/include/Kashflow/Kashflow.php';
require_once 'custom/include/Kashflow/Kashflow_Customer_Hooks.php';
class Kashflow_Invoices {

    /**
     * @param $bean
     * @param $event
     * @param $arguments
     */
    function addOrUpdateInvoice($bean, $event, $arguments)
    {
        global $sugar_config, $app_strings;
        if ($sugar_config['kashflow_api']['send_invoices'] == 1 && $bean->from_kashflow == false &&
            (($sugar_config['kashflow_api']['send_invoices_option'] == 'modified' && $bean->date_entered != $bean->date_modified) ||
             ($sugar_config['kashflow_api']['send_invoices_option'] == 'new' && $bean->date_entered == $bean->date_modified) ||
              $sugar_config['kashflow_api']['send_invoices_option'] == 'all')) {
            $kashflow = new Kashflow();
            if(!empty($bean->billing_account_id)) {
                $accountBean = BeanFactory::getBean("Accounts", $bean->billing_account_id);
                $kashflowAccount = new Kashflow_Customer_Hooks();
                if(!empty($bean->billing_contact_id)) {
                    $contactBean = BeanFactory::getBean("Contacts", $bean->billing_contact_id);
                    if($contactBean->account_id != $accountBean->id) {
                        $contactBean->account_id = $accountBean->id;
                        $contactBean->billing_contact = true;
                        $contactBean->save();
                    }
                    $kashflowAccount->sendCustomerDetails($accountBean, $kashflow, $contactBean);
                } else {
                    if($accountBean->load_relationship('contacts')) {
                        $contactBeans = $accountBean->get_linked_beans('contacts','Contact');
                        foreach($contactBeans as $contact) {
                            if($contact->billing_contact == true) {
                                $billing_contact = $contact;
                                break;
                            }
                        }
                    }
                    if (isset($billing_contact)) $kashflowAccount->sendCustomerDetails($accountBean, $kashflow, $billing_contact);
                    else $kashflowAccount->sendCustomerDetails($accountBean, $kashflow);
                }
            }
            $response = $kashflow->getInvoice($bean->number);
            if($response->GetInvoiceResult->InvoiceDBID != 0){

                // HANDLE EXISTING LINE ITEMS
                if(!empty($response->GetInvoiceResult->Lines)) {
                    foreach($response->GetInvoiceResult->Lines as $row) {
                        if($row->enc_value->LineID != 0) {
                            if ($row->enc_value->LineID) {
                                $line_item = new AOS_Products_Quotes();
                                $line_item->retrieve_by_string_fields(array('kashflow_id' => $row->LineID));
                            }
                            if ($row->enc_value->ProductID) {
                                $product = new AOS_Products();
                                $product->retrieve_by_string_fields(array('kashflow_id' => $row->ProductID));
                            }
                            if($line_item->deleted != 1) {
                                $line = array(
                                    "LineID"           => !empty($line_item->kashflow_id) ? $line_item->kashflow_id : 0,
                                    "Quantity"         => !empty($line_item->product_qty) ? $line_item->product_qty : $row->Quantity,
                                    "Description"      => !empty($line_item->item_description) ? $line_item->item_description : $row->Description,
                                    "Rate"             => !empty($line_item->product_unit_price) ? $line_item->product_unit_price : $row->Rate,
                                    "ChargeType"       => !empty($product->nominal_code) ? (int)$product->nominal_code : $row->ChargeType,
                                    "VatAmount"        => !empty($line_item->product_vat_amt) ? $line_item->product_vat_amt : $row->VatAmount,
                                    "VatRate"          => !empty($line_item->product_vat) ? $line_item->product_vat : $row->VatRate,
                                    "Sort"             => !empty($line_item->number) ? $line_item->number : $row->Sort,
                                    "ProductID"        => !empty($product->kashflow_id) ? (int)$product->kashflow_id : $row->ProductID,
                                    "ValuesInCurrency" => 0,
                                    "ProjID"           => 0,
                                );
                                if($line->LineID != 0) $lines[] = new SoapVar($line, 0, "InvoiceLine", "KashFlow");
                            } elseif($line_item->deleted == 1) {
                                $deleteParams['LineID'] = $line_item->kashflow_id;
                                $deleteParams['InvoiceNumber'] = $bean->number;
                                $kashflow->deleteInvoiceLine($deleteParams);
                            }
                        }
                    }
                }

                $parameters['Inv'] = $response->GetInvoiceResult;
                $parameters['Inv']->InvoiceDate = $bean->invoice_date."T00:00:00";
                $parameters['Inv']->DueDate = $bean->due_date."T00:00:00";
                $parameters['Inv']->CustomerID = (int)$accountBean->kashflow_id;
                $parameters['Inv']->Paid = $bean->status == "Paid" ? 1 : 0;
                if (!empty($lines)) $parameters['Inv']->Lines = $lines; else $parameters['Inv']->Lines = array();
                $parameters['Inv']->NetAmount = !empty($bean->total_amount) ? $bean->total_amount : "0.0000";
                $parameters['Inv']->VATAmount = !empty($bean->tax_amount) ? $bean->tax_amount : "0.0000";
                $parameters['Inv']->AmountPaid = !empty($bean->amount_paid) ? $bean->amount_paid : "0.0000";
                if(!empty($bean->due_date)) $response = $kashflow->updateInvoice($parameters);
                else SugarApplication::appendErrorMessage($app_strings['LBL_FAILED_KASHFLOW_INVOICES']);
            } else {
                // NEW INVOICE - PREP LINE ITEMS
                $bean->load_relationships();
                if($bean->aos_products_quotes->getBeans()) {
                    foreach ($bean->aos_products_quotes->beans as $line_item) {
                        if ($line_item->product_id) {
                            $product = new AOS_Products();
                            $product->retrieve_by_string_fields(array('id' => $line_item->product_id));
                        }
                        $line = array(
                            "LineID"           => 0,
                            "Quantity"         => $line_item->product_qty,
                            "Description"      => !empty($line_item->item_description) ? $line_item->item_description : "",
                            "Rate"             => $line_item->product_unit_price,
                            "ChargeType"       => !empty($product->nominal_code) ? (int)$product->nominal_code : 0,
                            "VatAmount"        => !empty($line_item->product_vat_amt) ? $line_item->product_vat_amt : 0,
                            "VatRate"          => !empty($line_item->product_vat) ? $line_item->product_vat : 0,
                            "Sort"             => $line_item->number,
                            "ProductID"        => !empty($product->kashflow_id) ? (int)$product->kashflow_id : 0,
                            "ValuesInCurrency" => 0,
                            "ProjID"           => 0,
                        );
                        $lines[] = new SoapVar($line, XSD_STRING, "InvoiceLine", "KashFlow");
                    }
                }
                $parameters['Inv'] = array
                (
                    "InvoiceDBID"   => 0,
                    "InvoiceNumber" => 0,
                    "InvoiceDate"   => $bean->invoice_date."T00:00:00",
                    "DueDate"       => $bean->due_date."T00:00:00",
                    "CustomerID"    => (int)$accountBean->kashflow_id,
                    "Paid"          => $bean->status == "Paid" ? 1 : 0,
                    "SuppressTotal" => 0,
                    "ProjectID"     => 0,
                    "ExchangeRate"  => "0.0000",
                    "Lines"         => !empty($lines) ? $lines : array(),
                    "NetAmount"     => !empty($bean->total_amount) ? $bean->total_amount : "0.0000",
                    "VATAmount"     => !empty($bean->tax_amount) ? $bean->tax_amount : "0.0000",
                    "AmountPaid"    => !empty($bean->amount_paid) ? $bean->amount_paid : "0.0000",
                    "UseCustomDeliveryAddress"  => false,
                );
                if(!empty($bean->due_date) && !empty($bean->invoice_date) && !empty($accountBean->kashflow_id)) $response = $kashflow->insertInvoice($parameters);
                else SugarApplication::appendErrorMessage($app_strings['LBL_FAILED_KASHFLOW_INVOICES_FIELDS']);
                if(!empty($response->InsertInvoiceResult)){
                    $invoiceResponse = $kashflow->getInvoice($response->InsertInvoiceResult);
                    $sql = "UPDATE aos_invoices SET number = '".$response->InsertInvoiceResult."', kashflow_id = '".$invoiceResponse->GetInvoiceResult->InvoiceDBID."' WHERE id = '".$bean->id."'";
                    $bean->db->query($sql);
                }
            }
            if($response->Status == "NO") SugarApplication::appendErrorMessage($app_strings['LBL_FAILED_KASHFLOW_INVOICES']);
        }
    }

    function calculateTotals($bean){

        if(empty($bean->total_amt) || $bean->total_amt == "" || $bean->total_amt == "0.00") {
            $sql = "SELECT pg.id, pg.group_id FROM aos_products_quotes pg LEFT JOIN aos_line_item_groups lig ON pg.group_id = lig.id WHERE pg.parent_type = '".$bean->object_name."' AND pg.parent_id = '".$bean->id."' AND pg.deleted = 0 ORDER BY lig.number ASC, pg.number ASC";
            $result = $bean->db->query($sql);
            $tot_amt = 0;
            $dis_tot = 0;
            $tax = 0;

            while ($row = $bean->db->fetchByAssoc($result)) {
                $line_item = new AOS_Products_Quotes();
                $line_item->retrieve($row['id']);
                $qty = $line_item->product_qty;
                $list_price = $line_item->product_list_price;
                $unit_price = $line_item->product_unit_price;
                $discount_amount = $line_item->product_discount_amount;
                $vat_amt = $line_item->vat_amt;
                $deleted = $line_item->deleted;

                if ($qty !== 0 && $list_price !== null && $deleted != 1) {
                    $tot_amt += $list_price * $qty;
                } else if ($qty !== 0 && $unit_price !== 0 && $deleted != 1) {
                    $tot_amt += $unit_price * $qty;
                }
                if ($discount_amount !== 0 && $deleted != 1) {
                    $dis_tot += $discount_amount * $qty;
                }
                if ($vat_amt !== 0 && $deleted != 1) {
                    $tax += $vat_amt;
                }
            }
            if($tot_amt == 0) {
                return;
            }
            $subtotal = $tot_amt + $dis_tot;

            $bean->total_amt = $tot_amt;
            $bean->discount_amount = $dis_tot;
            $bean->subtotal_amount = $subtotal;

            $shipping = $bean->shipping_amount;

            $shippingtax = $bean->shipping_tax;

            $shippingtax_amt = $shipping * ($shippingtax/100);

            $bean->shipping_tax_amt = $shippingtax_amt;

            $tax += $shippingtax_amt;

            $bean->tax_amount = $tax;

            $bean->subtotal_tax_amount = $subtotal + $tax;
            $bean->total_amount = $subtotal + $tax + $shipping;
        }
    }

    function makeGroup($bean) {
        $sql = "SELECT id, total_amount FROM aos_line_item_groups WHERE parent_id = '".$bean->id."' AND deleted = '0'";
        $result = $bean->db->query($sql);
        $row = $bean->db->fetchByAssoc($result);
        if($row['total_amount'] == "0.000000") {
            $sql = "DELETE FROM aos_line_item_groups WHERE id = '".$row['id']."'";
            $bean->db->query($sql);
            $row['id'] = null;
        }
        if(empty($row['id'])) {
            $group = new AOS_Line_Item_Groups();
            $group->number = 1;
            $group->assigned_user_id = $bean->assigned_user_id;
            $group->currency_id = $bean->currency_id;
            $group->parent_id = $bean->id;
            $group->parent_type = $bean->object_name;
            $sql = "SELECT pg.id FROM aos_products_quotes pg WHERE pg.parent_type = '".$bean->object_name."' AND pg.parent_id = '".$bean->id."' AND pg.deleted = 0 ORDER BY pg.number ASC";
            $result = $bean->db->query($sql);
            $tot_amt = 0;
            $dis_tot = 0;
            $tax = 0;

            while ($row = $bean->db->fetchByAssoc($result)) {
                $line_item = new AOS_Products_Quotes();
                $line_item->retrieve($row['id']);
                $qty = $line_item->product_qty;
                $list_price = $line_item->product_list_price;
                $unit_price = $line_item->product_unit_price;
                $discount_amount = $line_item->product_discount_amount;
                $vat_amt = $line_item->vat_amt;
                $deleted = $line_item->deleted;

                if ($qty !== 0 && $list_price !== null && $deleted != 1) {
                    $tot_amt += $list_price * $qty;
                } else if ($qty !== 0 && $unit_price !== 0 && $deleted != 1) {
                    $tot_amt += $unit_price * $qty;
                }
                if ($discount_amount !== 0 && $deleted != 1) {
                    $dis_tot += $discount_amount * $qty;
                }
                if ($vat_amt !== 0 && $deleted != 1) {
                    $tax += $vat_amt;
                }
            }
            if($tot_amt == 0) {
                return;
            }
            $subtotal = $tot_amt + $dis_tot;
            $group->total_amt = $tot_amt;
            $group->discount_amount = $dis_tot;
            $group->subtotal_amount = $subtotal;
            $group->tax_amount = $tax;
            $group->subtotal_tax_amount = $subtotal + $tax;
            $group->total_amount = $subtotal + $tax;
            $group->save();
            $sql = "UPDATE aos_products_quotes SET group_id = '".$group->id."' WHERE parent_id = '".$bean->id."' AND deleted = '0'";
            $bean->db->query($sql);
        }
    }
}
