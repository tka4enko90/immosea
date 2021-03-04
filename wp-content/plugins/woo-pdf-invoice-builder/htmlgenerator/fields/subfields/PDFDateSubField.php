<?php
namespace rnwcinv\htmlgenerator\fields\subfields;
class PDFDateSubField extends PDFSubFieldBase {

    public function FormatValue($value,$format='')
    {
        $format=$this->GetFieldOptions('format');
        $formattedDate=date($format,$value);
        if(!$formattedDate)
            return 'Invalid Format';
        return $formattedDate;
    }


    public function GetTestFieldValue()
    {
        return strtotime('today');
    }

    public function GetWCFieldName()
    {
        return "order_date";
    }




}