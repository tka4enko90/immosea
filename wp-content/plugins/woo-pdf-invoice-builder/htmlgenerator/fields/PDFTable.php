<?php
namespace  rnwcinv\htmlgenerator\fields;

use RednaoWooCommercePDFInvoice;
use rnwcinv\htmlgenerator\FieldDTO;
use rnwcinv\pr\CustomField\utilities\CustomFieldValueRetriever;
use rnwcinv\pr\CustomFieldV2\Wrappers\CRow;



/**
 * Created by PhpStorm.
 * User: Edgar
 * Date: 10/6/2017
 * Time: 6:52 AM
 */

class PDFTable extends PDFFieldBase
{

    protected $items=array();
    /** @var CRow[] */
    public $CustomRows;



    private function CreateTotalsRows()
    {
        $totalsTable='';
        if($this->GetBoolValue('ShowTotalQuantity'))
        {
            $totalsTable.=$this->CreateTotalRow('quantity','TotalQuantityLabel');
        }

        if($this->GetBoolValue('ShowTotalWeight'))
        {
            $totalsTable.=$this->CreateTotalRow('weight','TotalWeightLabel');
        }


        if($this->GetBoolValue('ShowSubtotal'))
        {
            $totalsTable.=$this->CreateTotalRow('subtotal','SubTotalLabel');
        }

        if($this->GetBoolValue('ShowDiscount'))
        {
            $totalsTable.=$this->CreateTotalRow('discount','DiscountLabel',true);
        }

        if($this->GetBoolValue('ShowShipping'))
        {
            $shippingText=$this->CreateTotalRow('shipping','ShippingLabel',true);
            $shippingText=apply_filters('wcpdfi_set_shipping_text',$shippingText);
            $totalsTable.=$shippingText;
        }

        if($this->GetBoolValue('ShowFees')&&$this->GetFeePosition()=='subtotal')
        {
            $totalsTable.=$this->CreateTabularRow($this->GetFees(),'fees');
        }

        if($this->GetBoolValue('ShowTaxes'))
        {
            $totalsTable.=$this->CreateTabularRow($this->GetTaxes(),'taxes');
        }

        if($this->GetBoolValue('ShowTotal'))
        {
            $totalsTable.=$this->CreateTotalRow('total','TotalLabel');
        }
        

        if(strlen($totalsTable)>0)
            $totalsTable='<table width="100%" class="footerTable"><tbody>'.$totalsTable.'</tbody></table>';


        return $totalsTable;


    }

    private function CreateDetailRows($columns)
    {
        $rows='<tbody>';
        $count=count($this->items);
        for($i=0;$i<$count;$i++)
        {
            $rows.='<tr class="invoiceDetailRow">';
            foreach ($columns as $column)
            {
                $rows.=$this->CreateDetailColumn($column,$this->GetDetailValue($i,$column->type,$column));
            }
            $rows.='</tr>';
        }

        $rows.='</tbody>';
        return $rows;

    }




    protected function CreateDetailColumn($column,$value)
    {
        if($column->type=='prod_thumbnail')
        {

            $thumbnails=$this->GetColumn('prod_thumbnail');
            $width='75px';
            $height='75px';
            if($thumbnails!=null)
            {
                if(isset($thumbnails->additionalProperties))
                {
                    if(isset($thumbnails->additionalProperties->maxWidth))
                        $width=$thumbnails->additionalProperties->maxWidth;

                    if(isset($thumbnails->additionalProperties->maxHeight))
                        $height=$thumbnails->additionalProperties->maxHeight;
                }
            }
            return '<td class="'.esc_attr($column->type).'" style="text-align:center;width:'.esc_attr($column->width).'">'.
                    '<img style="max-width:'.$width.';max-height:'.$height.'" src="'.htmlspecialchars($value).'"/>'.
                '</td>';
        }




        $id=esc_attr($column->type);
        if($column->type=='custom')
        {
            $id='cust_'.$column->customProperties->id;
        }

        return '<td class="'.$id.'" style="width:'.esc_attr($column->width).'">'.
            $value.'</td>';

    }


    /**
     * @param $columns stdClass[]
     * @return string
     */
    private function CreateHeader($columns)
    {
        /** @var FieldDTO $options */
        $options=$this->options;
        $header='<thead><tr>';
        foreach($columns as $column)
        {
            $id=esc_attr($column->type);
            if($column->type=='custom')
            {
                $id='th_cust_'.$column->customProperties->id;
            }
            $header.='<th class="'.$id.'" style="width:'.$column->width.';">'.htmlspecialchars($this->orderValueRetriever->TranslateText($options->fieldID,$column->type,$column->header) ).'</th>';
        }
        $header.='</tr></thead>';
        return $header;
    }


