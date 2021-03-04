<?php
/**
 * Created by PhpStorm.
 * User: Edgar
 * Date: 11/18/2018
 * Time: 7:45 AM
 */

namespace rnwcinv\bulk_actions;



use RednaoPDFGenerator;
use RednaoWooCommercePDFInvoice;
use rnwcinv\pr\htmlgenerator\merger\MergeItem;
use rnwcinv\pr\htmlgenerator\merger\PDFMerger;
use rnwcinv\pr\utilities\Printer\Printer;

class RNBulkActionManager
{
    public function InitializeHooks(){
        add_filter( "bulk_actions-edit-shop_order", array($this,'AddBulkActions') );
        add_filter( 'handle_bulk_actions-edit-shop_order',array($this,'HandleBulkAction'), 10, 3);
        add_action( 'admin_enqueue_scripts', array($this,'EnqueueScript'));
    }

    public function EnqueueScript(){
        global $typenow;

        if($typenow!='shop_order')
            return;

        $screen=get_current_screen();
        if($screen->base!='edit')
            return;

        \wp_enqueue_script('rednao_pdfinv_bulk_manager',RednaoWooCommercePDFInvoice::$URL.'js/bulkManager/BulkManager.js');

        global $wpdb;
        $invoices=$wpdb->get_results('select invoice_id InvoiceID, name Name from '.RednaoWooCommercePDFInvoice::$INVOICE_TABLE);

        \wp_localize_script('rednao_pdfinv_bulk_manager','bulkManagerVar',array(
            'invoices'=>$invoices
        ));

    }

    public function HandleBulkAction( $redirect_to, $action, $post_ids ) {


        if(!RednaoWooCommercePDFInvoice::IsPR())
            return $redirect_to;
        ini_set('max_execution_time', 300000);
        require_once RednaoWooCommercePDFInvoice::$DIR.'PDFGenerator.php';
        $templateId='';
        if(!isset($_GET['rnTemplateId']))
        {
            $options=RednaoPDFGenerator::GetPageOptionsById(-1);
            $templateId=$options->invoiceTemplateId;

        }else
            $templateId=$_GET['rnTemplateId'];
        
        if($action=='rnview_invoice')
        {set_time_limit(0);

            $mergeItems=array();
            foreach($post_ids as $invoiceId)
            {

                $mergeItems[]=new MergeItem($invoiceId,$templateId);
            }

            $merger=new PDFMerger();
            $merger->Merge($mergeItems);
            $merger->Stream();
            die();
        }


        if($action=='rnprint_invoice')
        {

            $invoiceOptions=\RednaoPDFGenerator::GetPageOptionsById($templateId);
            foreach($post_ids as $invoice)
            {
                $order=wc_get_order($invoice);
                if($order==false)
                {
                    echo "Invalid Order Number";
                    die();
                }

                require_once RednaoWooCommercePDFInvoice::$DIR. 'PDFGenerator.php';
                $generator=new RednaoPDFGenerator($invoiceOptions,false,$order);
                $generator->Generate(true,true);
                $printer=new Printer();
                $printer->PrintPDF($generator->GetFileName(),$generator->GetPrintableOutput());

            }

            echo '<div class="updated"><p>'._('Invoices printed successfully').'</p></div>';
            return $redirect_to;
        }

        if($action=='rndownload_invoice')
        {
            set_time_limit(0);
            require_once RednaoWooCommercePDFInvoice::$DIR.'PDFGenerator.php';





            $uploadDir = wp_upload_dir();

            $path = $uploadDir['basedir'] . '/sf_pdfs_bulk';

            $zip = new \ZipArchive();
            $zip->open($path . 'documents.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $usedNames=[];
            foreach ($post_ids as $invoice)
            {
                $order = wc_get_order($invoice);
                if ($order == false)
                {
                    echo "Invalid Order Number";
                    die();
                }

                $generator = new RednaoPDFGenerator(RednaoPDFGenerator::GetPageOptionsById($templateId), false, $order);
                $generator->Generate(true, true);
                $name=$generator->GetFileName();
                $nameToCheck=strtolower($name);
                $index=1;
                while(array_search($nameToCheck,$usedNames)!==false)
                {
                    $nameToCheck=strtolower($name).'('.$index.')';
                    $index++;
                }
                $usedNames[]=$nameToCheck;
                $zip->addFromString($nameToCheck. '.pdf', $generator->GetOutput());
            }
            $zip->close();

            header("Content-Type: application/zip");
            header("Content-Disposition: attachment; filename=documents.zip");
            header("Content-Length: " . filesize($path.'documents.zip'));
            readfile($path.'documents.zip');


            $files = glob($path.'*'); // get all file names
            foreach($files as $file){ // iterate files
                if(is_file($file))
                    unlink($file); // delete file
            }

            die();

        }


       /* if ( $action !== 'write_downloads' )
            return $redirect_to; // Exit

        global $attach_download_dir, $attach_download_file; // ???

        $processed_ids = array();

        foreach ( $post_ids as $post_id ) {
            $order = wc_get_order( $post_id );
            $order_data = $order->get_data();

            // Your code to be executed on each selected order
            $processed_ids[] = $post_id;
        }

        return $redirect_to = add_query_arg( array(
            'write_downloads' => '1',
            'processed_count' => count( $processed_ids ),
            'processed_ids' => implode( ',', $processed_ids ),
        ), $redirect_to );*/
    }



    public function AddBulkActions($actions){

       $actions['rnview_invoice'] = 'Bulk view invoices (full version only)';
       $actions['rnprint_invoice'] = 'Bulk print invoices (full version only)';
       $actions['rndownload_invoice'] = 'Bulk download invoices (full version only)';
        return $actions;
    }

}