# EDI X12 ANSI 270
PHP Library for creating EDI X12 ANSI 270 File 5010 Version

A Simple PHP function for creating an EDI X12 ANSI 270 file version 005010X279A1

* <a href="https://www.edibasics.com/what-is-edi/" target="_blank">EDI Transactions</a>

* <a href="http://www.x12.org/examples/" target="_blanl">X12 Examples</a>

## How To use
```````````
$res[] =   [
	'tansaction_id' => '000001' 			/*Unique Transaction ID for each request */
	,'date_of_service' => '20171028'        /* Date Of service */ 
	,'subscriber_lname' => '***'			/* Subscriber/Patient last name */ 
	,'subscriber_fname' => '***'			/* Subscriber/Patient First name */ 
	,'subscriber_mname' => '**'			/* Subscriber/Patient middle name */ 
	,'subscriber_dob'=>'***'			/* Subscriber/Patient DOB  YYYYMMDD*/ 
	,'subscriber_policy_number' => '***' /* Subscriber/Patient Policy Number*/ 
	,'subscriber_gender' => 'M'				/* Subscriber/Patient Gender M/F/U */ 
	,'date' =>'20171028'					/* Interchange Date  YYYYMMDD*/
	,'provider_npi' => '***'			/* Doctor NPI*/
	,'x12_sender_id' => '***'				
	,'payer_code' => '***' 				/* Insurance Payer Code*/
	,'x12_version' => '005010X279A1'
	,'payer_name' => '****'				/* Insurance Payer Name*/
	];
	

	createEDIFile($res,$segTer,$compEleSep);
	echo "270 File is Ready !";

```````````


The output file generated have a valid X12 EDI ANSI 270 file , just send the file to your insurance wharehouse

Happy Coding :) 

###### What Next ? Looking for 837P ? yep its ready will publish soon.