    protected function CreateTabularRow($rows, $class){
        $table='';
        if(count($rows)>0)
        {
            $table='';
            foreach($rows as $row)
            {
                $table.='<tr class="'.esc_attr($class).' subTotalRow"><td width="100%"></td><th class="subTotalLabel"><p style="width: 120px;margin:0;padding:0;">'.htmlspecialchars($row['label']).'</p></th>'.
                    '<td class="subTotalValue"><p style="width: 100px;margin:0;padding:0;">'.$row['value'].'</p></td></tr>';

            }
        }
        return $table;
    }

    private function CreateTotalRow($type,$labelProperty,$omitIfEmpty=false)
    {
        if (!empty($this->options))
        {
            $options=$this->options;
        }
        $total=$this->GetTotalValue($type,$omitIfEmpty);
        if($total==''&&$omitIfEmpty)
            return '';
        return '<tr class="'.esc_attr($type).' subTotalRow">'.
                    '<td width="100%"></td><th class="subTotalLabel"><p style="width: 120px;margin:0;padding:0;">'.htmlspecialchars($this->orderValueRetriever->TranslateText($options->fieldID,$labelProperty,$this->GetPropertyValue($labelProperty))).'</p></th>'.
                    '<td class="subTotalValue"><p style="width: 100px;margin:0;padding:0;">'.$total.'</p></td></tr>';


    }





