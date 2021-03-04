<?php
/**
 * Created by PhpStorm.
 * User: Edgar
 * Date: 6/22/2018
 * Time: 11:25 AM
 */

namespace rnwcinv\htmlgenerator;


use rnwcinv\htmlgenerator\fields\FieldFactory;
use rnwcinv\htmlgenerator\fields\PDFField;

class AreaGenerator
{
    /** @var ContainerBaseDTO */
    private $options;

    /** @var FieldDTO[] */
    private $fields;
    public $tagGenerator;
    private $orderValueRetriever;

    public function __construct($options, $fields,$orderValueRetriever)
    {
        $this->tagGenerator=new TagGenerator();
        $this->options = $options;
        $this->fields = $fields;
        $this->orderValueRetriever = $orderValueRetriever;
    }

    public function Generate(){
        $areaStyles=array('height'=>$this->options->height.'px','width'=>$this->options->width.'px');
        if($this->options->position=='documentFooter')
        {
            $areaStyles['position'] = 'absolute';
            $areaStyles['overflow'] = 'hidden';
            $areaStyles['bottom'] = '0px';
        }else{
            $areaStyles['position'] = 'relative';
        }
        $html=$this->tagGenerator->StartTag('div',$this->options->position,$areaStyles,null);
        foreach($this->fields as $field)
        {
            /** @var PDFField $createdField */
            $createdField=FieldFactory::GetField($field,$this->orderValueRetriever);
            $html.=$createdField->GetHTML();

        }

        $html.='</div>';
        return $html;
    }

}