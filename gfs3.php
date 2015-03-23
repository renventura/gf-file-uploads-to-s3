<?php

/**
 *	Send Gravity Forms file uploads to Amazon S3
 *	@author Ren Ventura <EnageWP.com>
 *	@link http://www.engagewp.com/send-gravity-forms-file-uploads-to-amazon-s3/
 */

//* Include the required library
include_once 'inc/S3.php';

//* AWS access info
define( 'awsAccessKey', 'access-key-here' );
define( 'awsSecretKey', 'secret-key-here' );
define( 'GFS3_BUCKET', 'bucket-name' );

//* Form constants
define( 'FORM_ID', 1 );
define( 'FILE_UPLOAD_FIELD_ID', 18 );

//* Upload the file after form is submitted (Product Edit)
add_action( 'gform_after_submission_' . FORM_ID, 'gf_submit_to_s3', 10, 2 );
function gf_submit_to_s3( $entry, $form ) {

	// Bail if there is no file uploaded to the form
	if ( empty( $entry[FILE_UPLOAD_FIELD_ID] ) )
		return;

	// Instantiate the S3 class
	$s3 = new S3( awsAccessKey, awsSecretKey );

	// Get the URL of the uploaded file
	$file_url = $entry[FILE_UPLOAD_FIELD_ID];

	// Retreive post variables
	$file_name = $_FILES['input_' . FILE_UPLOAD_FIELD_ID]['name'];

	/**
	 *	File Permissions
	 *
	 *	ACL_PRIVATE
	 *	ACL_PUBLIC_READ
	 *	ACL_PUBLIC_READ_WRITE
	 *	ACL_AUTHENTICATED_READ
	 */

	// Create a new bucket if it does not exist (happens only once)
	$s3->putBucket( GFS3_BUCKET, S3::ACL_AUTHENTICATED_READ );

	// Parse the URL of the uploaded file
	$url_parts = parse_url( $file_url );

	// Full path to the file
	$full_path = $_SERVER['DOCUMENT_ROOT'] . $url_parts['path'];

	// Add the file to S3
	$s3->putObjectFile( $full_path, GFS3_BUCKET, $file_name, S3::ACL_AUTHENTICATED_READ );

	// Confirmation/Error
	if ( $s3->putObjectFile( $full_path, GFS3_BUCKET, $file_name, S3::ACL_AUTHENTICATED_READ ) ) {
	    printf( 'Your file <strong>%1$s</strong> was successfully uploaded.', $file_name );
	} else {
	    wp_die( __( 'It looks like something went wrong while uploading your file. Please try again. If you continue to experience this problem, please contact the site administrator.' ) );
	}

}