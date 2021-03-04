<?php
/**
 * Created by PhpStorm.
 * User: Edgar
 * Date: 6/22/2018
 * Time: 6:20 AM
 */

namespace rnwcinv\htmlgenerator;


use rnwcinv\pr\utilities\FontManager;

class DocumentGenerator
{
    /**
     * @var DocumentOptionsDTO
     */
    public $options;
    /** @var OrderValueRetriever */
    private $orderValueRetriever;

    public function __construct($options,$orderValueRetriever)
    {
        $this->options = $options;
        $this->orderValueRetriever = $orderValueRetriever;
    }

    public function Generate(){
        $html="<html><body class='pdfBody'>";
        $html.='<style>
                                @font-face{
                                    font-family:\'FontAwesome\';
                                    src:url("'.\RednaoWooCommercePDFInvoice::$DIR.'css/fontAwesome/fonts/fontawesome-webfont.ttf");
                                    }
                                    
                                    
                          .total .woocommerce-Price-amount {
							   display:block;
							}
    
                            
                                body{
                                    font-family:\'DejaVu Sans\';
                                    font-weight: normal;                                
                                    font-size: 14px;
                                }
                                p{
                                    line-height: 1em !important;
                                    margin:0;
                                    padding:0;
                                }
                                @page{
                                    margin:0;
                                }                            
                 </style>';
        $html.='<style>'.$this->options->containerOptions->styles.'</style>';

        if(\RednaoWooCommercePDFInvoice::IsPR())
        {
            $fontManager=new FontManager();
            $fonts=$fontManager->GetAvailableFonts(false);
            $html.='<style>';
            $fontURL=$fontManager->GetFontPath();
            foreach($fonts as $currentFont){
                $html.= " @font-face{font-family:\"$currentFont\";
              src:url(\"".$fontURL.urlencode($currentFont).".ttf\");
                }";

                $html.= " @font-face{font-family:\"$currentFont\";
              src:url(\"".$fontURL.urlencode($currentFont).".ttf\");
                font-weight:bold;} ";
            }
            $html.='</style>';
        }

        for($i=0;$i<count($this->options->pages);$i++)
        {
            $pageGenerator=new PageGenerator($this,$this->options->pages[$i],$this->orderValueRetriever,$i);
            $html.=$pageGenerator->Generate();
        }
        $html.="</body></html>";
        return $html;
    }

    /**
     * @return FieldDTO[]
     */
    public function GetFieldsDictionary(){
        $dictionary=array();

        foreach($this->options->pages as $page)
        {
            foreach ($page->fields as $field)
                $dictionary['pdfField_'.$field->fieldID]=$field;
        }

        return $dictionary;
    }




}


