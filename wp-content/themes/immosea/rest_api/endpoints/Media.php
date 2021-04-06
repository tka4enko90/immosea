<?php

class Media extends HttpError
{
    private $error;
    private $file_type;
    private $format;
    private $upload_dir;
    private $hashed_filename;
    private $attach_id;

    public function create_media(\WP_REST_Request $request)
    {
        $this->error = new HttpError();
        $this->setUploadDir(wp_upload_dir());

        $base64 = $request->get_body();
        $pos  = strpos($base64, ';');
        $type = explode(':', substr($base64, 0, $pos))[1];
        $this->setFileType($type);

        if ($this->check_allow_type($this->getFileType())) {
            return $this->error->setStatusCode(400)->setMessage("Mime types of base64 doesn't exist in app")->report();
        }

        $this->set_allow_format($this->getFileType());
        $this->setHashedFilename(md5( microtime()).'.'.$this->getFormat()) ;

        $this->create_image_file($base64);
        $this->create_attachment_id();

        $attach_id = $this->getAttachId();
        $this->updated_attachment_metadata($attach_id);

        $response = array(
            'attachment_id' => $attach_id,
            'attachment_url' => wp_get_attachment_url($attach_id)
        );

        return $response;
    }

    /**
     * Updated attachment metadata / regenerate image sizes
     * @param $attach_id
     */
    private function updated_attachment_metadata($attach_id) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        $file_path = wp_get_original_image_path($attach_id);
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
        wp_update_attachment_metadata( $attach_id,  $attach_data );
    }

    /**
     *  Create attachment id
     * @return int|WP_Error
     */
    private function create_attachment_id() {
        $attachment = array(
            'post_mime_type' => $this->getFileType(),
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $this->getHashedFilename() ) ),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'post_type'    => 'attachment',
            'guid'           => $this->getUploadDir()['url'] . '/' . basename( $this->getHashedFilename() )
        );
        $attach_id = wp_insert_attachment( $attachment, $this->getUploadDir()['path'] . '/' . $this->getHashedFilename() );

        if (!is_wp_error($attach_id)){
            $this->setAttachId($attach_id);
        } else {
            return $this->error->setStatusCode(400)->setMessage("The image wasn't create in database")->report();
        }
    }

    /**
     * Set allow format for wordpress
     * @param $mtype
     */
    private function set_allow_format($mtype) {
        if($mtype == ( "application/pdf" )) {
            $this->setFormat('pdf');
        }elseif($mtype == ( "image/png" )){
            $this->setFormat('png');
        }elseif($mtype == ( "image/jpeg" )){
            $this->setFormat('jpeg');
        }elseif($mtype == ( "image/jpg" )){
            $this->setFormat('jpg');
        }else {
            $this->setFormat('doc');
        }
    }

    /**
     * Create image file based on base64
     * @param $base64
     * @return mixed
     */
    private function create_image_file($base64) {
        
        $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $this->getUploadDir()['path'] ) . DIRECTORY_SEPARATOR;
        $img             = str_replace( 'data:'.$this->getFileType().';base64,', '', $base64 );
        $img             = str_replace( ' ', '+', $img );
        $decoded         = base64_decode( $img );
        $upload_path = file_put_contents( $upload_path . $this->getHashedFilename(), $decoded );
        if (is_wp_error($upload_path)) {
            return $this->error->setStatusCode(400)->setMessage("The image wasn't load to uploads")->report();
        }
    }

    /**
     * Check allow mime type for wordpress
     * @param $mtype
     * @return bool
     */
    private function check_allow_type($mtype) {
        if ($mtype == ( "application/vnd.openxmlformats-officedocument.wordprocessingml.document" ) ||
            $mtype == ( "application/vnd.ms-excel" ) ||
            $mtype == ( "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" ) ||
            $mtype == ( "application/vnd.ms-powerpoint" ) ||
            $mtype == ( "application/vnd.openxmlformats-officedocument.presentationml.presentation" )) {
            return false;
        }
        elseif($mtype == ( "application/pdf" )) {
            return false;
        }
        elseif($mtype == ( "image/jpeg" )) {
            return false;
        }
        elseif($mtype == ( "image/jpg" )) {
            return false;
        }
        elseif($mtype == ( "image/png" )) {
            return false;
        }
        return true;
    }
    /**
     * @return mixed
     */
    public function getFileType()
    {
        return $this->file_type;
    }

    /**
     * @param mixed $file_type
     */
    public function setFileType($file_type)
    {
        $this->file_type = $file_type;
    }
    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param mixed $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return mixed
     */
    public function getUploadDir()
    {
        return $this->upload_dir;
    }

    /**
     * @param mixed $upload_dir
     */
    public function setUploadDir($upload_dir)
    {
        $this->upload_dir = $upload_dir;
    }

    /**
     * @return mixed
     */
    public function getHashedFilename()
    {
        return $this->hashed_filename;
    }

    /**
     * @param mixed $hashed_filename
     */
    public function setHashedFilename($hashed_filename)
    {
        $this->hashed_filename = $hashed_filename;
    }

    /**
     * @return mixed
     */
    public function getAttachId()
    {
        return $this->attach_id;
    }

    /**
     * @param mixed $attach_id
     */
    public function setAttachId($attach_id)
    {
        $this->attach_id = $attach_id;
    }
}