    protected function GetTaxes()
    {
        $includePercentage=$this->GetSubTotalProperty('Taxes','includePercentages');
        $taxes=array();
        if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
            foreach($this->orderValueRetriever->order->get_taxes() as $tax){
                $percentage='';

                if($includePercentage)
                {
                    $percentage=$tax->get_meta('rn_tax_percentage',true);
                    if($percentage!==false)
                    {
                        $percentage=' '.$percentage;
                    }
                }

                $taxes[]=array(
                    'label'=>htmlspecialchars_decode($tax->get_label()).$percentage,
                    'value'=>wc_price((float) $tax->get_tax_total() + (float) $tax->get_shipping_tax_total(),array( 'currency' => $this->orderValueRetriever->get('currency') ))
                );

            }
            return $taxes;
        }else{
            $taxes[]=array(
                'label'=>$this->GetPropertyValue('TaxesLabel'),
                'value'=>wc_price($this->orderValueRetriever->order->get_total_tax(), \apply_filters('rnwcinv_format_price',array( 'currency' =>  $this->orderValueRetriever->get('currency')) ) )
            );
        }
        return $taxes;
    }

    protected function GetFees()
    {
        $fees=array();
        foreach($this->orderValueRetriever->order->get_fees() as $fee){
            $fees[]=array(
                'label'=>$this->GetFeePropertyValue($fee,'name'),
                'value'=>wc_price($this->GetFeePropertyValue($fee,'total'), \apply_filters('rnwcinv_format_price',array( 'currency' =>  $this->orderValueRetriever->get('currency')) ) )
            );
        }
        return $fees;
    }

    protected function GetFeePropertyValue($object,$propertyName)
    {
        if(!is_array($object))
        {
            if ( is_callable( array( $object, "get_{$propertyName}" ) ) ) {
                return $object->{"get_{$propertyName}"}();
            } else {
                if(is_callable(array($object,$propertyName)))
                    return $object->$propertyName;
            }
        }else{
            if(isset($object[$propertyName]))
                return $object[$propertyName];
            if(isset($object['line_'.$propertyName]))
                return $object['line_'.$propertyName];
        }
        return null;
    }

    protected function GetTotalValue($totalType,$omitIfEmpty)
    {
        if($totalType=='quantity')
        {
            $items=$this->orderValueRetriever->order->get_items();
            $quantity=0;
            foreach($items as $currentItem)
            {
                $quantity+=$currentItem->get_quantity();
            }

            return $quantity;
        }

        if($totalType=='weight')
        {
            /** @var \WC_Order_Item[] $items */
            $items=$this->orderValueRetriever->order->get_items();
            $weight=0;
            foreach($items as $currentItem)
            {
                $product=$currentItem->get_product();
                $weight+=\floatval($product->get_weight()*$currentItem->get_quantity());
            }

            return $weight . get_option( 'woocommerce_weight_unit' );
        }

        if($totalType=='shipping')
        {
            $excludeCarrier=$this->GetSubTotalProperty('Shipping','excludeCarrierName');
            if($excludeCarrier)
                return \wc_price($this->orderValueRetriever->order->get_shipping_total(),\apply_filters('rnwcinv_format_price',array( 'currency' =>  $this->orderValueRetriever->get('currency')) ));
            return $this->orderValueRetriever->GetTotal('shipping', $omitIfEmpty);
        }

        if($totalType=='discount')
        {
            $includeTax=$this->GetSubTotalProperty('SubTotal','includeTaxes')===true;
            $total=$this->orderValueRetriever->order->get_discount_total();
            if($includeTax)
                $total+=$this->orderValueRetriever->order->get_discount_tax();



            return wc_price($total, \apply_filters('rnwcinv_format_price',array( 'currency' => $this->orderValueRetriever->get('currency') )));
        }

        if($totalType=='subtotal')
        {
            $includeTax=$this->GetSubTotalProperty('SubTotal','includeTaxes')===true;
            $includeDiscount=$this->GetSubTotalProperty('SubTotal','includeDiscount')===true;
            $total=$this->orderValueRetriever->order->get_subtotal();
            if($includeTax)
                $total+=$this->orderValueRetriever->order->get_total_tax();
            if($includeDiscount)
                $total-=$this->orderValueRetriever->order->get_total_discount();

            if($this->GetFeePosition()=='table')
            {
                $fees=$this->orderValueRetriever->order->get_fees();
                $totalFees=0;
                foreach ($fees as $fee)
                {
                   $totalFees+=floatval($fee->get_total());
                   if($includeTax)
                       $totalFees+=floatval($fee->get_total_tax());
                }

                $total+=$totalFees;

            }
            return wc_price($total, \apply_filters('rnwcinv_format_price',array( 'currency' => $this->orderValueRetriever->get('currency') )));
        }


        if($totalType=='total')
            return $this->orderValueRetriever->order->get_formatted_order_total();
        return '';

    }


    protected function GetSubTotalProperty($type,$property)
    {
        $assoc=$this->GetAssoc($type.'AdditionalProperties');
        if(!isset($assoc->$property))
            return '';

        return $assoc->$property;

    }

    protected function GetItems()
    {
        $items=array();
        $imageOptions=$this->GetColumn('prod_thumbnail');
        $regularPriceIncludeTaxes=$this->GetAdditionalOptionsProperty('regular_price','includeTaxes');
        $unitPriceIncludeTaxes=$this->GetAdditionalOptionsProperty('unit_price','includeTaxes');

        foreach($this->orderValueRetriever->order->get_items() as $orderItem)
        {

            $product=$orderItem->get_product();
            $sku='';
            $weight='';
            $regularPrice='';
            $unitPrice='';

            if(!empty($product))
            {
                $sku=$product->get_sku();
                $regularPrice=$product->get_regular_price();
                if($regularPriceIncludeTaxes)
                {
                    $regularPrice=wc_get_price_including_tax($product,array('price'=>$regularPrice));
                }
                $regularPrice=wc_price($regularPrice, \apply_filters('rnwcinv_format_price',array( 'currency' =>  $this->orderValueRetriever->get('currency') )) );

                $unitPrice=$product->get_price();
                if($unitPriceIncludeTaxes)
                {
                    $unitPrice=wc_get_price_including_tax($product,array('price'=>$unitPrice));
                }
                $unitPrice=wc_price($unitPrice, \apply_filters('rnwcinv_format_price',array( 'currency' =>  $this->orderValueRetriever->get('currency') )) );
                $weight= $product->get_weight();

            }

            $price=$this->GetPrice($orderItem,$unitPriceIncludeTaxes,false);
            $qty=$orderItem['qty'];

            if(!\is_numeric($qty))
                $qty=1;

            $unitPrice=$price/$qty;
            $newItem=array(
                'data'=>$orderItem,
                'prod'=>$orderItem['name'],
                'qty'=>$orderItem['qty'],
                'price'=>wc_price($this->GetPrice($orderItem),\apply_filters('rnwcinv_format_price', array( 'currency' => $this->orderValueRetriever->get('currency') ))),
                'vat'=>wc_price($orderItem['line_tax'],\apply_filters('rnwcinv_format_price', array( 'currency' => $this->orderValueRetriever->get('currency') ))),
                'discount'=>wc_price(floatval($orderItem['line_total'])-floatval($orderItem['line_subtotal']),\apply_filters('rnwcinv_format_price', array( 'currency' => $this->orderValueRetriever->get('currency') ))),
                'sku'=>$sku,
                'regular_price'=>$regularPrice,
                'unit_price'=>wc_price($unitPrice,\apply_filters('rnwcinv_format_price', array( 'currency' => $this->orderValueRetriever->get('currency') ))),
                'weight'=> $weight,
                'total_tax'=>wc_price($this->orderValueRetriever->order->get_line_tax($orderItem),\apply_filters('rnwcinv_format_price', array( 'currency' => $this->orderValueRetriever->get('currency') ))),
                'total'=>wc_price(floatval($orderItem['total'])+floatval($orderItem['total_tax']),\apply_filters('rnwcinv_format_price', array( 'currency' => $this->orderValueRetriever->get('currency') )))
            );


            if($imageOptions!=null)
            {
                $var=null;
                $var = apply_filters( 'woocommerce_order_item_thumbnail', $var, $orderItem );

                /** @var WC_Product $product */
                $product=$orderItem->get_product();
                $imagePath='';
                if($product!=false)
                    $imagePath = \get_attached_file( $product->get_image_id());


                $newItem['prod_thumbnail']=$imagePath;
            }


            if($this->GetColumn('description'))
            {
                $newItem['description'] = get_post($orderItem['product_id'])->post_content;

            }

            if($this->GetColumn('short_description'))
            {
                $newItem['short_description'] = get_post($orderItem['product_id'])->post_excerpt;

            }

            $items[]=$newItem;
            
            foreach($this->CustomRows as $customRow)
            {
                CustomFieldValueRetriever::$lineItem=$orderItem;
                foreach($customRow->GetItems() as $subItem)
                {
                    $items[]=$subItem;
                }

            }

        }

        if($this->GetFeePosition()=='table')
        {
            foreach($this->orderValueRetriever->order->get_fees() as $fee){
                $newItem=array(
                    'data'=>null,
                    'prod'=>$this->GetFeePropertyValue($fee,'name'),
                    'qty'=>1,
                    'price'=>wc_price($this->GetFeePropertyValue($fee,'total'),\apply_filters('rnwcinv_format_price', array( 'currency' =>  $this->orderValueRetriever->get('currency') ) )),
                    'vat'=>0,
                    'discount'=>0,
                    'sku'=>'',
                    'regular_price'=>0,
                    'weight'=> 0
                );
                $items[]=$newItem;
            }



        }
        return apply_filters('wcpdfi_get_items',$items);
    }

    /**
     * @return '
     */
    protected function GetFeePosition(){
        if(!RednaoWooCommercePDFInvoice::IsPR()||(isset($this->options->FeesAdditionalProperties)&&$this->options->FeesAdditionalProperties->Position=='table'))
            return 'table';
        return 'subtotal';
    }

    protected function GetColumn($columnName)
    {
        foreach($this->GetArray('ColumnOptions') as $option)
        {
            if($option->type==$columnName)
            {
                return $option;
            }
        }
        return null;
    }

    protected function GetAdditionalOptionsProperty($type,$propertyName)
    {
        foreach($this->GetArray('ColumnOptions') as $option)
        {
            if($option->type==$type)
            {
                if(!isset($option->additionalProperties))
                    return '';

                if(!isset($option->additionalProperties->$propertyName))
                    return '';
                return $option->additionalProperties->$propertyName;
            }
        }
        return '';
    }







    protected function GetDetailValue($i, $type,$column)
    {
        if($type=='custom')
        {
            if($this->orderValueRetriever->useTestData)
            {
                return 'test';
            }

            return $this->GetCustomColumnValue($column->customProperties->id,$i);
        }
        return $this->items[$i][$type];

    }

    private function GetCustomColumnValue($id,$index)
    {

        global $wpdb;
        $results=$wpdb->get_results($wpdb->prepare('select custom_field_text from '.\RednaoWooCommercePDFInvoice::$CUSTOM_FIELDS_TABLE.' where custom_field_id=%s',$id),'ARRAY_A');
        if($results!==false&&count($results)>0)
        {
            $order=$this->orderValueRetriever->order;




            /** @var \WC_Order_Item_Product $item */
            $item=$this->items[$index]['data'];

            if(!isset($item['sku']))
            {
                $item['sku']=$this->items[$index]['sku'];
            }
            if(!isset($item['weight']))
            {
                $item['weight']=$this->items[$index]['weight'];
            }
            $evalResult='';
            /** @noinspection PhpUnusedLocalVariableInspection  use on eval*/
            $actions=$this;
            try{
                $customCode=$results[0]['custom_field_text'];
                $customCode='use rnwcinv\pr\CustomField\CustomFieldFactory; use rnwcinv\pr\CustomField\utilities\CustomFieldValueRetriever;use rnwcinv\pr\CustomFieldV2;
             use rnwcinv\pr\CustomFieldV2\BasicFields\CNumericField;
             use rnwcinv\pr\CustomFieldV2\BasicFields\CArrayField;
             use rnwcinv\pr\CustomFieldV2\BasicFields\CSimpleField;
             use rnwcinv\pr\CustomFieldV2\BasicFields\CCurrencyField;
             use rnwcinv\pr\CustomFieldV2\BasicFields\CImageField;
        use rnwcinv\pr\CustomFieldV2\Wrappers\CRow; '.$customCode;


                CustomFieldValueRetriever::$order=$order;
                CustomFieldValueRetriever::$lineItem=$this->items[$index]['data'];
                
                $evalResult= eval($customCode);
            }catch(\Exception $ex)
            {
                echo $ex;
            }

            if($evalResult==null)
                return '';
            return $evalResult;
        }
        return '';



    }

    public function FormatCurrency($value){
        return wc_price($value, \apply_filters('rnwcinv_format_price',array( 'currency' =>  $this->orderValueRetriever->get('currency') )) );
    }

    public function QRCode($string)
    {
        require_once \RednaoWooCommercePDFInvoice::$DIR.'vendor/phpqrcode/qrlib.php';
        $svgCode = \QRcode::svg($string,false,QR_ECLEVEL_L,3,0);
        return '<img   src="data:image/svg+xml;base64,' . base64_encode($svgCode).'"/>';
    }

    private function GetPrice($orderItem,$includeTaxes=null,$includeDiscount=null)
    {
        if($includeTaxes===null)
            $includeTaxes=$this->GetAdditionalOptionsProperty('price','includeTaxes');
        if($includeDiscount===null)
            $includeDiscount=$this->GetAdditionalOptionsProperty('price','includeDiscount');

        if($includeDiscount)
        {
            $value = floatval($orderItem['line_total']);
        }
        else
        {
            $value = floatval($orderItem['line_subtotal']);
        }

        if($includeTaxes)
        {
            $value = $value + floatval($orderItem['line_tax']);
        }

        return $value;
    }


    protected function InternalGetHTML()
    {
        $this->GetCustomRowsIfAny();
        $this->items=$this->GetItems();
        $columns=$this->GetArray('ColumnOptions');
        $html='<table class="pdfTable" style="width:100%">';
        $html.=$this->CreateHeader($columns);
        $html.=$this->CreateDetailRows($columns);
        $html.='</table>';
        $html.=$this->CreateTotalsRows();
        return $html;
    }



    private function GetCustomRowsIfAny()
    {
        $this->CustomRows=[];
        if(isset($this->options->CustomRows))
        {
            $ids=\implode(',',$this->options->CustomRows);
            global $wpdb;
            $rows=$wpdb->get_results($wpdb->prepare('select custom_field_text from '.\RednaoWooCommercePDFInvoice::$CUSTOM_FIELDS_TABLE.' where custom_field_id in (%d)',$ids));
            foreach($rows as $row)
            {
                $customRow=$this->ProcessCustomRowText($row->custom_field_text);
                if($customRow!=null)
                    $this->CustomRows[]=$customRow;
            }
        }
    }

    public function ProcessCustomRowText($text){
        $customField=
            'use rnwcinv\pr\CustomField\CustomFieldFactory; 
             use rnwcinv\pr\CustomField\utilities\CustomFieldValueRetriever;
             use rnwcinv\pr\CustomFieldV2;
             use rnwcinv\pr\CustomFieldV2\BasicFields\CNumericField;
             use rnwcinv\pr\CustomFieldV2\BasicFields\CArrayField;
             use rnwcinv\pr\CustomFieldV2\BasicFields\CSimpleField;
             use rnwcinv\pr\CustomFieldV2\BasicFields\CCurrencyField;
        use rnwcinv\pr\CustomFieldV2\Wrappers\CRow; ';

        $text=str_replace("\\","\\\\",$text);
        $text=\str_replace("\n",'',$text);
        $customField.=$text;
        return  eval($customField);

    }


}